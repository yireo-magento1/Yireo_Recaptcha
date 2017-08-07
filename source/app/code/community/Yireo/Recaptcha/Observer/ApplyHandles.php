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
class Yireo_Recaptcha_Observer_ApplyHandles
{
    /**
     * @var Yireo_Recaptcha_Helper_Data
     */
    protected $moduleHelper;

    /**
     * Yireo_Recaptcha_Model_Observer constructor.
     */
    public function __construct()
    {
        $this->moduleHelper = Mage::helper('recaptcha');
    }

    /**
     * @parameter Varien_Event_Observer $observer
     *
     * @return $this
     * @event controller_action_layout_load_before
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $overwrites = $this->moduleHelper->getOverwrites();

        /** @var Mage_Core_Model_Layout_Update $layoutUpdate */
        $layoutUpdate = $observer->getEvent()->getLayout()->getUpdate();

        foreach ($overwrites as $layoutUpdateHandle => $postUrl) {
            if ($this->moduleHelper->getStoreConfig('overwrite_' . $layoutUpdateHandle)) {
                $layoutUpdate->addHandle('recaptcha_' . $layoutUpdateHandle);
            }
        }
    }
}
