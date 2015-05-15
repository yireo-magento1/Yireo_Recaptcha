function recaptchaDisableAllButtons(formElement)
{
    var form = $(formElement).up('form');
    var buttons = form.select('button.button').each(function(button) {
        button.disabled = true;
        button.addClassName('disabled');
    });
}

function recaptchaEnableAllButtons(formElement)
{
    var form = $(formElement).up('form');
    var buttons = form.select('button.button').each(function(button) {
        button.disabled = false;
        button.removeClassName('disabled');
    });
}