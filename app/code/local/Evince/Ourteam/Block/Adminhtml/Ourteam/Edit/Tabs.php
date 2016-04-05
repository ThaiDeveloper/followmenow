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
 * Our Team admin edit tabs
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Block_Adminhtml_Ourteam_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs{
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function __construct(){
		parent::__construct();
		$this->setId('ourteam_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('ourteam')->__('Our Team'));
	}
	/**
	 * before render html
	 * @access protected
	 * @return Evince_Ourteam_Block_Adminhtml_Ourteam_Edit_Tabs
	 * @author Evince Development
	 */
	protected function _beforeToHtml(){
		$this->addTab('form_ourteam', array(
			'label'		=> Mage::helper('ourteam')->__('Our Team'),
			'title'		=> Mage::helper('ourteam')->__('Our Team'),
			'content' 	=> $this->getLayout()->createBlock('ourteam/adminhtml_ourteam_edit_tab_form')->toHtml(),
		));
		if (!Mage::app()->isSingleStoreMode()){
			$this->addTab('form_store_ourteam', array(
				'label'		=> Mage::helper('ourteam')->__('Store views'),
				'title'		=> Mage::helper('ourteam')->__('Store views'),
				'content' 	=> $this->getLayout()->createBlock('ourteam/adminhtml_ourteam_edit_tab_stores')->toHtml(),
			));
		}
		return parent::_beforeToHtml();
	}
}