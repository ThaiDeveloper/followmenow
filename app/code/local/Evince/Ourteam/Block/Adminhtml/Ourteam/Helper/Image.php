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
 * Our Team image field renderer helper
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Block_Adminhtml_Ourteam_Helper_Image extends Varien_Data_Form_Element_Image{
	/**
	 * get the url of the image
	 * @access protected
	 * @return string
	 * @author Evince Development
	 */
	protected function _getUrl(){
		$url = false;
		if ($this->getValue()) {
			$url = Mage::helper('ourteam/ourteam_image')->getImageBaseUrl().$this->getValue();
		}
		return $url;
	}
}
 