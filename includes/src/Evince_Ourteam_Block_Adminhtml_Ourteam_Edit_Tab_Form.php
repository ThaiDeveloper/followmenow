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
 * Our Team edit form tab
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Block_Adminhtml_Ourteam_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form{	
	/**
	 * prepare the form
	 * @access protected
	 * @return Ourteam_Ourteam_Block_Adminhtml_Ourteam_Edit_Tab_Form
	 * @author Evince Development
	 */
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$form->setHtmlIdPrefix('ourteam_');
		$form->setFieldNameSuffix('ourteam');
		$this->setForm($form);
		$fieldset = $form->addFieldset('ourteam_form', array('legend'=>Mage::helper('ourteam')->__('Our Team')));
		$fieldset->addType('image', Mage::getConfig()->getBlockClassName('ourteam/adminhtml_ourteam_helper_image'));
		$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();

		$fieldset->addField('name', 'text', array(
			'label' => Mage::helper('ourteam')->__('Name'),
			'name'  => 'name',
			'required'  => true,
			'class' => 'required-entry',

		));

		$fieldset->addField('image', 'image', array(
			'label' => Mage::helper('ourteam')->__('Image'),
			'name'  => 'image',

		));

		$fieldset->addField('designation', 'text', array(
			'label' => Mage::helper('ourteam')->__('Designation'),
			'name'  => 'designation',
			'required'  => true,
			'class' => 'required-entry',

		));

		$fieldset->addField('description', 'editor', array(
			'label' => Mage::helper('ourteam')->__('Description'),
			'name'  => 'description',
			'config'	=> $wysiwygConfig,
			'required'  => true,
			'class' => 'required-entry',

		));
		$fieldset->addField('status', 'select', array(
			'label' => Mage::helper('ourteam')->__('Status'),
			'name'  => 'status',
			'values'=> array(
				array(
					'value' => 1,
					'label' => Mage::helper('ourteam')->__('Enabled'),
				),
				array(
					'value' => 0,
					'label' => Mage::helper('ourteam')->__('Disabled'),
				),
			),
		));
		if (Mage::getSingleton('adminhtml/session')->getOurteamData()){
			$form->setValues(Mage::getSingleton('adminhtml/session')->getOurteamData());
			Mage::getSingleton('adminhtml/session')->setOurteamData(null);
		}
		elseif (Mage::registry('current_ourteam')){
			$form->setValues(Mage::registry('current_ourteam')->getData());
		}
		return parent::_prepareForm();
	}
}