<?php

/**
 * iKantam
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade InstagramConnect to newer
 * versions in the future.
 *
 * @category    Ikantam
 * @package     Ikantam_InstagramConnect
 * @copyright   Copyright (c) 2012 iKantam LLC (http://www.ikantam.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ikantam_InstagramConnect_Block_Team extends Mage_Core_Block_Template {

    /**
     * Retrieve list of gallery images
     *
     * @return array|Varien_Data_Collection
     */
    public function getInstagramGalleryImages($memberid, $withPrefix = true) {
        $teamModel = Mage::getModel('ourteam/ourteam')->load($memberid);
        $rawTags = $teamModel->getData('instagram');

        if ($rawTags) {
            $tags = explode(',', $rawTags);
            $out = array();
            foreach ($tags as $tag) {
                $tag = ltrim(trim($tag), '#');
                $tag = ltrim(trim($tag), '@');
                if (!empty($tag)) {
                    $out[] = '#' . $tag;
                    $out[] = '@' . $tag;
                }
            }

//            $tags = Mage::helper('instagramconnect/image')->getTags();
//            $users = Mage::helper('instagramconnect/image_user')->getUsers();
//            $tags = array_merge($tags, $out);
            $limit = Mage::helper('instagramconnect')->getTeamLimit();
            if (count($out)) {
                $tagsCollection = Mage::getModel('instagramconnect/instagramimage')
                        ->getCollection();
                $select = $tagsCollection->getSelect()
                        ->where('is_approved = 1')
                        ->where('is_visible = 1')
                        ->order('dateapproved DESC')
                        ->where('tag IN (\'' . implode("','", $out) . '\')')
                        ->orWhere('username IN (\'' . implode("','", $out) . '\')');// ko anh huong 
                if ($limit) {
                    $select->limit($limit);
                }
                $results = $select->query()->fetchAll();
                return $results;
            }
        }
    }

}
