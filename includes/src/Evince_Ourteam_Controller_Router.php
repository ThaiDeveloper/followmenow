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
 * Router
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract{
	/**
	 * init routes
	 * @access public
	 * @param Varien_Event_Observer $observer
	 * @return Evince_Ourteam_Controller_Router
	 * @author Evince Development
	 */
	public function initControllerRouters($observer){
		$front = $observer->getEvent()->getFront();
		$front->addRouter('ourteam', $this);
		return $this;
	}
	/**
	 * Validate and match entities and modify request
	 * @access public
	 * @param Zend_Controller_Request_Http $request
	 * @return bool
	 * @author Evince Development
	 */
	public function match(Zend_Controller_Request_Http $request){
		if (!Mage::isInstalled()) {
			Mage::app()->getFrontController()->getResponse()
				->setRedirect(Mage::getUrl('install'))
				->sendResponse();
			exit;
		}
		$urlKey = trim($request->getPathInfo(), '/');
		$check = array();
		$check['ourteam'] = new Varien_Object(array(
			'model' =>'ourteam/ourteam',
			'controller' => 'ourteam',
			'action' => 'view',
			'param'	=> 'id',
		));
		foreach ($check as $key=>$settings){
			$model = Mage::getModel($settings->getModel());
			$id = $model->checkUrlKey($urlKey, Mage::app()->getStore()->getId());
			if ($id){
				$request->setModuleName('ourteam')
					->setControllerName($settings->getController())
					->setActionName($settings->getAction())
					->setParam($settings->getParam(), $id);
				$request->setAlias(
					Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
					$urlKey
				);
				return true;
			}
		}
		return false;
	}
}