<?php
/**
 * Google Recaptcha for Magento 
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
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
     * @parameter null
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
     * @parameter null
     * @return string
     */
    public function _toHtml()
    {
        // If CAPTCHA is not enabled, return nothing
        if($this->getModuleHelper()->useCaptcha() == false) {
            return null;
        }

        return $this->addHtml();
    }

    /**
     * Add the current reCaptcha code
     *
     * @parameter null
     * @return string
     */
    public function addHtml()
    {
        // Load variables
        $theme = $this->getTheme();

        // Output the custom template
        if($theme == 'custom') {
            $this->setTemplate('recaptcha/custom.phtml');
            return parent::_toHtml();
        }

        // Helper-method to include the CAPTCHA-library
        $this->getModuleHelper()->includeRecaptcha();

        $this->setTemplate('recaptcha/default.phtml');
        return parent::_toHtml();
    }
}
