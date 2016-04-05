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
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Catalog_Model_Convert_Adapter_Product extends Mage_Eav_Model_Convert_Adapter_Entity {

    const MULTI_DELIMITER = ' , ';
    const ENTITY = 'catalog_product_import';
    //bainguyen
    const PRODUCT_TYPE = 'simple';
    const PROFILE_IMPORT_ALL_PRODUCT_ID = 3;
    const WEBSITE_CODE_DEFAULT = 'base,main';
    const QTY_DEFAULT = '100';
    const SECOND_STORE_ID = 3;
    const MAX_NUMBER_IMAGE_GALLERY = 5;
    const PRODUCT_CATEGORY = '122';

    public static $_CATEGORY = array(
        'ropes package' => '122,124,129', // 122- product
        'ropes - handle' => '122,124,130',
        'ropes only' => '122,124,131',
        'vests - impact' => '122,123',
        'vests - au appr' => '122,123',
        'board bags' => '122,121',
        'tubes' => '122,121',
        'acc - misc' => '122,121',
        'acc - decals' => '122,121',
        'acc - keyrings' => '122,121',
        'wetsuits' => '122,121',
        'boardshorts' => '122,125',
        'shorts' => '122,125',
        'shirts' => '122,125',
        'tee shirts - sh' => '122,125',
        'hydro tees' => '122,125',
        'jackets' => '122,125',
        'caps' => '122,125'
    );
    public static $_RECOMMENDED_BY = array(
        'AMONGST THE STICKS' => '90', // 122- product
        'ANGELIKA SCHRIBER' => '84',
        'BEN HORAN' => '88',
        'BRENTON PRIESTLY' => '83',
        'CODY HESSE' => '89',
        'DEREK COOK' => '87',
        'MITCH LANGFIELD' => '86',
        'OLI DEROME' => '85',
        'TOBIAS RITTIG' => '91',
    );

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'catalog_product_import';

    /**
     * Product model
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_productModel;

    /**
     * product types collection array
     *
     * @var array
     */
    protected $_productTypes;

    /**
     * Product Type Instances singletons
     *
     * @var array
     */
    protected $_productTypeInstances = array();

    /**
     * product attribute set collection array
     *
     * @var array
     */
    protected $_productAttributeSets;
    protected $_stores;
    protected $_attributes = array();
    protected $_configs = array();
    protected $_requiredFields = array();
    protected $_ignoreFields = array();

    /**
     * @deprecated after 1.5.0.0-alpha2
     *
     * @var array
     */
    protected $_imageFields = array();

    /**
     * Inventory Fields array
     *
     * @var array
     */
    protected $_inventoryFields = array();

    /**
     * Inventory Fields by product Types
     *
     * @var array
     */
    protected $_inventoryFieldsProductTypes = array();
    protected $_toNumber = array();

    /**
     * Retrieve event prefix for adapter
     *
     * @return string
     */
    public function getEventPrefix() {
        return $this->_eventPrefix;
    }

    /**
     * Affected entity ids
     *
     * @var array
     */
    protected $_affectedEntityIds = array();

    /**
     * Store affected entity ids
     *
     * @param  int|array $ids
     * @return Mage_Catalog_Model_Convert_Adapter_Product
     */
    protected function _addAffectedEntityIds($ids) {
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->_addAffectedEntityIds($id);
            }
        } else {
            $this->_affectedEntityIds[] = $ids;
        }

        return $this;
    }

    /**
     * Retrieve affected entity ids
     *
     * @return array
     */
    public function getAffectedEntityIds() {
        return $this->_affectedEntityIds;
    }

    /**
     * Clear affected entity ids results
     *
     * @return Mage_Catalog_Model_Convert_Adapter_Product
     */
    public function clearAffectedEntityIds() {
        $this->_affectedEntityIds = array();
        return $this;
    }

    /**
     * Load product collection Id(s)
     */
    public function load() {
        $attrFilterArray = array();
        $attrFilterArray ['name'] = 'like';
        $attrFilterArray ['sku'] = 'startsWith';
        $attrFilterArray ['type'] = 'eq';
        $attrFilterArray ['attribute_set'] = 'eq';
        $attrFilterArray ['visibility'] = 'eq';
        $attrFilterArray ['status'] = 'eq';
        $attrFilterArray ['price'] = 'fromTo';
        $attrFilterArray ['qty'] = 'fromTo';
        $attrFilterArray ['store_id'] = 'eq';

        $attrToDb = array(
            'type' => 'type_id',
            'attribute_set' => 'attribute_set_id'
        );

        $filters = $this->_parseVars();

        if ($qty = $this->getFieldValue($filters, 'qty')) {
            $qtyFrom = isset($qty['from']) ? (float) $qty['from'] : 0;
            $qtyTo = isset($qty['to']) ? (float) $qty['to'] : 0;

            $qtyAttr = array();
            $qtyAttr['alias'] = 'qty';
            $qtyAttr['attribute'] = 'cataloginventory/stock_item';
            $qtyAttr['field'] = 'qty';
            $qtyAttr['bind'] = 'product_id=entity_id';
            $qtyAttr['cond'] = "{{table}}.qty between '{$qtyFrom}' AND '{$qtyTo}'";
            $qtyAttr['joinType'] = 'inner';

            $this->setJoinField($qtyAttr);
        }

        parent::setFilter($attrFilterArray, $attrToDb);

        if ($price = $this->getFieldValue($filters, 'price')) {
            $this->_filter[] = array(
                'attribute' => 'price',
                'from' => $price['from'],
                'to' => $price['to']
            );
            $this->setJoinAttr(array(
                'alias' => 'price',
                'attribute' => 'catalog_product/price',
                'bind' => 'entity_id',
                'joinType' => 'LEFT'
            ));
        }

        return parent::load();
    }

    /**
     * Retrieve product model cache
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProductModel() {
        if (is_null($this->_productModel)) {
            $productModel = Mage::getModel('catalog/product');
            $this->_productModel = Mage::objects()->save($productModel);
        }
        return Mage::objects()->load($this->_productModel);
    }

    /**
     * Retrieve eav entity attribute model
     *
     * @param string $code
     * @return Mage_Eav_Model_Entity_Attribute
     */
    public function getAttribute($code) {
        if (!isset($this->_attributes[$code])) {
            $this->_attributes[$code] = $this->getProductModel()->getResource()->getAttribute($code);
        }
        if ($this->_attributes[$code] instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
            $applyTo = $this->_attributes[$code]->getApplyTo();
            if ($applyTo && !in_array($this->getProductModel()->getTypeId(), $applyTo)) {
                return false;
            }
        }
        return $this->_attributes[$code];
    }

    /**
     * Retrieve product type collection array
     *
     * @return array
     */
    public function getProductTypes() {
        if (is_null($this->_productTypes)) {
            $this->_productTypes = array();
            $options = Mage::getModel('catalog/product_type')
                    ->getOptionArray();
            foreach ($options as $k => $v) {
                $this->_productTypes[$k] = $k;
            }
        }
        return $this->_productTypes;
    }

    /**
     * ReDefine Product Type Instance to Product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Convert_Adapter_Product
     */
    public function setProductTypeInstance(Mage_Catalog_Model_Product $product) {
        $type = $product->getTypeId();
        if (!isset($this->_productTypeInstances[$type])) {
            $this->_productTypeInstances[$type] = Mage::getSingleton('catalog/product_type')
                    ->factory($product, true);
        }
        $product->setTypeInstance($this->_productTypeInstances[$type], true);
        return $this;
    }

    /**
     * Retrieve product attribute set collection array
     *
     * @return array
     */
    public function getProductAttributeSets() {
        if (is_null($this->_productAttributeSets)) {
            $this->_productAttributeSets = array();

            $entityTypeId = Mage::getModel('eav/entity')
                    ->setType('catalog_product')
                    ->getTypeId();
            $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
                    ->setEntityTypeFilter($entityTypeId);
            foreach ($collection as $set) {
                $this->_productAttributeSets[$set->getAttributeSetName()] = $set->getId();
            }
        }
        return $this->_productAttributeSets;
    }

    /**
     *  Init stores
     */
    protected function _initStores() {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores(true, true);
            foreach ($this->_stores as $code => $store) {
                $this->_storesIdCode[$store->getId()] = $code;
            }
        }
    }

    /**
     * Retrieve store object by code
     *
     * @param string $store
     * @return Mage_Core_Model_Store
     */
    public function getStoreByCode($store) {
        $this->_initStores();
        /**
         * In single store mode all data should be saved as default
         */
        if (Mage::app()->isSingleStoreMode()) {
            return Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        }

        if (isset($this->_stores[$store])) {
            return $this->_stores[$store];
        }

        return false;
    }

    /**
     * Retrieve store object by code
     *
     * @param string $store
     * @return Mage_Core_Model_Store
     */
    public function getStoreById($id) {
        $this->_initStores();
        /**
         * In single store mode all data should be saved as default
         */
        if (Mage::app()->isSingleStoreMode()) {
            return Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        }

        if (isset($this->_storesIdCode[$id])) {
            return $this->getStoreByCode($this->_storesIdCode[$id]);
        }

        return false;
    }

    public function parse() {
        $batchModel = Mage::getSingleton('dataflow/batch');
        /* @var $batchModel Mage_Dataflow_Model_Batch */

        $batchImportModel = $batchModel->getBatchImportModel();
        $importIds = $batchImportModel->getIdCollection();
        foreach ($importIds as $importId) {
            //print '<pre>'.memory_get_usage().'</pre>';
            $batchImportModel->load($importId);
            $importData = $batchImportModel->getBatchData();
            $this->saveRow($importData);
        }
    }

    protected $_productId = '';

    /**
     * Initialize convert adapter model for products collection
     *
     */
    public function __construct() {
        $fieldset = Mage::getConfig()->getFieldset('catalog_product_dataflow', 'admin');
        foreach ($fieldset as $code => $node) {
            /* @var $node Mage_Core_Model_Config_Element */
            if ($node->is('inventory')) {
                foreach ($node->product_type->children() as $productType) {
                    $productType = $productType->getName();
                    $this->_inventoryFieldsProductTypes[$productType][] = $code;
                    if ($node->is('use_config')) {
                        $this->_inventoryFieldsProductTypes[$productType][] = 'use_config_' . $code;
                    }
                }

                $this->_inventoryFields[] = $code;
                if ($node->is('use_config')) {
                    $this->_inventoryFields[] = 'use_config_' . $code;
                }
            }
            if ($node->is('required')) {
                $this->_requiredFields[] = $code;
            }
            if ($node->is('ignore')) {
                $this->_ignoreFields[] = $code;
            }
            if ($node->is('to_number')) {
                $this->_toNumber[] = $code;
            }
        }

        $this->setVar('entity_type', 'catalog/product');
        if (!Mage::registry('Object_Cache_Product')) {
            $this->setProduct(Mage::getModel('catalog/product'));
        }

        if (!Mage::registry('Object_Cache_StockItem')) {
            $this->setStockItem(Mage::getModel('cataloginventory/stock_item'));
        }
    }

    /**
     * Retrieve not loaded collection
     *
     * @param string $entityType
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _getCollectionForLoad($entityType) {
        $collection = parent::_getCollectionForLoad($entityType)
                ->setStoreId($this->getStoreId())
                ->addStoreFilter($this->getStoreId());
        return $collection;
    }

    public function setProduct(Mage_Catalog_Model_Product $object) {
        $id = Mage::objects()->save($object);
        //$this->_product = $object;
        Mage::register('Object_Cache_Product', $id);
    }

    public function getProduct() {
        return Mage::objects()->load(Mage::registry('Object_Cache_Product'));
    }

    public function setStockItem(Mage_CatalogInventory_Model_Stock_Item $object) {
        $id = Mage::objects()->save($object);
        Mage::register('Object_Cache_StockItem', $id);
    }

    public function getStockItem() {
        return Mage::objects()->load(Mage::registry('Object_Cache_StockItem'));
    }

    public function save() {
        $stores = array();
        foreach (Mage::getConfig()->getNode('stores')->children() as $storeNode) {
            $stores[(int) $storeNode->system->store->id] = $storeNode->getName();
        }

        $collections = $this->getData();
        if ($collections instanceof Mage_Catalog_Model_Entity_Product_Collection) {
            $collections = array($collections->getEntity()->getStoreId() => $collections);
        } elseif (!is_array($collections)) {
            $this->addException(
                    Mage::helper('catalog')->__('No product collections found.'), Mage_Dataflow_Model_Convert_Exception::FATAL
            );
        }

        $stockItems = Mage::registry('current_imported_inventory');
        if ($collections)
            foreach ($collections as $storeId => $collection) {
                $this->addException(Mage::helper('catalog')->__('Records for "%s" store found.', $stores[$storeId]));

                if (!$collection instanceof Mage_Catalog_Model_Entity_Product_Collection) {
                    $this->addException(
                            Mage::helper('catalog')->__('Product collection expected.'), Mage_Dataflow_Model_Convert_Exception::FATAL
                    );
                }
                try {
                    $i = 0;
                    foreach ($collection->getIterator() as $model) {
                        $new = false;
                        // if product is new, create default values first
                        if (!$model->getId()) {
                            $new = true;
                            $model->save();

                            // if new product and then store is not default
                            // we duplicate product as default product with store_id -
                            if (0 !== $storeId) {
                                $data = $model->getData();
                                $default = Mage::getModel('catalog/product');
                                $default->setData($data);
                                $default->setStoreId(0);
                                $default->save();
                                unset($default);
                            } // end
                            #Mage::getResourceSingleton('catalog_entity/convert')->addProductToStore($model->getId(), 0);
                        }
                        if (!$new || 0 !== $storeId) {
                            if (0 !== $storeId) {
                                Mage::getResourceSingleton('catalog_entity/convert')->addProductToStore(
                                        $model->getId(), $storeId
                                );
                            }
                            $model->save();
                        }

                        if (isset($stockItems[$model->getSku()]) && $stock = $stockItems[$model->getSku()]) {
                            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($model->getId());
                            $stockItemId = $stockItem->getId();

                            if (!$stockItemId) {
                                $stockItem->setData('product_id', $model->getId());
                                $stockItem->setData('stock_id', 1);
                                $data = array();
                            } else {
                                $data = $stockItem->getData();
                            }

                            foreach ($stock as $field => $value) {
                                if (!$stockItemId) {
                                    if (in_array($field, $this->_configs)) {
                                        $stockItem->setData('use_config_' . $field, 0);
                                    }
                                    $stockItem->setData($field, $value ? $value : 0);
                                } else {

                                    if (in_array($field, $this->_configs)) {
                                        if ($data['use_config_' . $field] == 0) {
                                            $stockItem->setData($field, $value ? $value : 0);
                                        }
                                    } else {
                                        $stockItem->setData($field, $value ? $value : 0);
                                    }
                                }
                            }
                            $stockItem->save();
                            unset($data);
                            unset($stockItem);
                            unset($stockItemId);
                        }
                        unset($model);
                        $i++;
                    }
                    $this->addException(Mage::helper('catalog')->__("Saved %d record(s)", $i));
                } catch (Exception $e) {
                    if (!$e instanceof Mage_Dataflow_Model_Convert_Exception) {
                        $this->addException(
                                Mage::helper('catalog')->__('An error occurred while saving the collection, aborting. Error message: %s', $e->getMessage()), Mage_Dataflow_Model_Convert_Exception::FATAL
                        );
                    }
                }
            }
        unset($collections);

        return $this;
    }

    /**
     * Save product (import)
     *
     * @param  array $importData
     * @param  array $simpleProducts // cumtom for import configurable products from csv
     * @throws Mage_Core_Exception
     * @return bool
     * @desc : Bai Nguyen
     */
    public function saveRow(array $importData, array $simpleProducts = array()) {

        $isnew = true;
        $importData['_sku'] = $importData['sku'];
        $importProfile = isset($importData['importProfile']) ? $importData['importProfile'] : 'import';
        $importData['color'] = str_replace('/', '', $importData['color']);
        if (isset($importData['type']) && $importData['type'] == 'configurable'):
            $importData['sku'] = $importData['stylecode'];
            $importData['product_type_id'] = $importData['type'];
        endif;
		 if (empty($importData['sku'])) {
			return;
		}
        $product = $this->getProductModel()
                ->reset();
        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" is not defined.', 'store');
                Mage::throwException($message);
            }
        } else {
            $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__('Skipping import row, store "%s" field does not exist.', $importData['store']);
            Mage::throwException($message);
        }

        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" is not defined.', 'sku');
            Mage::throwException($message);
        }
        $product->setStoreId($store->getId());
        $product->setStoreId('0');
        $productId = $product->getIdBySku($importData['sku']);
        if (empty($importData['price'])):
            $importData['price'] = $importData['rrp_price'];
            unset($importData['rrp_price']);
        endif;
        /*
         * CHECK PRODUCT IS EXISTING OR NOT
         * IF EXIST, UPDATE 'QTY' AND IMAGE
         * ELSE, INSERT NEW PRODUCT
         */
        if ($importProfile == 'import') {
            if ($productId) {
                //ko update bất cứ field nào nếu là import
                return $productId;
                $isnew = false;
                $product->load($productId);

                $importData['short_description'] = $importData['description'];
                $importData['qty'] = $importData['free_stock']; //UPDATE 'QTY'
                $media_dir = Mage::getBaseDir('media');
                //Begin Update Image
                $checkImgs = $product->getImage();

                if (empty($checkImgs)) {
                    /*
                     * IF HAVE NO IMAGE
                     * INSERT IMAGE TO BOTH PRODUCT
                     */
                    $galleries = array();
                    if (isset($importData['product_type_id']) && $importData['product_type_id'] == 'configurable'):
                        // BEGIN MEDIA PROCESSING FOR CONFIGURABLE PRODUCT ONLY
                        $media_dir = Mage::getBaseDir('media');
                        $olderImages = array();
                        $images = $product->getMediaGalleryImages();
                        if (!empty($images)) {
                            foreach ($images as $image) {
                                $olderImages[] = $image->getData('label');
                            }
                        }
                        $olderImages[] = $product->getImageLabel();
                        if (empty($importData['image'])):
                            if (is_file($media_dir . '/import/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg')):
                                $importData['small_image'] = $importData['thumbnail'] = $importData['image'] = '/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg';
                            else :
                                $importData['small_image'] = $importData['thumbnail'] = $importData['image'] = '';
                            endif;
                            $importData['image_label'] = $importData['small_image_label'] = $importData['thumbnail_label'] = strtolower($importData['color']) . '-swatch';
                        endif;
                        foreach ($simpleProducts as $simpleProduct) {
                            $colorname = str_replace('/', '', strtolower($simpleProduct['color']));
                            if (!in_array($colorname . '-swatch', $olderImages)) {
                                // Import multi images for CONFIGURABLE product
                                // Sample CSV datas:
                                // $importData['color'] = 'Black/White';
                                // File name: [stylecode]-BlackWhite.jpg
                                // Not use               
                                if (!empty($simpleProduct['color'])):
                                    $simpleProduct['color'] = str_replace('/', '', $simpleProduct['color']);
                                    if (is_file($media_dir . '/import/products/' . strtoupper($simpleProduct['stylecode']) . '-' . strtoupper($simpleProduct['color']) . '.jpg')):
                                        $galleries[$colorname]['file'] = '/products/' . strtoupper($simpleProduct['stylecode']) . '-' . strtoupper($simpleProduct['color']) . '.jpg';
                                        $galleries[$colorname]['label'] = $colorname . '-swatch';
                                    endif;
                                endif;
                            }
                        }

                    // END MEDIA PROCESSING FOR CONFIGURABLE PRODUCT ONLY

                    else:
                        // BEGIN MEDIA PROCESSING FOR SIMPLE PRODUCT ONLY

                        if (empty($importData['image'])):
                            if (is_file($media_dir . '/import/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg')):
                                $importData['thumbnail'] = $importData['small_image'] = $importData['image'] = '/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg';
                            else :
                                $importData['image'] = '';
                            endif;
                        endif;
                        $importData['image_label'] = $importData['small_image_label'] = $importData['thumbnail_label'] = strtolower($importData['color']) . '-swatch';

                        // Import multi images for SIMPLE product
                        // Sample CSV datas:
                        // $importData['color'] = 'Black/White';
                        // File name: [stylecode]-BlackWhite.jpg      
                        // Not use
                        $colorname = strtolower($importData['color']);
                        if (!empty($importData['color'])):
                            if (is_file($media_dir . '/import/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg')):
                                $galleries[$colorname]['file'] = '/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg';
                                $galleries[$colorname]['label'] = strtolower($importData['color']) . '-swatch';
                            endif;
                        endif;

                    // END MEDIA PROCESSING FOR SIMPLE PRODUCT ONLY                    
                    endif;
                }else {
                    /*
                     * IF HAVE IMAGE
                     * ADD IMAGE TO PRODUCT
                     * JUST FOR CONFIGURABLE PRODUCT
                     */
                    $galleries = array();
                    if (isset($importData['product_type_id']) && $importData['product_type_id'] == 'configurable') {
                        $olderImages = array();
                        $images = $product->getMediaGalleryImages();
                        if (!empty($images)) {
                            foreach ($images as $image) {
                                $olderImages[] = $image->getData('label');
                            }
                        }
                        $olderImages[] = $product->getImageLabel();
                        foreach ($simpleProducts as $simpleProduct) {
                            $colorname = str_replace('/', '', strtolower($simpleProduct['color']));
                            if (!in_array($colorname . '-swatch', $olderImages)) {
                                // Import multi images for CONFIGURABLE product
                                // Sample CSV datas:
                                // $importData['color'] = 'Black/White';
                                // File name: [stylecode]-BlackWhite.jpg
                                // Not use               
                                if (!empty($simpleProduct['color'])):
                                    $simpleProduct['color'] = str_replace('/', '', $simpleProduct['color']);
                                    if (is_file($media_dir . '/import/products/' . strtoupper($simpleProduct['stylecode']) . '-' . strtoupper($simpleProduct['color']) . '.jpg')):
                                        $galleries[$colorname]['file'] = '/products/' . strtoupper($simpleProduct['stylecode']) . '-' . strtoupper($simpleProduct['color']) . '.jpg';
                                        $galleries[$colorname]['label'] = $colorname . '-swatch';
                                    endif;
                                endif;
                            }
                        }
                    }
                    //END ADD IMAGE
                }
            } else {
                $productTypes = $this->getProductTypes();
                $productAttributeSets = $this->getProductAttributeSets();

                /**
                 * CHECK PRODUCT DEFINE TYPE
                 */
                if (empty($importData['type'])):
                    $importData['type'] = self::PRODUCT_TYPE;
                endif;
                if (empty($importData['type'])):
                    $importData['attribute_set'] = self::ATTRIBUTE_SET_DEFAULT;
                endif;
                if (!isset($productTypes[strtolower($importData['type'])])) {
                    $value = isset($importData['type']) ? $importData['type'] : '';
                    $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'type');
                    Mage::throwException($message);
                }
                $product->setTypeId($productTypes[strtolower($importData['type'])]);
                /**
                 * CHECK PRODUCT DEFINE ATTRIBUTE SET
                 */
                if (empty($importData['attribute_set'])):
                    $profile = Mage::getModel('dataflow/profile');
                    $data = $profile->getCollection()->addFieldToFilter('profile_id', self::PROFILE_IMPORT_ALL_PRODUCT_ID)->removeAllFieldsFromSelect()->addFieldToSelect('attribute_set')->getFirstItem()->getData();

                    $importData['attribute_set'] = $data['attribute_set'];
                endif;
                if (empty($importData['description'])):
                    $importData['description'] = $importData['name'];
                endif;
                if (empty($importData['short_description'])):
                    $importData['short_description'] = $importData['description'];
                endif;

                if (empty($importData['store'])):
                    $store = Mage::app()->getStore();
                    $importData['store'] = $store->getName();
                endif;
                if (empty($importData['websites'])):
                    $importData['websites'] = self::WEBSITE_CODE_DEFAULT;
                endif;

                $galleries = array();
                if (isset($importData['product_type_id']) && $importData['product_type_id'] == 'configurable'):
                    // BEGIN MEDIA PROCESSING FOR CONFIGURABLE PRODUCT ONLY
                    $media_dir = Mage::getBaseDir('media');
                    if (empty($importData['image'])):
                        if (is_file($media_dir . '/import/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg')):
                            $importData['small_image'] = $importData['thumbnail'] = $importData['image'] = '/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg';
                        else :
                            $importData['small_image'] = $importData['thumbnail'] = $importData['image'] = '';
                        endif;
                        $importData['image_label'] = $importData['small_image_label'] = $importData['thumbnail_label'] = strtolower($importData['color']) . '-swatch';
                    endif;
                    foreach ($simpleProducts as $simpleProduct) {
                        $colorname = strtolower(str_replace('/', '', $simpleProduct['color']));
                        // Import multi images for CONFIGURABLE product
                        // Sample CSV datas:
                        // $importData['color'] = 'Black/White';
                        // File name: [stylecode]-BlackWhite.jpg
                        // Not use               
                        if (!empty($simpleProduct['color'])):
                            $simpleProduct['color'] = str_replace('/', '', $simpleProduct['color']);
                            if (is_file($media_dir . '/import/products/' . strtoupper($simpleProduct['stylecode']) . '-' . strtoupper($simpleProduct['color']) . '.jpg')):
                                $galleries[$colorname]['file'] = '/products/' . strtoupper($simpleProduct['stylecode']) . '-' . strtoupper($simpleProduct['color']) . '.jpg';
                                $galleries[$colorname]['label'] = $colorname . '-swatch';
                            endif;
                        endif;
                    }

                // END MEDIA PROCESSING FOR CONFIGURABLE PRODUCT ONLY

                else:
                    // BEGIN MEDIA PROCESSING FOR SIMPLE PRODUCT ONLY
                    $media_dir = Mage::getBaseDir('media');
                    if (empty($importData['image'])):
                        if (is_file($media_dir . '/import/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg')):
                            $importData['thumbnail'] = $importData['small_image'] = $importData['image'] = '/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg';
                        else :
                            $importData['image'] = '';
                        endif;
                    endif;
                    $importData['image_label'] = $importData['small_image_label'] = $importData['thumbnail_label'] = strtolower($importData['color']) . '-swatch';

                    // Import multi images for SIMPLE product
                    // Sample CSV datas:
                    // $importData['color'] = 'Black/White';
                    // File name: [stylecode]-BlackWhite.jpg      
                    // Not use
                    $colorname = strtolower($importData['color']);
                    if (!empty($importData['color'])):
                        if (is_file($media_dir . '/import/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg')):
                            $galleries[$colorname]['file'] = '/products/' . strtoupper($importData['stylecode']) . '-' . strtoupper($importData['color']) . '.jpg';
                            $galleries[$colorname]['label'] = strtolower(str_replace('/', '', $simpleProduct['color'])) . '-swatch';
                        endif;
                    endif;

                // END MEDIA PROCESSING FOR SIMPLE PRODUCT ONLY                    
                endif;

                if (empty($importData['url_key'])):
                    $importData['url_key'] = Mage::getModel('catalog/product_url')->formatUrlKey($importData['name']);
                endif;
                if (empty($importData['url_path'])):
                    $importData['url_path'] = Mage::getModel('catalog/product_url')->formatUrlKey($importData['name']) . '.html';
                endif;
                if (empty($importData['status'])):
                    $importData['status'] = 'Enabled';
                endif;

                if (!empty($importData['free_stock'])):
                    $importData['qty'] = $importData['free_stock'];
                endif;
                if (empty($importData['qty'])):
                    $importData['qty'] = self::QTY_DEFAULT;
                endif;
                if (empty($importData['min_qty'])):
                    $importData['min_qty'] = '1';
                endif;
                if (empty($importData['tax_class_id'])):
                    $importData['tax_class_id'] = 'None';
                endif;
                if (empty($importData['use_config_min_qty'])):
                    $importData['use_config_min_qty'] = '1';
                endif;
                if (empty($importData['is_qty_decimal'])):
                    $importData['is_qty_decimal'] = '0';
                endif;
                if (empty($importData['backorders'])):
                    $importData['backorders'] = '0';
                endif;
                if (empty($importData['use_config_backorders'])):
                    $importData['use_config_backorders'] = '1';
                endif;
                if (empty($importData['min_sale_qty'])):
                    $importData['min_sale_qty'] = '1';
                endif;
                if (empty($importData['use_config_min_sale_qty'])):
                    $importData['use_config_min_sale_qty'] = '1';
                endif;
                if (empty($importData['max_sale_qty'])):
                    $importData['max_sale_qty'] = '0';
                endif;
                if (empty($importData['use_config_max_sale_qty'])):
                    $importData['use_config_max_sale_qty'] = '1';
                endif;
                if (empty($importData['use_config_max_sale_qty'])):
                    $importData['use_config_max_sale_qty'] = '1';
                endif;
                if (empty($importData['is_in_stock'])):
                    $importData['is_in_stock'] = '1';
                endif;
                if (empty($importData['use_config_notify_stock_qty'])):
                    $importData['use_config_notify_stock_qty'] = '1';
                endif;
                if (empty($importData['manage_stock'])):
                    $importData['manage_stock'] = '1';
                endif;
                if (empty($importData['use_config_manage_stock'])):
                    $importData['use_config_manage_stock'] = '1';
                endif;
                if (empty($importData['stock_status_changed_auto'])):
                    $importData['stock_status_changed_auto'] = '1';
                endif;
                if (empty($importData['use_config_qty_increments'])):
                    $importData['use_config_qty_increments'] = '1';
                endif;
                if (empty($importData['qty_increments'])):
                    $importData['qty_increments'] = '1';
                endif;
                if (empty($importData['use_config_enable_qty_inc'])):
                    $importData['use_config_enable_qty_inc'] = '1';
                endif;
                if (empty($importData['enable_qty_increments'])):
                    $importData['enable_qty_increments'] = '1';
                endif;
                if (empty($importData['is_decimal_divided'])):
                    $importData['is_decimal_divided'] = '0';
                endif;
                if (empty($importData['stock_status_changed_automatically'])):
                    $importData['stock_status_changed_automatically'] = '1';
                endif;
                if (empty($importData['use_config_enable_qty_increments'])):
                    $importData['use_config_enable_qty_increments'] = '1';
                endif;
                if (empty($importData['product_name'])):
                    $importData['product_name'] = $importData['name'];
                endif;
                if (empty($importData['size'])):
                    $importData['size'] = 'None';
                endif;
                if (empty($importData['store_id'])):
                    $importData['store_id'] = '0,' . ',' . self::SECOND_STORE_ID;
                endif;
                if (empty($importData['product_type_id'])):
                    $importData['product_type_id'] = self::PRODUCT_TYPE;
                endif;
                if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
                    $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                    $message = Mage::helper('catalog')->__('Skip import row, the value "%s" is invalid for field "%s"', $value, 'attribute_set');
                    Mage::throwException($message);
                }
                $cate_str_ids = trim(strtolower($importData['prod_grp']));
                if (empty($importData['category_ids'])):
                    if (isset(self::$_CATEGORY[$cate_str_ids])):
                        $importData['category_ids'] = self::$_CATEGORY[$cate_str_ids];
                        if (substr($cate_str_ids, 0, 5) == 'vests'):
                            if (trim(strtoupper($importData['gender'])) == 'MENS'):
                                $importData['category_ids'].=',126';
                            elseif (trim(strtoupper($importData['gender'])) == 'LADIES'):
                                $importData['category_ids'].=',127';
                            else:
                                $importData['category_ids'].=',128';
                            endif;
                        endif;
                    else:
                        $importData['category_ids'] = self::PRODUCT_CATEGORY;
                    endif;
                endif;
                $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);

                foreach ($this->_requiredFields as $field) {
                    $attribute = $this->getAttribute($field);
                    if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                        $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" for new products is not defined.', $field);
                        Mage::throwException($message);
                    }
                }
            }
//        if (!isset($importData['product_type_id']) || $importData['product_type_id'] != 'configurable') {
//            $importData['name'] = $importData['name'] . ' - ' . $importData['color'];
//        }

            if (isset($importData['recomended_by']) && trim($importData['recomended_by'])):
                $importData['recomended_by'] = strtoupper(trim($importData['recomended_by']));
            endif;
            if (!$importData['recomended_by'] || !isset(self::$_RECOMMENDED_BY[$importData['recomended_by']])):
                unset($importData['recomended_by']);
            endif;
            if (!$importData['description']):
                $importData['description'] = $importData['name'];
            endif;
            if (empty($importData['short_description'])):
                $importData['short_description'] = $importData['description'];
            endif;
            // new request cho moi color cho ra 1 sp se doi lai cau duoi
            if (isset($importData['product_type_id']) && $importData['product_type_id'] == 'configurable'):
//                $importData['visibility'] = 'Catalog, Search'; //live
                $importData['visibility'] = 'Search'; //staging
            else:
//                $importData['visibility'] = 'Not Visible Individually'; //live
                $importData['visibility'] = 'Catalog, Search'; //staging
            endif;
            // ignore all data exclude 
            if (!$isnew):// neu la san pham cu => chỉ update 2 field
                foreach ($importData as $key => $value):
                    if ($key != 'qty' && $key != 'free_date' && $key != 'free_stock'):
                        unset($importData[$key]);
                    endif;
                endforeach;
            endif;
            $this->_ignoreFields[] = $this->setProductTypeInstance($product);

            if (isset($importData['category_ids'])) {
                $product->setCategoryIds($importData['category_ids']);
            }

            foreach ($this->_ignoreFields as $field) {
                if (isset($importData[$field])) {
                    unset($importData[$field]);
                }
            }

            if ($store->getId() != 0) {
                $websiteIds = $product->getWebsiteIds();
                if (!is_array($websiteIds)) {
                    $websiteIds = array();
                }
                if (!in_array($store->getWebsiteId(), $websiteIds)) {
                    $websiteIds[] = $store->getWebsiteId();
                }
                $product->setWebsiteIds($websiteIds);
            }

            if (isset($importData['websites'])) {
                $websiteIds = $product->getWebsiteIds();
                if (!is_array($websiteIds) || !$store->getId()) {
                    $websiteIds = array();
                }
                $websiteCodes = explode(',', $importData['websites']);
                foreach ($websiteCodes as $websiteCode) {
                    try {
                        $website = Mage::app()->getWebsite(trim($websiteCode));
                        if (!in_array($website->getId(), $websiteIds)) {
                            $websiteIds[] = $website->getId();
                        }
                    } catch (Exception $e) {
                        
                    }
                }
                $product->setWebsiteIds($websiteIds);
                unset($websiteIds);
            }
            foreach ($importData as $field => $value) {
                if (in_array($field, $this->_inventoryFields)) {
                    continue;
                }
                if (is_null($value)) {
                    continue;
                }

                $attribute = $this->getAttribute($field);


                if (!$attribute) {
                    continue;
                }

                $isArray = false;
                $setValue = $value;
                if ($attribute->getFrontendInput() == 'multiselect') {
                    $value = explode(self::MULTI_DELIMITER, $value);
                    $isArray = true;
                    $setValue = array();
                }

                if ($value && $attribute->getBackendType() == 'decimal') {
                    $setValue = $this->getNumber($value);
                }

                if ($attribute->usesSource()) {

                    $options = $attribute->getSource()->getAllOptions(false);

                    if ($isArray) {
                        foreach ($options as $item) {
                            if (in_array(strtolower($item['label']), array_map('strtolower', $value))) {
//                        if (in_array($item['label'], $value)) {
                                $setValue[] = $item['value'];
                            }
                        }
                    } else {
                        $setValue = false;
                        foreach ($options as $item) {
                            if (is_array($item['value'])) {

                                foreach ($item['value'] as $subValue) {

                                    if (isset($subValue['value']) && $subValue['value'] == $value) {
                                        $setValue = $value;
                                    }
                                }
                            } else if ($item['label'] == $value) {

                                $setValue = $item['value'];
                            }
                        }
                    }
                }
                $product->setData($field, $setValue);
            }

            if (!$product->getVisibility()) {
                $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
            }

            $stockData = array();
            $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()]) ? $this->_inventoryFieldsProductTypes[$product->getTypeId()] : array();
            foreach ($inventoryFields as $field) {
                if (isset($importData[$field])) {
                    if (in_array($field, $this->_toNumber)) {
                        $stockData[$field] = $this->getNumber($importData[$field]);
                    } else {
                        $stockData[$field] = $importData[$field];
                    }
                }
            }
            $product->setStockData($stockData);

            $mediaGalleryBackendModel = $this->getAttribute('media_gallery')->getBackend();
            $arrayToMassAdd = array();
            foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
                if (isset($importData[$mediaAttributeCode])) {
                    $file = trim($importData[$mediaAttributeCode]);
                    if (!empty($file) && !$mediaGalleryBackendModel->getImage($product, $file)) {
                        $arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => $mediaAttributeCode);
                    }
                }
            }
            if (!empty($galleries)):
                foreach ($galleries as $colorname => $media_gallery) :
                    $file = trim($media_gallery['file']);
                    $label = trim($media_gallery['label']);
                    if (!empty($file) && !$mediaGalleryBackendModel->getImage($product, $file)) {
                        $arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => 'media_gallery');
                    }
                endforeach;
            endif;
            $addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes(
                    $product, $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import', false, false
            );

            if (!empty($galleries)):
                $addedFile = $product->getData('media_gallery');
                foreach ($galleries as $colorname => $media_gallery) :
                    $label = trim($media_gallery['label']);
//             $galleries[$colorname]['label'];
                    $mediaGalleryBackendModel->updateImage($product, $addedFile, array('label' => strtolower($label)));
                endforeach;
            endif;

            foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
                $addedFile = '';
                if (isset($importData[$mediaAttributeCode . '_label'])) {
                    $fileLabel = trim($importData[$mediaAttributeCode . '_label']);
                    if (isset($importData[$mediaAttributeCode])) {
                        $keyInAddedFile = array_search($importData[$mediaAttributeCode], $addedFilesCorrespondence['alreadyAddedFiles']);
                        if ($keyInAddedFile !== false) {
                            $addedFile = $addedFilesCorrespondence['alreadyAddedFilesNames'][$keyInAddedFile];
                        }
                    }
                    if (!$addedFile) {
                        $addedFile = $product->getData($mediaAttributeCode);
                    }
                    if ($fileLabel && $addedFile) {
                        $mediaGalleryBackendModel->updateImage($product, $addedFile, array('label' => strtolower($fileLabel)));
                    }
                }
            }

            $product->setIsMassupdate(true);
            $product->setExcludeUrlRewrite(true);
            $product->save();
            $productId = $product->getId();

            if ($productId && (!empty($importData['msrp_price']) || $importData['msrp_price'])):
                $resource = Mage::getSingleton('core/resource');
                $write = $resource->getConnection('core_write');
                $read = $resource->getConnection('core_read');
                $query = "select count(*) as exist from catalog_product_entity_decimal WHERE `entity_type_id` = 4 AND `attribute_id` = 75 AND `entity_id` = $productId AND `store_id` = " . self::SECOND_STORE_ID;
                $query2 = "select count(*) as exist from catalog_product_index_price WHERE `entity_id` = $productId AND `website_id` = 2 ";
                if ($read->fetchOne($query) > 0):
//                $update = "UPDATE catalog_product_entity_decimal set value = " . $importData['msrp_price'] . " WHERE `entity_type_id` = 4 AND `attribute_id` = 75 AND `store_id` = " . self::SECOND_STORE_ID;
//                $write->query($update);
                else:
                    $insert = "INSERT INTO catalog_product_entity_decimal (entity_type_id,attribute_id,store_id,entity_id,value) VALUES (4,75," . self::SECOND_STORE_ID . ",$productId," . $importData['msrp_price'] . ")";
                    $write->query($insert);
                endif;
                if ($read->fetchOne($query2) == 0):
                    $insert = "INSERT INTO catalog_product_index_price "
                            . "(entity_id,customer_group_id,website_id,tax_class_id,price,final_price,min_price,max_price) "
                            . "VALUES ($productId,0,2,0," . $importData['msrp_price'] . "," . $importData['msrp_price'] . "," . $importData['msrp_price'] . "," . $importData['msrp_price'] . "),"
                            . " ($productId,1,2,0," . $importData['msrp_price'] . "," . $importData['msrp_price'] . "," . $importData['msrp_price'] . "," . $importData['msrp_price'] . "),"
                            . " ($productId,2,2,0," . $importData['msrp_price'] . "," . $importData['msrp_price'] . "," . $importData['msrp_price'] . "," . $importData['msrp_price'] . ")";
                    $write->query($insert);
                endif;
            endif;

            $this->_addAffectedEntityIds();

            // @desc : Toan LE.
            // I tried many times to update but i dont know how s the best way to update the image's attributes of product after save.
            // Maybe we need to use the Hook in magento. But now, i dont have more time to do that.
            if (isset($importData['product_type_id']) && $importData['product_type_id'] == 'configurable'):
                $product = mage::getModel('catalog/product')->load($productId);
                $attributes = $product->getTypeInstance(true)->getSetAttributes($product);
                $gallery = $attributes['media_gallery'];
                $images = $product->getMediaGalleryImages();
                foreach ($images as $image):
                    foreach ($simpleProducts as $simpleProduct):
                        $simpleProduct['stylecode'] = strtoupper($simpleProduct['stylecode']);
                        $simpleProduct['color'] = strtoupper(str_replace('/', '', $simpleProduct['color']));
                        if (preg_match("/{$simpleProduct['stylecode']}-{$simpleProduct['color']}/i", $image->getFile())):
                            if (is_file(Mage::getBaseDir('media') . '/import/products/' . strtoupper($simpleProduct['stylecode']) . '-' . strtoupper(str_replace('/', '', $simpleProduct['color'])) . '.jpg')):
                                $backend = $gallery->getBackend();
                                $backend->updateImage(
                                        $product, $image->getFile(), array('label' => strtolower(str_replace('/', '', $simpleProduct['color'])) . '-swatch')
                                );
                            endif;
                        else:
                        endif;
                        /*
                          if (isset($simpleProducts[$i]['color'])):
                          if (is_file(Mage::getBaseDir('media') . '/import/products/' . $simpleProducts[$i]['stylecode'] . '-' . $simpleProducts[$i]['color'] . '.jpg')):
                          $backend = $gallery->getBackend();
                          $backend->updateImage(
                          $product, $image->getFile(), array('label' => strtolower($simpleProducts[$i]['color']) . '-swatch')
                          );
                          endif;
                          endif;
                         * 
                         */
                    endforeach;
                endforeach;
                $product->getResource()->saveAttribute($product, 'media_gallery');
                $product->save();
            endif;
            return $productId;
        } elseif ($importProfile == 'update_date_available') {
            if ($productId) {
                $product->load($productId);

                foreach ($importData as $field => $value) {
                    if ($field == 'free_date') {
                        if (in_array($field, $this->_inventoryFields)) {
                            continue;
                        }
                        if (is_null($value)) {
                            continue;
                        }

                        $attribute = $this->getAttribute($field);


                        if (!$attribute) {
                            continue;
                        }

                        $isArray = false;
                        $setValue = $value;
                        if ($attribute->getFrontendInput() == 'multiselect') {
                            $value = explode(self::MULTI_DELIMITER, $value);
                            $isArray = true;
                            $setValue = array();
                        }

                        if ($value && $attribute->getBackendType() == 'decimal') {
                            $setValue = $this->getNumber($value);
                        }

                        if ($attribute->usesSource()) {

                            $options = $attribute->getSource()->getAllOptions(false);

                            if ($isArray) {
                                foreach ($options as $item) {
                                    if (in_array(strtolower($item['label']), array_map('strtolower', $value))) {
//                        if (in_array($item['label'], $value)) {
                                        $setValue[] = $item['value'];
                                    }
                                }
                            } else {
                                $setValue = false;
                                foreach ($options as $item) {
                                    if (is_array($item['value'])) {

                                        foreach ($item['value'] as $subValue) {

                                            if (isset($subValue['value']) && $subValue['value'] == $value) {
                                                $setValue = $value;
                                            }
                                        }
                                    } else if ($item['label'] == $value) {

                                        $setValue = $item['value'];
                                    }
                                }
                            }
                        }
                        $product->setData($field, $setValue);
                    } else {
                        unset($importData[$field]);
                        continue;
                    }
                }
                $product->setIsMassupdate(true);
                $product->setExcludeUrlRewrite(true);
                $product->save();
                return $product->getId();
            } else {
                return 0;
            }
        } elseif ($importProfile == 'update_stock') {
            if ($productId) {
                $product->load($productId);
                if (isset($importData['free_stock'])) {
                    $importData['qty'] = $importData['free_stock'];
                    unset($importData['free_stock']);
                } else {
                    $importData['qty'] = 0;
                }
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                $stockItem->setData('qty', (int) $importData['qty']);
                $stockItem->save();
                $product->setIsMassupdate(true);
                $product->setExcludeUrlRewrite(true);
                $product->save();
                return $product->getId();
            } else {
                return 0;
            }
        }
    }

    /**
     * Silently save product (import)
     *
     * @param  array $importData
     * @return bool
     */
    public function saveRowSilently(array $importData) {
        try {
            $result = $this->saveRow($importData);
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Process after import data
     * Init indexing process after catalog product import
     */
    public function finish() {
        /**
         * Back compatibility event
         */
        Mage::dispatchEvent($this->_eventPrefix . '_after', array());

        $entity = new Varien_Object();
        Mage::getSingleton('index/indexer')->processEntityAction(
                $entity, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
    }

}
