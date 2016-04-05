<?php

class Infortis_Ultimo_Helper_Template_Catalog_Product_View extends Mage_Core_Helper_Abstract {

    /**
     * Create grid classes for product page sections
     *
     * @return array
     */
    public function getGridClasses() {
        $theme = Mage::helper('ultimo');

        //Width (in grid units) of product page sections
        $imgColUnits = $theme->getCfg('product_page/image_column');
        $primColUnits = $theme->getCfg('product_page/primary_column');
        $secColUnits = $theme->getCfg('product_page/secondary_column');
        $cont2ColUnits = $theme->getCfg('product_page/container2_column'); //$imgColUnits + $primColUnits;
        $lowerPrimColUnits = $theme->getCfg('product_page/lower_primary_column');
        $lowerSecColUnits = $theme->getCfg('product_page/lower_secondary_column');

        //TODO: may be a good idea to check if any section has 0 units.
        //Grid classes
        $classPrefix = 'grid12-';
        $classFullWidth = 'grid12-12';

        $grid['imgCol'] = $classPrefix . $imgColUnits;

        $grid['primCol'] = $classPrefix . $primColUnits;

        if (!empty($secColUnits)) {
            $grid['secCol'] = $classPrefix . $secColUnits;
        }

        $grid['cont2Col'] = $classPrefix . $cont2ColUnits;

        $grid['lowerPrimCol'] = $classPrefix . $lowerPrimColUnits;

        if (!empty($lowerSecColUnits)) {
            $grid['lowerSecCol'] = $classPrefix . $lowerSecColUnits;
        }

        return $grid;
    }

    public function getCmsBlockTitle($id) {
        return Mage::getModel('cms/block')->setStoreId(Mage::app()->getStore()->getId())->load($id)->getTitle();
    }

    public function getProductSameStyleCode($product, $attr) {
        $products = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect(array('stylecode', 'color'))
                ->addAttributeToFilter('status', 1) // enabled
                ->addAttributeToFilter('visibility', array(2, 4)) //visibility in catalog,search
                ->groupByAttribute(array('stylecode', 'color'))
                ->addAttributeToFilter('color', $attr)
                ->addAttributeToFilter('stylecode', $product->getStylecode())
                ->setOrder('pice', 'desc')
                ->setOrder('id', 'desc')
                ->setPage('1', '1');
        if ($products):
            foreach ($products as $prod) {
                return $prod;
            }
        endif;
        return null;
    }

}
