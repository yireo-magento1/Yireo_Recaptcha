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
 * General helper
 */
class Yireo_Recaptcha_Block_Default extends Yireo_Recaptcha_Block_Generic
{
    /**
     * @var string
     */
    protected $_template = 'recaptcha/default.phtml';

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
        if ($this->moduleHelper->useCaptcha() === false) {
            return '';
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
        $theme = $this->getData('theme');

        if ($theme === 'custom') {
            $this->setTemplate('recaptcha/custom.phtml');
            return parent::_toHtml();
        }

        $this->moduleHelper->includeRecaptcha();
        return parent::_toHtml();
    }
}
