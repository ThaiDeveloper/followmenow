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
 * Our Team admin edit block
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Block_Adminhtml_Ourteam_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
	/**
	 * constuctor
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function __construct(){
		parent::__construct();
		$this->_blockGroup = 'ourteam';
		$this->_controller = 'adminhtml_ourteam';
		$this->_updateButton('save', 'label', Mage::helper('ourteam')->__('Save Team Member'));
		$this->_updateButton('delete', 'label', Mage::helper('ourteam')->__('Delete Team Member'));
		$this->_addButton('saveandcontinue', array(
			'label'		=> Mage::helper('ourteam')->__('Save And Continue Edit'),
			'onclick'	=> 'saveAndContinueEdit()',
			'class'		=> 'save',
		), -100);
		$this->_formScripts[] = "
			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
	}
	/**
	 * get the edit form header
	 * @access public
	 * @return string
	 * @author Evince Development
	 */
	public function getHeaderText(){
		if( Mage::registry('ourteam_data') && Mage::registry('ourteam_data')->getId() ) {
			return Mage::helper('ourteam')->__("Edit Team Member '%s'", $this->htmlEscape(Mage::registry('ourteam_data')->getName()));
		} 
		else {
			return Mage::helper('ourteam')->__('Add Team Member');
		}
	}
}