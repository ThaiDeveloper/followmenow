<?php

class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product extends Mage_Catalog_Model_Product {

    public function getMaxPossibleFinalPrice() {
        if (is_callable(array($this->getPriceModel(), 'getMaxPossibleFinalPrice'))) {
            return $this->getPriceModel()->getMaxPossibleFinalPrice($this);
        } else {
            #return $this->_getData('minimal_price');
            return parent::getMaxPrice();
        }
    }

    public function isVisibleInSiteVisibility() {
        #Force visible any simple products which have a parent conf product.
        #this will only apply to products which have been added to the cart
        if (is_callable(array($this->getTypeInstance(), 'hasConfigurableProductParentId')) && $this->getTypeInstance()->hasConfigurableProductParentId()) {
            return true;
        } else {
            return parent::isVisibleInSiteVisibility();
        }
    }

    public function getMediaGalleryImages($disable = false, $is_all = false) {
        if (!$this->hasData('media_gallery_images') && is_array($this->getMediaGallery('images'))) {
            $images = new Varien_Data_Collection();
            foreach ($this->getMediaGallery('images') as $image) {
                if (!$disable) {
                    if ($image['disabled']) {// anh exclude
                        continue;
                    }
                    $image['url'] = $this->getMediaConfig()->getMediaUrl($image['file']);
                    $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                    $image['path'] = $this->getMediaConfig()->getMediaPath($image['file']);
                    $images->addItem(new Varien_Object($image));
                } else {
                    if ($is_all):
                        $image['url'] = $this->getMediaConfig()->getMediaUrl($image['file']);
                        $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                        $image['path'] = $this->getMediaConfig()->getMediaPath($image['file']);
                        $images->addItem(new Varien_Object($image));
                    else:
                        if ($image['disabled']) {// anh exclude
                            $image['url'] = $this->getMediaConfig()->getMediaUrl($image['file']);
                            $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                            $image['path'] = $this->getMediaConfig()->getMediaPath($image['file']);
                            $images->addItem(new Varien_Object($image));
                        } else {// anh binh thuong
                            continue;
                        }
                    endif;
                }
            }
            $this->setData('media_gallery_images', $images);
        }

        return $this->getData('media_gallery_images');
    }

}
