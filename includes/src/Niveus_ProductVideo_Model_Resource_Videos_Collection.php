<?php
/**
 * Video Plugin for Magento
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Niveus
 * @package    Niveus_ProductVideo
 * @copyright  Copyright (c) 2013 Niveus Solutions (http://www.niveussolutions.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Niveus Solutions <support@niveussolutions.com>
 */


class Niveus_ProductVideo_Model_Resource_Videos_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('productvideo/videos');
    }
}










