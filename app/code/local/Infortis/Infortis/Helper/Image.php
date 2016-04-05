<?php

class Infortis_Infortis_Helper_Image extends Mage_Core_Helper_Abstract {

    /**
     * Get image URL of the given product
     *
     * @param Mage_Catalog_Model_Product	$product		Product
     * @param int							$w				Image width
     * @param int							$h				Image height
     * @param string						$imgVersion		Image version: image, small_image, thumbnail
     * @param mixed							$file			Specific file
     * @return string
     */
    public function getImg($product, $w, $h, $imgVersion = 'image', $file = NULL) {
        $url = '';
        if ($h <= 0) {
            $url = Mage::helper('catalog/image')
                    ->init($product, $imgVersion, $file)
                    ->constrainOnly(true)
                    ->keepAspectRatio(true)
                    ->keepFrame(false)
                    //->setQuality(90)
                    ->resize($w);
        } else {
            $url = Mage::helper('catalog/image')
                    ->init($product, $imgVersion, $file)
                    ->resize($w, $h);
        }
        return $url;
    }

    // bainguyen resize dont change color
    public function getImage($_product_id, $imgVersion = 'image') {
        $_product = Mage::getModel('catalog/product')->load($_product_id);
        $image = $_product->getImage();
        if ($imgVersion == 'small_image' || !$image) {

            $image = $_product->getSmallImage();
        }
        $imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($image);
        $imagePath = Mage::getModel('catalog/product_media_config')->getMediaPath($image);

        if (is_file($imagePath)) {
            return $imageUrl;
        } else {
            $placeholder = Mage::getStoreConfig('catalog/placeholder');
            $phPath = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . 'placeholder' . DS . $placeholder[$imgVersion . '_placeholder'];
            if (is_file($phPath)) {
                return Mage::getBaseUrl('media') . 'catalog/product/placeholder/' . $placeholder[$imgVersion . '_placeholder'];
            }
        }
    }

}
