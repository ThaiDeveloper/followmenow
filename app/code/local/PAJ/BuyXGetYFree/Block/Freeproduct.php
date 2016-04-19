<?php

/**
 * Class PAJ_BuyXGetYFree_Block_Freeproduct
 */
class PAJ_BuyXGetYFree_Block_Freeproduct extends Mage_Catalog_Block_Product_Abstract
{
    public function isShowPopup()
    {
        $quote = Mage::getModel('checkout/session')->getQuote();
        $quoteData = $quote->getData();
        $grandTotal = $quoteData['grand_total'];

        $spendCartTotalRequired = Mage::getStoreConfig('buyxgetyfree_section2/general/spend_cart_total_required');

        if ((float)$grandTotal < (float)$spendCartTotalRequired) {
            return false;
        }

        $freeProductId = explode(",", Mage::getStoreConfig('buyxgetyfree_section2/general/spend_producty_product_id'));

        foreach ($quote->getAllItems() as $_item) {

            $productId = $_item->getProduct()->getId();

            if (in_array($productId, $freeProductId)) {
                return false;
            }

        }

        return true;

    }


    public function getFreeProducts()
    {
        $configIds = explode(",", Mage::getStoreConfig('buyxgetyfree_section2/general/spend_producty_product_id'));
        $products = array();

        if (!$configIds || $configIds == '' || !$this->isShowPopup()) {
            return $products;
        }

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('entity_id', array('in' => $configIds));


        return $collection;
    }
}