<?php
/**
 * Evince_Ourteam extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category   	Evince
 * @package		Evince_Ourteam
 * @copyright  	Copyright (c) 2013
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Our Team admin controller
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Adminhtml_Ourteam_OurteamController extends Evince_Ourteam_Controller_Adminhtml_Ourteam{
	/**
	 * init the ourteam
	 * @access protected
	 * @return Evince_Ourteam_Model_Ourteam
	 */
	protected function _initOurteam(){
		$ourteamId  = (int) $this->getRequest()->getParam('id');
		$ourteam	= Mage::getModel('ourteam/ourteam');
		if ($ourteamId) {
			$ourteam->load($ourteamId);
		}
		Mage::register('current_ourteam', $ourteam);
		return $ourteam;
	}
 	/**
	 * default action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function indexAction() {
		$this->loadLayout();
		$this->_title(Mage::helper('ourteam')->__('Ourteam'))
			 ->_title(Mage::helper('ourteam')->__('Our Team'));
		$this->renderLayout();
	}
	/**
	 * grid action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function gridAction() {
		$this->loadLayout()->renderLayout();
	}
	/**
	 * edit our team - action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function editAction() {
		$ourteamId	= $this->getRequest()->getParam('id');
		$ourteam  	= $this->_initOurteam();
		if ($ourteamId && !$ourteam->getId()) {
			$this->_getSession()->addError(Mage::helper('ourteam')->__('This our team no longer exists.'));
			$this->_redirect('*/*/');
			return;
		}
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$ourteam->setData($data);
		}
		Mage::register('ourteam_data', $ourteam);
		$this->loadLayout();
		$this->_title(Mage::helper('ourteam')->__('Ourteam'))
			 ->_title(Mage::helper('ourteam')->__('Our Team'));
		if ($ourteam->getId()){
			$this->_title($ourteam->getName());
		}
		else{
			$this->_title(Mage::helper('ourteam')->__('Add our team'));
		}
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) { 
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true); 
		}
		$this->renderLayout();
	}
	/**
	 * new our team action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function newAction() {
		$this->_forward('edit');
	}
	/**
	 * save our team - action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function saveAction() {
		if ($data = $this->getRequest()->getPost('ourteam')) {
			try {
				$ourteam = $this->_initOurteam();
				$ourteam->addData($data);
				$imageName = $this->_uploadAndGetName('image', Mage::helper('ourteam/ourteam_image')->getImageBaseDir(), $data);
				$ourteam->setData('image', $imageName);
				$ourteam->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ourteam')->__('Our Team was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $ourteam->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			} 
			catch (Mage_Core_Exception $e){
				if (isset($data['image']['value'])){
					$data['image'] = $data['image']['value'];
				}
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
			catch (Exception $e) {
				Mage::logException($e);
				if (isset($data['image']['value'])){
					$data['image'] = $data['image']['value'];
				}
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ourteam')->__('There was a problem saving the our team.'));
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ourteam')->__('Unable to find our team to save.'));
		$this->_redirect('*/*/');
	}
	/**
	 * delete our team - action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0) {
			try {
				$ourteam = Mage::getModel('ourteam/ourteam');
				$ourteam->setId($this->getRequest()->getParam('id'))->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ourteam')->__('Our Team was successfully deleted.'));
				$this->_redirect('*/*/');
				return; 
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ourteam')->__('There was an error deleteing our team.'));
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				Mage::logException($e);
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ourteam')->__('Could not find our team to delete.'));
		$this->_redirect('*/*/');
	}
	/**
	 * mass delete our team - action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function massDeleteAction() {
		$ourteamIds = $this->getRequest()->getParam('ourteam');
		if(!is_array($ourteamIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ourteam')->__('Please select our team to delete.'));
		}
		else {
			try {
				foreach ($ourteamIds as $ourteamId) {
					$ourteam = Mage::getModel('ourteam/ourteam');
					$ourteam->setId($ourteamId)->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ourteam')->__('Total of %d our team were successfully deleted.', count($ourteamIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ourteam')->__('There was an error deleteing our team.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass status change - action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function massStatusAction(){
		$ourteamIds = $this->getRequest()->getParam('ourteam');
		if(!is_array($ourteamIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ourteam')->__('Please select our team.'));
		} 
		else {
			try {
				foreach ($ourteamIds as $ourteamId) {
				$ourteam = Mage::getSingleton('ourteam/ourteam')->load($ourteamId)
							->setStatus($this->getRequest()->getParam('status'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d our team were successfully updated.', count($ourteamIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ourteam')->__('There was an error updating our team.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * export as csv - action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function exportCsvAction(){
		$fileName   = 'ourteam.csv';
		$content	= $this->getLayout()->createBlock('ourteam/adminhtml_ourteam_grid')->getCsv();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	/**
	 * export as MsExcel - action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function exportExcelAction(){
		$fileName   = 'ourteam.xls';
		$content	= $this->getLayout()->createBlock('ourteam/adminhtml_ourteam_grid')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	/**
	 * export as xml - action
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function exportXmlAction(){
		$fileName   = 'ourteam.xml';
		$content	= $this->getLayout()->createBlock('ourteam/adminhtml_ourteam_grid')->getXml();
		$this->_prepareDownloadResponse($fileName, $content);
	}
}