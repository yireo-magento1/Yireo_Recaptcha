<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * Event observer
 */
class Yireo_Recaptcha_Model_Observer
{
    /**
     * @event controller_action_layout_load_before
     * @parameter Varien_Event_Observer $observer
     * @return $this
     * @deprecated Flush the cache to use the new observers
     */
    public function applyHandles(Varien_Event_Observer $observer)
    {
        return $this;
    }

    /**
     * @event core_block_abstract_to_html_before
     * @parameter Varien_Event_Observer $observer
     * @return $this
     * @deprecated Flush the cache to use the new observers
     */
    public function customerAccountForgotpasswordSetEmailValue(Varien_Event_Observer $observer)
    {
        return $this;
    }

    /**
     * @event controller_action_predispatch
     * @param Varien_Event_Observer $observer
     * @return $this
     * @deprecated Flush the cache to use the new observers
     */
    public function checkRecaptchaResponse(Varien_Event_Observer $observer)
    {
        return $this;
    }
}
