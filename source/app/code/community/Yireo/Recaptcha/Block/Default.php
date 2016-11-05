<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2016 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * General helper
 */
class Yireo_Recaptcha_Block_Default extends Yireo_Recaptcha_Block_Abstract
{
    /**
     * Method to return an unique block ID
     *
     * @return integer
     */
    public function getUniqueId()
    {
        static $counter = 0;
        $counter++;

        return $counter;
    }

    /**
     * Overriden method _toHtml() to add CAPTCHA when needed
     *
     * @return string
     */
    public function _toHtml()
    {
        // If CAPTCHA is not enabled, return nothing
        if ($this->moduleHelper->useCaptcha() == false) {
            return null;
        }

        return $this->addHtml();
    }

    /**
     * Add the current reCaptcha code
     *
     * @return string
     */
    public function addHtml()
    {
        // Load variables
        $theme = $this->getTheme();

        // Output the custom template
        if ($theme == 'custom') {
            $this->setTemplate('recaptcha/custom.phtml');
            return parent::_toHtml();
        }

        // Helper-method to include the CAPTCHA-library
        $this->moduleHelper->includeRecaptcha();

        $this->setTemplate('recaptcha/default.phtml');
        return parent::_toHtml();
    }
}
