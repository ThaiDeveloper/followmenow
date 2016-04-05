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
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass() {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('sales')->__('Purchased From (Store)'),
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action', array(
                'header' => Mage::helper('sales')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url' => array('base' => '*/sales_order/view'),
                        'field' => 'order_id',
                        'data-column' => 'action',
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));
        }
        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                'label' => Mage::helper('sales')->__('Cancel'),
                'url' => $this->getUrl('*/sales_order/massCancel'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                'label' => Mage::helper('sales')->__('Hold'),
                'url' => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                'label' => Mage::helper('sales')->__('Unhold'),
                'url' => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
            'label' => Mage::helper('sales')->__('Print Invoices'),
            'url' => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
            'label' => Mage::helper('sales')->__('Print Packingslips'),
            'url' => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
            'label' => Mage::helper('sales')->__('Print Credit Memos'),
            'url' => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
            'label' => Mage::helper('sales')->__('Print All'),
            'url' => $this->getUrl('*/sales_order/pdfdocs'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array(
            'label' => Mage::helper('sales')->__('Print Shipping Labels'),
            'url' => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));

        return $this;
    }

    public function getRowUrl($row) {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
    public function getCsvFile()
    {
        $this->_isExport = true;
        $this->_prepareGrid();

        $io = new Varien_Io_File();

        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name = md5(microtime());
        $file = $path . DS . $name . '.csv';

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWriteCsv($this->_getExportHeaders());

        $this->_exportIterateCollection('_exportCsvItem', array($io));
        $rowE = array('E');//fixed 
        $rowE[] = 'EOF';//fixed 
        $io->streamWriteCsv($rowE);
        if ($this->getCountTotals()) {
            $io->streamWriteCsv($this->_getExportTotals());
        }

        $io->streamUnlock();
        $io->streamClose();

        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true // can delete file after use
        );
    }
    protected function _exportCsvItem(Varien_Object $item, Varien_Io_File $adapter) {

        $tmp = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $tmp[] = $column->getRowFieldExport($item);
            }
        }
        $order = Mage::getModel('sales/order')->loadByIncrementId($tmp[0]);
        $items = $order->getAllItems();

        $shippingAddress = Mage::getModel('sales/order_address')->load($order->getShippingAddressId());
        $billingAddress = Mage::getModel('sales/order_address')->load($order->getBillingAddressId());
        $shipping = $shippingAddress->getData();
        $billing = $billingAddress->getData();
        $customerId = $order->getCustomerId();
         $rowHs = array();
        $countryNameShip = Mage::app()->getLocale()->getCountryTranslation($shipping['country_id']);
        $countryNameBilling = Mage::app()->getLocale()->getCountryTranslation($billing['country_id']);
        foreach ($items as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
           
           
            if (!isset($rowHs[$tmp[0]])) {
                
                $rowH = array('H'); // fixed  - 1
                $rowH[] = $tmp[0]; //order id 8776 - 2 
                $rowH[] = date(Ymd, strtotime($tmp[2])); //date 20150812 - 3
                $rowH[] = number_format(floatval($item->getBaseDiscountAmount()), 2); // discount - 0.00 - 4
                $rowH[] = $customerId; // 15521 -sku - 5
                $rowH[] = $shipping['lastname'] . ' ' . $shipping['middlename'] . ' ' . $shipping['firstname']; // name - 6
                $rowH[] = ''; // none - 7
                $rowH[] = $shipping['street']; // 24 tweedmouth avenue - 8 
                $rowH[] = ''; // none - 9?
                $rowH[] = $shipping['city']; // sydney - 10
                $rowH[] = $shipping['region']; // NSW - 11
                $rowH[] = $shipping['postcode']; // 25118 - 12
                $rowH[] = $countryNameShip; // Australia - 13
                $rowH[] = $shipping['email']; // william.iskandar@gmail.com - 14
                $rowH[] = $shipping['telephone']; // 0433301231 - 15
                $rowH[] = ''; // none - 16
                $rowH[] = ''; // none - 17
                $rowH[] = $billing['lastname'] . ' ' . $billing['middlename'] . ' ' . $billing['firstname']; // name - 18
                $rowH[] = ''; // none - 19
                $rowH[] = $billing['street']; // 24 tweedmouth avenue - 20
                $rowH[] = ''; // none - 21
                $rowH[] = $billing['city']; // sydney - 22
                $rowH[] = $billing['region']; // NSW - 23
                $rowH[] = $billing['postcode']; // 25118 - 24
                $rowH[] = $countryNameBilling; // Australia - 25
                $rowHs[$tmp[0]] = $rowH;
                $adapter->streamWriteCsv($rowH);
            }
            $rowD = array('D'); //fixed - 26
            $product_name = $item->getName();
            if($size = $product->getAttributeText('size')){
                $product_name .=' - '.$size;
            }
            if($color = $product->getAttributeText('color')){
                $product_name .=' - '.$color;
            }            
            $rowD[] = $product_name; //THE CAUSE PFD3 F/E NEO VEST - BLACK - XL - 27
            $rowD[] = $item->getSku(); //155219 - 28 
            $rowD[] = $item->getData('qty_ordered'); //qty - 1 - 29
            $rowD[] = $item->getPrice(); //price - 149.99 - 30
          
            $adapter->streamWriteCsv($rowD);
        }
       
        //H,8776,20150812,0.00,15521,
        //Josepine Kimberley,,24 tweedmouth avenue,,sydney,
        //NSW,25118,Australia,william.iskandar@gmail.com,0433301231
        //,,,Josepine Kimberley,,24 tweedmouth avenue
        //,,sydney,NSW,25118,Australia
        //,D,THE CAUSE PFD3 F/E NEO VEST - BLACK - XL,155219,1,149.99,E,EOF'
    }

}
