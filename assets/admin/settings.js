jQuery(document).ready(function ($) {
    var redirectUrl = jQuery('[data-ac-redirect-url]');
    var sitePage = jQuery('[data-ac-site-page]');

    jQuery('.ac-failure-type').change(function (e) {

        if ($(this).val() === 'custom_url') {
            redirectUrl.show();
        } else {
            redirectUrl.hide();
        }

        if ($(this).val() === 'site_page') {
            sitePage.show();
        } else {
            sitePage.hide();
        }
    }).filter(':checked').trigger('change');


    var partialBlock = jQuery('.age-checker-settings-partial-block');

    jQuery('.age-checker-settings-restriction-type').change(function (e) {

        if ($(this).val() === 'partial') {
            partialBlock.show();
        } else {
            partialBlock.hide();
        }
    }).filter(':checked').trigger('change');

    jQuery('.age-checker-settings-partial-block__entity-block-type input').change(function (e) {
        if ($(this).val() === 'partial') {
            $(this).closest('fieldset').find('.age-checker-settings-partial-block__blocked_items').show();
        } else {
            $(this).closest('fieldset').find('.age-checker-settings-partial-block__blocked_items').hide();
        }
    }).filter(':checked').trigger('change');
});