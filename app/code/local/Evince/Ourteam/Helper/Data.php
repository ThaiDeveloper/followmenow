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
 * Ourteam default helper
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Helper_Data extends Mage_Core_Helper_Abstract{
	/**
	 * get the url to the our team list page
	 * @access public
	 * @return string
	 * @author Evince Development
	 */
	public function getOurteamsUrl(){
		return Mage::getUrl('ourteam/ourteam/index');
	}
}