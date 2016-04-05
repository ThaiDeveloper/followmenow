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
 * Our Team front contrller
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_OurteamController extends Mage_Core_Controller_Front_Action{
	/**
 	 * default action
 	 * @access public
 	 * @return void
 	 * @author Evince Development
 	 */
 	public function indexAction(){
		$this->loadLayout();
 		if (Mage::helper('ourteam/ourteam')->getUseBreadcrumbs()){
			if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')){
				$breadcrumbBlock->addCrumb('home', array(
							'label'	=> Mage::helper('ourteam')->__('Home'), 
							'link' 	=> Mage::getUrl(),
						)
				);
				$breadcrumbBlock->addCrumb('ourteams', array(
							'label'	=> Mage::helper('ourteam')->__('Our Team'), 
							'link'	=> '',
					)
				);
			}
		}
		$this->renderLayout();
	}
	/**
 	 * view our team action
 	 * @access public
 	 * @return void
 	 * @author Evince Development
 	 */
	public function viewAction(){
		$ourteamId 	= $this->getRequest()->getParam('id', 0);
		$ourteam 	= Mage::getModel('ourteam/ourteam')
						//->setStoreId(Mage::app()->getStore()->getId())
						->load($ourteamId);
		if (!$ourteam->getId()){
			$this->_forward('no-route');
		}
		elseif (!$ourteam->getStatus()){
			$this->_forward('no-route');
		}
		else{
			Mage::register('current_ourteam_ourteam', $ourteam);
			$this->loadLayout();
			if ($root = $this->getLayout()->getBlock('root')) {
				$root->addBodyClass('ourteam-ourteam ourteam-ourteam' . $ourteam->getId());
			}
			if (Mage::helper('ourteam/ourteam')->getUseBreadcrumbs()){
				if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')){
					$breadcrumbBlock->addCrumb('home', array(
								'label'	=> Mage::helper('ourteam')->__('Home'), 
								'link' 	=> Mage::getUrl(),
							)
					);
					$breadcrumbBlock->addCrumb('ourteams', array(
								'label'	=> Mage::helper('ourteam')->__('Our Team'), 
								'link'	=> Mage::helper('ourteam')->getOurteamsUrl(),
						)
					);
					$breadcrumbBlock->addCrumb('ourteam', array(
								'label'	=> $ourteam->getName(), 
								'link'	=> '',
						)
					);
				}
			}
			$this->renderLayout();
		}
	}
}