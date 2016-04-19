<?php

class PAJ_BuyXGetYFree_Model_Observer
{
    /**
     * @desc Check if free product then set price down to zero
     *
     * @param Varien_Event_Observer $observer
     */
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

    /**
     * @desc Limit qty of free product is only 1
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function checkoutCartUpdateItemsAfter(Varien_Event_Observer $observer)
    {
        $info = $observer->getEvent()->getInfo();
        $spendProductYID = explode(",", Mage::getStoreConfig('buyxgetyfree_section2/general/spend_producty_product_id'));

        foreach ($info as $key => $value) {

            $item = $observer->getEvent()->getCart()->getQuote()->getItemById($key);
            $productId = $item->getProduct()->getId();

            if (in_array($productId, $spendProductYID)) {
                $item->setQty(1);
            }

        }

        return $this;
    }
}