<?php
/**
 * Google reCaptcha for Magento 
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_Recaptcha_Model_Backend_Source_Api
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'default', 'label'=> Mage::helper('recaptcha')->__('Default')),
            array('value' => 'ajax', 'label'=> Mage::helper('recaptcha')->__('AJAX')),
        );
    }

}
