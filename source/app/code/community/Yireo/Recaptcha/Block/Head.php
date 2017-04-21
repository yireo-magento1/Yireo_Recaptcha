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
class Yireo_Recaptcha_Block_Head extends Yireo_Recaptcha_Block_Abstract
{
    /**
     * @return void
     */
    public function _construct()
    {
        $rt = parent::_construct();

        // If CAPTCHA is not enabled, return nothing
        if ($this->moduleHelper->useCaptcha() == false) {
            return;
        }

        $this->setTemplate('recaptcha/head.phtml');
    }
}
