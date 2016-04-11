<?php

class PAJ_BuyXGetYFree_Model_Observer
{
    public function checkoutCartProductAddAfter(Varien_Event_Observer $observer)
    {
        $item = $observer->getQuoteItem();
        $productId = $item->getProductId();

        $spendProductYID = explode(",", Mage::getStoreConfig('buyxgetyfree_section2/general/spend_producty_product_id'));

        if (in_array($productId, $spendProductYID)) {

            $item->setCustomPrice(0);
            $item->setOriginalCustomPrice(0);
            $item->getProduct()->setIsSuperMode(true);

        }
    }
}