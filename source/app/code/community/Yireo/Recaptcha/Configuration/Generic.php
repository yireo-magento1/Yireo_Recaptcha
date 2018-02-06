<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_Recaptcha_Configuration_Generic
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $layoutHandle;

    /**
     * @var array
     */
    protected $validationUrls = [];

    /**
     * @var array
     */
    protected $displayUrls = [];

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLayoutHandle()
    {
        return 'recaptcha_'.$this->id;
    }

    /**
     * @return array
     */
    public function getValidationUrls()
    {
        return $this->validationUrls;
    }

    /**
     * @return array
     */
    public function getDisplayUrls()
    {
        return $this->displayUrls;
    }
}