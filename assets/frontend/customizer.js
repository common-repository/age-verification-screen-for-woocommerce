jQuery(document).ready(function () {
    if (ageCheckerCustomizerSettings !== undefined && typeof ageCheckerCustomizerSettings === 'object') {
        (function (api) {

            jQuery(document).on('tinymce-editor-init', function (event, editor) {
                editor.on('change', function () {
                    tinyMCE.triggerSave();
                    jQuery("#".concat(editor.id)).trigger('change');
                });
            });

            api.section(ageCheckerCustomizerSettings.section, function (section) {
                var previousUrl, clearPreviousUrl, previewUrlValue;
                previewUrlValue = api.previewer.previewUrl;

                clearPreviousUrl = function () {
                    previousUrl = null;
                };

                section.expanded.bind(function (isExpanded) {
                    var url;

                    if (isExpanded) {
                        url = new URL(api.settings.url.home);

                        url.searchParams.append(ageCheckerCustomizerSettings.previewKey, 'yes');

                        previousUrl = previewUrlValue.get();

                        previewUrlValue.set(url);

                        previewUrlValue.bind(clearPreviousUrl);
                    } else {
                        previewUrlValue.unbind(clearPreviousUrl);
                        if (previousUrl) {
                            previewUrlValue.set(previousUrl);
                        }
                    }
                });
            });
            var createViewMode = function (mode, setting) {
                return function (control) {
                    var setActiveState, isDisplayed;

                    isDisplayed = function () {
                        return mode === setting.get();
                    };

                    setActiveState = function () {
                        control.active.set(isDisplayed());
                    };

                    setting.bind(setActiveState);

                    setActiveState();
                }
            };

            api(ageCheckerCustomizerSettings.prefix + 'view_mode', function (setting) {

                var viewModalControl = createViewMode('modal', setting);
                var viewFullPageControl = createViewMode('full', setting);

                // Modal related options
                wp.customize.control(ageCheckerCustomizerSettings.prefix + 'background_blur', viewModalControl);
                wp.customize.control(ageCheckerCustomizerSettings.prefix + 'modal_background_color', viewModalControl);

                // Full page related options
                wp.customize.control(ageCheckerCustomizerSettings.prefix + 'background_color', viewFullPageControl);
                wp.customize.control(ageCheckerCustomizerSettings.prefix + 'background_image', viewFullPageControl);
            });

            api(ageCheckerCustomizerSettings.prefix + 'confirmation_type', function (setting) {

                var viewCheckboxControl = createViewMode('checkbox', setting);
                var viewAgeSelectionControl = createViewMode('age_selection', setting);
                var viewButtonsControl = createViewMode('buttons', setting);

                // checkbox related options
                wp.customize.control(ageCheckerCustomizerSettings.prefix + 'checkbox_text', viewCheckboxControl);

                // Age selection related options
                wp.customize.control(ageCheckerCustomizerSettings.prefix + 'fail_message', viewAgeSelectionControl);

                // Buttons related options
                wp.customize.control(ageCheckerCustomizerSettings.prefix + 'cancel_button_text', viewButtonsControl);
            });

        }(wp.customize));
    }
});