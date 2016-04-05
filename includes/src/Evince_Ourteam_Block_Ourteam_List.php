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
 * Our Team list block
 *
 * @category	Evince
 * @package		Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Block_Ourteam_List extends Mage_Core_Block_Template{
	/**
	 * initialize
	 * @access public
	 * @return void
	 * @author Evince Development
	 */
 	public function __construct(){
		parent::__construct();
 		$ourteams = Mage::getResourceModel('ourteam/ourteam_collection')
 						//->addStoreFilter(Mage::app()->getStore())
				->addFilter('status', 1)
		;
		$ourteams->setOrder('name', 'asc');
		$this->setOurteams($ourteams);
	}
	/**
	 * prepare the layout
	 * @access protected
	 * @return Evince_Ourteam_Block_Ourteam_List
	 * @author Evince Development
	 */
	protected function _prepareLayout(){
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager', 'ourteam.ourteam.html.pager')
			->setCollection($this->getOurteams());
		$this->setChild('pager', $pager);
		$this->getOurteams()->load();
		return $this;
	}
	/**
	 * get the pager html
	 * @access public
	 * @return string
	 * @author Evince Development
	 */
	public function getPagerHtml(){
		return $this->getChildHtml('pager');
	}
}