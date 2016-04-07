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
class Evince_Ourteam_Block_Adminhtml_Ourteam_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare the form
     * @access protected
     * @return Ourteam_Ourteam_Block_Adminhtml_Ourteam_Edit_Tab_Form
     * @author Evince Development
     */
    protected function _prepareForm() {
        $model = Mage::registry('ourteam_data');
        $data = $model->getData();

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('ourteam_');
        $form->setFieldNameSuffix('ourteam');
        $this->setForm($form);
        $fieldset = $form->addFieldset('ourteam_form', array('legend' => Mage::helper('ourteam')->__('Our Team')));
        $fieldset->addType('image', Mage::getConfig()->getBlockClassName('ourteam/adminhtml_ourteam_helper_image'));
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('ourteam')->__('Name'),
            'name' => 'name',
            'required' => true,
            'class' => 'required-entry',
        ));

        $fieldset->addField('image', 'image', array(
            'label' => Mage::helper('ourteam')->__('Image'),
            'name' => 'image',
        ));

        $fieldset->addField('designation', 'text', array(
            'label' => Mage::helper('ourteam')->__('Designation'),
            'name' => 'designation',
            'required' => true,
            'class' => 'required-entry',
        ));
        $fieldset->addField('instagram', 'text', array(
            'label' => Mage::helper('ourteam')->__('Instagram Tag'),
            'name' => 'instagram',
            'required' => true,
            'class' => 'required-entry',
            'after_element_html' => '<p class="nm"><small>'.Mage::helper('ourteam')->__('This tag must be in tags list configured "Instagram Connect"').'</small></p>',
        ));

        $fieldset->addField('description', 'editor', array(
            'label' => Mage::helper('ourteam')->__('Description'),
            'name' => 'description',
            'config' => $wysiwygConfig,
            'required' => true,
            'class' => 'required-entry',
        ));
        $fieldset->addField('pro_team', 'checkbox', array(
            'label' => Mage::helper('ourteam')->__('Set Pro Team?'),
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'checked' => (int) $data['pro_team'] > 0 ? 'checked' : '',
            'name' => 'pro_team'
            )
        );
        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('ourteam')->__('Status'),
            'name' => 'status',
            'values' => array(
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
        /* META */

        $fieldset->addField('meta_title', 'text', array(
            'label' => Mage::helper('ourteam')->__('Meta Title'),
            'name' => 'meta_title',
            'required' => false,
        ));

        $fieldset->addField('meta_description', 'textarea', array(
            'label' => Mage::helper('ourteam')->__('Meta Description'),
            'name' => 'meta_description',
            'required' => false,
        ));

        /* END META */
        if (Mage::getSingleton('adminhtml/session')->getOurteamData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getOurteamData());
            Mage::getSingleton('adminhtml/session')->setOurteamData(null);
        } elseif (Mage::registry('current_ourteam')) {
            $form->setValues(Mage::registry('current_ourteam')->getData());
        }
        return parent::_prepareForm();
    }

}
