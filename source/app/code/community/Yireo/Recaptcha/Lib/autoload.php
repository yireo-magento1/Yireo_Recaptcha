<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

spl_autoload_register(function ($originalClass) {
    if (substr($originalClass, 0, 10) !== 'ReCaptcha\\') {
      return;
    }

    $class = str_replace('\\', '/', $originalClass);

    $path = dirname(__FILE__).'/'.$class.'.php';
    if (is_readable($path)) {
        require_once $path;
    }
});
