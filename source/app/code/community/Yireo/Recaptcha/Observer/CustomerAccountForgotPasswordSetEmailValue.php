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
class Yireo_Recaptcha_Observer_CustomerAccountForgotPasswordSetEmailValue
{
    /**
     * @var Mage_Core_Model_Session
     */
    protected $coreSession;

    /**
     * Yireo_Recaptcha_Model_Observer constructor.
     */
    public function __construct()
    {
        $this->coreSession = Mage::getSingleton('core/session');
    }

    /**
     * Listen to the event core_block_abstract_to_html_before
     *
     * @event core_block_abstract_to_html_before
     * @parameter Varien_Event_Observer $observer
     * @return $this
     */
    public function execute(Varien_Event_Observer $observer)
    {
        // Get the parameters from the event
        $block = $observer->getEvent()->getBlock();
        if (empty($block) || !is_object($block)) {
            return $this;
        }

        // Re-insert the email-value for the forgotpassword-block
        $blockClass = 'Mage_Customer_Block_Account_Forgotpassword';
        if ($block instanceof $blockClass) {
            $block->setEmailValue($this->coreSession->getEmailValue());
        }

        return $this;
    }
}
