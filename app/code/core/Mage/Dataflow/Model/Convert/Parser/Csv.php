<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Dataflow
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Convert csv parser
 *
 * @category   Mage
 * @package    Mage_Dataflow
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Dataflow_Model_Convert_Parser_Csv extends Mage_Dataflow_Model_Convert_Parser_Abstract {

    protected $_fields;
    protected $_mapfields = array();
    protected $type;

    public function parse() {
        // fixed for multibyte characters
        setlocale(LC_ALL, Mage::app()->getLocale()->getLocaleCode() . '.UTF-8');
        $params = Mage::app()->getRequest()->getParams();
        switch ($params['id']):
            case 3:
                $this->type = 'import';
                break;
            case 7:
                $this->type = 'update_stock';
                break;
            case 8:
                $this->type = 'update_date_available';
                break;
        endswitch;
        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $adapterName = $this->getVar('adapter', null);
        $adapterMethod = $this->getVar('method', 'saveRow');

        if (!$adapterName || !$adapterMethod) {
            $message = Mage::helper('dataflow')->__('Please declare "adapter" and "method" nodes first.');
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        try {
            $adapter = Mage::getModel($adapterName);
        } catch (Exception $e) {
            $message = Mage::helper('dataflow')->__('Declared adapter %s was not found.', $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        if (!method_exists($adapter, $adapterMethod)) {
            $message = Mage::helper('dataflow')->__('Method "%s" not defined in adapter %s.', $adapterMethod, $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        $batchModel = $this->getBatchModel();
        $batchIoAdapter = $this->getBatchModel()->getIoAdapter();

        if (Mage::app()->getRequest()->getParam('files')) {
            $file = Mage::app()->getConfig()->getTempVarDir() . '/import/'
                    . urldecode(Mage::app()->getRequest()->getParam('files'));
            $this->_copy($file);
        }


        $batchIoAdapter->open(false);
        
        
        $isFieldNames = $this->getVar('fieldnames', '') == 'true' ? true : false;
        if (!$isFieldNames && is_array($this->getVar('map'))) {
            $fieldNames = $this->getVar('map');
        } else {
            $fieldNames = array();
            foreach ($batchIoAdapter->read(true, $fDel, $fEnc) as $v) {
                $fieldNames[$v] = $v;
            }
        }

        $countRows = 0;

        while (($csvData = $batchIoAdapter->read(true, $fDel, $fEnc)) !== false) {
            if (count($csvData) == 1 && $csvData[0] === null) {
                continue;
            }

            $itemData = array();
            $countRows ++;
            $i = 0;
            foreach ($fieldNames as $field) {
                $itemData[$field] = isset($csvData[$i]) ? $csvData[$i] : null;
                $i ++;
            }
            $itemData['importProfile'] = $this->type;
            $batchImportModel = $this->getBatchImportModel()
                    ->setId(null)
                    ->setBatchId($this->getBatchModel()->getId())
                    ->setBatchData($itemData)
                    ->setStatus(1)
                    ->save();

            // $__product[] = $collection->addAttributeToSelect('stylecode')->addFieldToFilter(array(array('attribute'=>'stylecode', 'eq'=>$itemData['stylecode']))); 
            // Add items to group "Configurable Product Type"
            $groups[$itemData['stylecode']][] = $itemData;
        }

        $this->addException(Mage::helper('dataflow')->__('Found %d rows.', $countRows));
        $this->addException(Mage::helper('dataflow')->__('Starting %s :: %s', $adapterName, $adapterMethod));

        $batchModel->setParams($this->getVars())
                ->setAdapter($adapterName)
                ->save();

        // $adapter->$adapterMethod();
        // $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'color');
        // $options = array();
        // if ($attribute->usesSource()) {
        //    $options = $attribute->getSource()->getAllOptions(false);
        // }
        // Begin create configurable product.
        foreach ($groups as $key => $products) {
            $configurable = $products[0];
            if (strtolower(trim($configurable['color'])) != 'none') {
                $configurable['visibility'] = 'Search';
                $configurable['type'] = 'configurable';
                $configurable['store'] = 'default';
//                if($this->type=='import')
                $productId = $adapter->saveRow($configurable, $products);
//                echo '<pre>';
//                print_r($productId);
//                echo '</pre>';
//                exit;
                $resource = Mage::getSingleton('core/resource');
                $read = $resource->getConnection('core_read');
                $write = $resource->getConnection('core_write');
                $query = "select count(*) as exist from catalog_product_super_attribute"
                        . " WHERE `product_id` = " . $productId;
                if ($read->fetchOne($query) > 0) {
                    /*
                     * BEGIN SET DEFAULT IMAGES
                     */
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $mediaGallery = $product->getMediaGallery();
                    //loop through the images
                    foreach ($mediaGallery['images'] as $image) {
                        //set the first image as the base image
                        Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('image' => $image['file']), 0);
                        Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('small_image' => $image['file']), 0);
                        Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('thumbnail' => $image['file']), 0);
                        //stop
                        break;
                    }
                    /*
                     * END SET DEFAULT IMAGE
                     */

                    $delete = "DELETE FROM `catalog_product_super_attribute` WHERE `product_id` = $productId AND `attribute_id` IN (168,134)";
                    $write->query($delete);
                    $items = Mage::getModel('catalog/product')->getCollection();
                    $items->addAttributeToSelect('id');
                    $items->addAttributeToFilter('stylecode', array('eq' => $key))->addAttributeToFilter('type_id', array('eq' => 'simple'));
                    $configurableProductsData = array();
                    if (!empty($items)) {
                        foreach ($items as $item) {
                            /**/
                            /** assigning associated product to configurable */
                            /**/
                            if (strtolower(trim($configurable['size'])) == 'none' || !$configurable['size']) {
                                $product->getTypeInstance()->setUsedProductAttributeIds(array(168));
                            } else {
                                $product->getTypeInstance()->setUsedProductAttributeIds(array(168, 134)); //attribute ID of attribute 'color' / 'size' in my store
                            }

                            $configurableAttributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray();
                            $product->setCanSaveConfigurableAttributes(true);
                            $product->setConfigurableAttributesData($configurableAttributesData);
                            $configurableProductsData[$item->getId()] = array();
                        }
                    }
                    $product->setConfigurableProductsData($configurableProductsData);
                    $product->save();
                } else {
                    /*
                     * BEGIN SET DEFAULT IMAGES
                     */
                    $product = Mage::getModel('catalog/product')->load($productId);

                    $mediaGallery = $product->getMediaGallery();
                    if (isset($mediaGallery['images'])) {
                        //loop through the images
                        foreach ($mediaGallery['images'] as $image) {
                            //set the first image as the base image
                            Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('image' => $image['file']), 0);
                            Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('small_image' => $image['file']), 0);
                            Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('thumbnail' => $image['file']), 0);
                            //stop
                            break;
                        }
                    }

                    /*
                     * END SET DEFAULT IMAGE
                     */

                    if ($productId > 0) {
                        $configProduct = Mage::getModel('catalog/product')->load($productId);

                        $items = Mage::getModel('catalog/product')->getCollection();
                        $items->addAttributeToSelect('id');
                        $items->addAttributeToFilter('stylecode', array('eq' => $key))->addAttributeToFilter('type_id', array('eq' => 'simple'));
                        $configurableProductsData = array();
                        foreach ($items as $item) {
                            /**/
                            /** assigning associated product to configurable */
                            /**/
                            if (strtolower(trim($configurable['size'])) == 'none' || !$configurable['size']) {
                                $configProduct->getTypeInstance()->setUsedProductAttributeIds(array(168));
                            } else {
                                $configProduct->getTypeInstance()->setUsedProductAttributeIds(array(168, 134)); //attribute ID of attribute 'color' / 'size' in my store
                            }

                            $configurableAttributesData = $configProduct->getTypeInstance()->getConfigurableAttributesAsArray();
                            $configProduct->setCanSaveConfigurableAttributes(true);
                            $configProduct->setConfigurableAttributesData($configurableAttributesData);
                            $configurableProductsData[$item->getId()] = array();
                        }
                        $configProduct->setConfigurableProductsData($configurableProductsData);
                        $configProduct->save();
                    }
                }
            }
        }
        // End create configurable product.

        return $this;

//        // fix for field mapping
//        if ($mapfields = $this->getProfile()->getDataflowProfile()) {
//            $this->_mapfields = array_values($mapfields['gui_data']['map'][$mapfields['entity_type']]['db']);
//        } // end
//
//        if (!$this->getVar('fieldnames') && !$this->_mapfields) {
//            $this->addException('Please define field mapping', Mage_Dataflow_Model_Convert_Exception::FATAL);
//            return;
//        }
//
//        if ($this->getVar('adapter') && $this->getVar('method')) {
//            $adapter = Mage::getModel($this->getVar('adapter'));
//        }
//
//        $i = 0;
//        while (($line = fgetcsv($fh, null, $fDel, $fEnc)) !== FALSE) {
//            $row = $this->parseRow($i, $line);
//
//            if (!$this->getVar('fieldnames') && $i == 0 && $row) {
//                $i = 1;
//            }
//
//            if ($row) {
//                $loadMethod = $this->getVar('method');
//                $adapter->$loadMethod(compact('i', 'row'));
//            }
//            $i++;
//        }
//
//        return $this;
    }

    public function parseRow($i, $line) {
        if (sizeof($line) == 1)
            return false;

        if (0 == $i) {
            if ($this->getVar('fieldnames')) {
                $this->_fields = $line;
                return;
            } else {
                foreach ($line as $j => $f) {
                    $this->_fields[$j] = $this->_mapfields[$j];
                }
            }
        }

        $resultRow = array();

        foreach ($this->_fields as $j => $f) {
            $resultRow[$f] = isset($line[$j]) ? $line[$j] : '';
        }
        return $resultRow;
    }

    /**
     * Read data collection and write to temporary file
     *
     * @return Mage_Dataflow_Model_Convert_Parser_Csv
     */
    public function unparse() {
        $batchExport = $this->getBatchExportModel()
                ->setBatchId($this->getBatchModel()->getId());
        $fieldList = $this->getBatchModel()->getFieldList();
        $batchExportIds = $batchExport->getIdCollection();

        $io = $this->getBatchModel()->getIoAdapter();
        $io->open();

        if (!$batchExportIds) {
            $io->write("");
            $io->close();
            return $this;
        }

        if ($this->getVar('fieldnames')) {
            $csvData = $this->getCsvString($fieldList);
            $io->write($csvData);
        }

        foreach ($batchExportIds as $batchExportId) {
            $csvData = array();
            $batchExport->load($batchExportId);
            $row = $batchExport->getBatchData();

            foreach ($fieldList as $field) {
                $csvData[] = isset($row[$field]) ? $row[$field] : '';
            }
            $csvData = $this->getCsvString($csvData);
            $io->write($csvData);
        }

        $io->close();

        return $this;
    }

    public function unparseRow($args) {
        $i = $args['i'];
        $row = $args['row'];

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        $fEsc = $this->getVar('escape', '\\');
        $lDel = "\r\n";

        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $line = array();
        foreach ($this->_fields as $f) {
            $v = isset($row[$f]) ? str_replace(array('"', '\\'), array($fEnc . '"', $fEsc . '\\'), $row[$f]) : '';
            $line[] = $fEnc . $v . $fEnc;
        }

        return join($fDel, $line);
    }

    /**
     * Retrieve csv string from array
     *
     * @param array $fields
     * @return sting
     */
    public function getCsvString($fields = array()) {
        $delimiter = $this->getVar('delimiter', ',');
        $enclosure = $this->getVar('enclose', '');
        $escapeChar = $this->getVar('escape', '\\');

        if ($delimiter == '\t') {
            $delimiter = "\t";
        }

        $str = '';

        foreach ($fields as $value) {
            if (strpos($value, $delimiter) !== false ||
                    empty($enclosure) ||
                    strpos($value, $enclosure) !== false ||
                    strpos($value, "\n") !== false ||
                    strpos($value, "\r") !== false ||
                    strpos($value, "\t") !== false ||
                    strpos($value, ' ') !== false) {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i = 0; $i < $len; $i++) {
                    if ($value[$i] == $escapeChar) {
                        $escaped = 1;
                    } else if (!$escaped && $value[$i] == $enclosure) {
                        $str2 .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                    $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2 . $delimiter;
            } else {
                $str .= $enclosure . $value . $enclosure . $delimiter;
            }
        }
        return substr($str, 0, -1) . "\n";
    }

}
