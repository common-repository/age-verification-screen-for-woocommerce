jQuery(document).ready(function ($) {

    if (ageCheckerCustomizerSettings !== undefined && typeof ageCheckerCustomizerSettings === 'object') {

        (function (api) {
            // todo: refactor
            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'background_blur',
                function (value) {
                    value.bind(
                        function (to) {
                            document.documentElement.style.setProperty('--age-checker-background-blur', to + 'px');
                        }
                    );
                }
            );

            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'modal_background_color',
                function (value) {
                    value.bind(
                        function (to) {
                            document.documentElement.style.setProperty('--age-checker-modal-background-color', to);
                        }
                    );
                }
            );

            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'background_color',
                function (value) {
                    value.bind(
                        function (to) {
                            document.documentElement.style.setProperty('--age-checker-background-color', to);
                        }
                    );
                }
            );
            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'background_image',
                function (value) {
                    value.bind(
                        function (to) {
                            document.documentElement.style.setProperty('--age-checker-background-image', 'url(' + to + ')');
                        }
                    );
                }
            );

            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'text_color',
                function (value) {
                    value.bind(
                        function (to) {
                            document.documentElement.style.setProperty('--age-checker-text-color', to);
                        }
                    );
                }
            );

            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'header',
                function (value) {
                    value.bind(
                        function (to) {
                            $('.age-checker__header').text(to);
                        }
                    );
                }
            );

            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'description',
                function (value) {
                    value.bind(
                        function (to) {
                            $('.age-checker__description').text(to);
                        }
                    );
                }
            );

            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'checkbox_text',
                function (value) {
                    value.bind(
                        function (to) {
                            $('.age-checker__checkbox-text').text(to);
                        }
                    );
                }
            );
            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'confirm_button_text',
                function (value) {
                    value.bind(
                        function (to) {
                            $('.age-checker__confirm-button').text(to);
                        }
                    );
                }
            );
            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'cancel_button_text',
                function (value) {
                    value.bind(
                        function (to) {
                            $('.age-checker__cancel-button').text(to);
                        }
                    );
                }
            );

            wp.customize(
                ageCheckerCustomizerSettings.prefix + 'warning',
                function (value) {
                    value.bind(
                        function (to) {
                            $('.age-checker__warning').text(to);
                        }
                    );
                }
            );

        }(wp.customize));
    }
});
