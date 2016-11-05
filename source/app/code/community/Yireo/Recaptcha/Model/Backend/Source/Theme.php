<?php
/**
 * Google reCaptcha for Magento 
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2016 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_Recaptcha_Model_Backend_Source_Theme
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label'=> Mage::helper('recaptcha')->__('None')),
            array('value' => 'dark', 'label'=> Mage::helper('recaptcha')->__('Dark')),
            array('value' => 'light', 'label'=> Mage::helper('recaptcha')->__('Light')),
            array('value' => 'custom', 'label'=> Mage::helper('recaptcha')->__('Custom')),
        );
    }

}
