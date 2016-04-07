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
 * @category    Evince
 * @package        Evince_Ourteam
 * @copyright    Copyright (c) 2013
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Our Team view block
 *
 * @category    Evince
 * @package        Evince_Ourteam
 * @author Evince Development
 */
class Evince_Ourteam_Block_Ourteam_View extends Mage_Core_Block_Template
{
    /**
     * get the current our team
     * @access public
     * @return mixed (Evince_Ourteam_Model_Ourteam|null)
     * @author Evince Development
     */
    public function getCurrentOurteam()
    {
        return Mage::registry('current_ourteam_ourteam');
    }

    /**
     * Set meta data for our team
     */
    public function _prepareLayout()
    {
        $headBlock = Mage::app()->getLayout()->getBlock('head');

        if ($headBlock) {
            $currentOurTeam = $this->getCurrentOurteam();

            /* Reset meta title */
            if ($targetTitle = $currentOurTeam->getMetaTitle()) {
                $headBlock->setTitle($targetTitle);
            }

            /* Reset meta description */
            if ($targetDescription = $currentOurTeam->getMetaDescription()) {
                $headBlock->setDescription($targetDescription);
            }
        }
    }
} 