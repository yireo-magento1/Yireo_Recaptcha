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
class Yireo_Recaptcha_Block_Head extends Yireo_Recaptcha_Block_Abstract
{
    public function _construct()
    {
        // If CAPTCHA is not enabled, return nothing
        if($this->getModuleHelper()->useCaptcha() == false) {
            return null;
        }

        $this->setTemplate('recaptcha/head.phtml');

        return parent::_construct();
    }
}
