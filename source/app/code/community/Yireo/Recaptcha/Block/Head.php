<?php
/**
 * Google Recaptcha for Magento 
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright (C) 2014 Yireo (http://www.yireo.com/)
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
        if(Mage::helper('recaptcha')->useCaptcha() == false) {
            return null;
        }

        $mode = Mage::helper('recaptcha')->getMode();
        if($mode == 'legacy') {
            $this->setTemplate('recaptcha/legacy/head.phtml');
        } else {
            $this->setTemplate('recaptcha/head.phtml');
        }

        return parent::_construct();
    }
}
