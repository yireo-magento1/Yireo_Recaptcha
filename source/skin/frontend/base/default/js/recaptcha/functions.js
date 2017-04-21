/**
 * Google reCAPTCHA extension for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL)
 */

function recaptchaDisableAllButtons(formElement) {
    var form = $(formElement).up('form');
    if (form == undefined) {
        return false;
    }

    if (form.id == 'login-form' && loginForm !== undefined) {
        buttons = $('checkout-step-login').select('button');
        if (buttons) {
            buttons.forEach(function (button) {
                recaptchaDisableButton(button);
            });
        }

        var registerButton = $('onepage-guest-register-button');
        if (registerButton) {
            recaptchaDisableButton(registerButton, true);
        }
    }

    var buttons = form.select('button.button').each(function (button) {
        recaptchaDisableButton(button);
    });

    return true;
}

function recaptchaDisableButton(button, allButtons) {
    if ((allButtons == false || allButtons == undefined) && button.type !== 'submit') {
        return false;
    }

    button.disabled = true;
    button.addClassName('disabled');
    return true;
}

function recaptchaEnableAllButtons(formElement) {
    var form = $(formElement).up('form');
    if (form == undefined) {
        return false;
    }

    if (form.id == 'login-form' && loginForm !== undefined) {
        buttons = $('checkout-step-login').select('button');
        if (buttons) {
            buttons.forEach(function (button) {
                recaptchaEnableButton(button);
            });
        }

        var registerButton = $('onepage-guest-register-button');
        if (registerButton) {
            recaptchaEnableButton(registerButton, true);
        }
    }

    var buttons = form.select('button.button').each(function (button) {
        recaptchaEnableButton(button);
    });
}

function recaptchaEnableButton(button, allButtons) {
    if ((allButtons == false || allButtons == undefined) && button.type !== 'submit') {
        return false;
    }

    button.disabled = false;
    button.removeClassName('disabled');
    return true;
}