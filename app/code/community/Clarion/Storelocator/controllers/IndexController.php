<?php

/**
 * Fontend index controller
 * 
 * @category    Clarion
 * @package     Clarion_Storelocator
 * @author      Clarion Magento Team
 * 
 */
class Clarion_Storelocator_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * Pre dispatch action that allows to redirect to no route page in case of disabled extension through admin panel
     */
    public function preDispatch() {
        parent::preDispatch();

        if (!Mage::helper('clarion_storelocator')->isEnabled()) {
            $this->setFlag('', 'no-dispatch', true);
            $this->_redirect('noRoute');
        }
    }

    function indexAction() {
        $this->loadLayout();

        $listBlock = $this->getLayout()->getBlock('stores.list');
        if ($listBlock) {
            $currentPage = abs(intval($this->getRequest()->getParam('p')));
            if ($currentPage < 1) {
                $currentPage = 1;
            }
            $listBlock->setCurrentPage($currentPage);
        }

        $this->renderLayout();
    }

    /**
     * Stores view action
     */
    public function viewAction() {
        $storelocatorId = $this->getRequest()->getParam('id');
        if (!$storelocatorId) {
            return $this->_forward('noRoute');
        }

        /** @var $model Clarion_Storelocator_Model_Storelocator */
        $model = Mage::getModel('clarion_storelocator/storelocator')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($storelocatorId);

        if (!$model->getId()) {
            return $this->_forward('noRoute');
        }
        Mage::register('store_view', $model);
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Stores search action
     */
    public function searchAction() {
        //search form validation
        $radius = $_REQUEST['radius'];
        $limit = $_REQUEST['num_results'];
        $lng = $_REQUEST['lng'];
        $lat = $_REQUEST['lat'];

        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $read = $resource->getConnection('core_read');

//        $query = "SELECT `GETDISTANCE` (a.`latitude`, a.`longitude`, $lat, $lng) AS distance ,a.* "
//                . "FROM  `clarion_storelocator` AS a WHERE `GETDISTANCE` (a.`latitude`, a.`longitude`, $lat, $lng) < $radius limit $limit";
        $query = "SELECT *, ( 6371 * ACOS (COS ( RADIANS($lat) ) "
                . "* COS( RADIANS( latitude ) ) "
                . "* COS( RADIANS( longitude ) - RADIANS($lng) ) "
                . "+ SIN ( RADIANS($lat) ) "
                . "* SIN( RADIANS( latitude ) ) ) ) "
                . "AS distance FROM clarion_storelocator ";

        if ($radius):
            $query .= " HAVING distance < $radius ";
        endif;
        $query .= " ORDER BY distance ";
        if ($limit):
            $query .= " LIMIT $limit";
        endif;

        $results = $read->fetchAll($query);
        die(json_encode($results));
    }

    public function ajaxDealerAction() {
        $sql = 'SELECT * FROM `clarion_storelocator` as `d` JOIN `clarion_storelocator_store` as `s` ON d.storelocator_id = s.storelocator_id WHERE s.store_id = 0';
        $store = Mage::app()->getStore();
        if ($store) {
            $sql .= ' OR s.store_id = ' . $store->getStoreId();
        }
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $results = $read->fetchAll($sql);
        $return = array();
        if (!empty($results)):
            foreach ($results as $result):
                if ($store->getStoreId() == 3):
                    $result['countryname'] = Mage::app()->getLocale()->getCountryTranslation($result['country']);
                endif;
                $return[] = $result;
            endforeach;
        endif;
        die(json_encode($return));
    }

}
