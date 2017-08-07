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
class Yireo_Recaptcha_Block_Script extends Yireo_Recaptcha_Block_Generic
{
    /**
     * @var string
     */
    protected $_template = 'recaptcha/script.phtml';

    /**
     * @return string
     */
    public function _toHtml()
    {
        // If CAPTCHA is not enabled, return nothing
        if ($this->moduleHelper->useCaptcha() === false) {
            return '';
        }

        return parent::_toHtml();
    }
}
