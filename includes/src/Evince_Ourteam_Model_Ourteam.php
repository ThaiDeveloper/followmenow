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
 * Our Team model
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Model_Ourteam extends Mage_Core_Model_Abstract{
	/**
	 * Entity code.
	 * Can be used as part of method name for entity processing
	 */
	const ENTITY= 'ourteam_ourteam';
	const CACHE_TAG = 'ourteam_ourteam';
	/**
	 * Prefix of model events names
	 * @var string
	 */
	protected $_eventPrefix = 'ourteam_ourteam';
	
	/**
	 * Parameter name in event
	 * @var string
	 */
	protected $_eventObject = 'ourteam';
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
	public function _construct(){
		parent::_construct();
		$this->_init('ourteam/ourteam');
	}
	/**
	 * before save our team
	 * @access protected
	 * @return Evince_Ourteam_Model_Ourteam
	 * @author Evince Development
	 */
	protected function _beforeSave(){
		parent::_beforeSave();
		$now = Mage::getSingleton('core/date')->gmtDate();
		if ($this->isObjectNew()){
			$this->setCreatedAt($now);
		}
		$this->setUpdatedAt($now);
		return $this;
	}
	/**
	 * get the url to the our team details page
	 * @access public
	 * @return string
	 * @author Evince Development
	 */
	public function getOurteamUrl(){
		return Mage::getUrl('ourteam/ourteam/view', array('id'=>$this->getId()));
	}
	/**
	 * get the ourteam Description
	 * @access public
	 * @return string
	 * @author Evince Development
	 */
	public function getDescription(){
		$description = $this->getData('description');
		$helper = Mage::helper('cms');
		$processor = $helper->getBlockTemplateProcessor();
		$html = $processor->filter($description);
		return $html;
	}
	/**
	 * save ourteam relation
	 * @access public
	 * @return Evince_Ourteam_Model_Ourteam
	 * @author Evince Development
	 */
	protected function _afterSave() {
		return parent::_afterSave();
	}
}