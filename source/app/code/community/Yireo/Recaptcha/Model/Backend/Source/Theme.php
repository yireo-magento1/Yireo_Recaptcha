<?php
/**
 * Google reCaptcha for Magento 
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright (C) 2014 Yireo (http://www.yireo.com/)
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
            array('value' => 'red', 'label'=> Mage::helper('recaptcha')->__('Red')),
            array('value' => 'white', 'label'=> Mage::helper('recaptcha')->__('White')),
            array('value' => 'blackglass', 'label'=> Mage::helper('recaptcha')->__('Blackglass')),
            array('value' => 'clean', 'label'=> Mage::helper('recaptcha')->__('Clean')),
            array('value' => 'custom', 'label'=> Mage::helper('recaptcha')->__('Custom')),
        );
    }

}
