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
 * Our Team admin block
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Block_Adminhtml_Ourteam extends Mage_Adminhtml_Block_Widget_Grid_Container{
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function __construct(){
		$this->_controller 		= 'adminhtml_ourteam';
		$this->_blockGroup 		= 'ourteam';
		$this->_headerText 		= Mage::helper('ourteam')->__('Team Member');
		$this->_addButtonLabel 	= Mage::helper('ourteam')->__('Add Team Member');
		parent::__construct();
	}
}