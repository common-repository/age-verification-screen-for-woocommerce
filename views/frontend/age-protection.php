<?php defined( "ABSPATH" ) || die;

use AlpakaWP\AgeVerification\Config\Config;
use AlpakaWP\AgeVerification\Core\FileManager;
use AlpakaWP\AgeVerification\Customizer\Customizer;

/**
 * @var Customizer $customizer
 * @var FileManager $fileManager
 */

?>

<style>
    .age-checker__restriction-wrapper {
        position: fixed;
        color: var(--age-checker-text-color);
        justify-content: center;
        align-items: center;
        display: flex;
        width: 100%;
        height: 100%;
        z-index: 99999;
        overflow: hidden;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        text-align: center;
    }

    .age-checker__restriction-wrapper, .age-checker__content, .age-checker__content p {
        color: var(--age-checker-text-color);
    }

    .age-checker__restriction-wrapper--modal {
        backdrop-filter: blur(var(--age-checker-background-blur));
    }

    .age-checker__restriction-wrapper--full {
        background: var(--age-checker-background-color);
        background-image: var(--age-checker-background-image);
    }

    .age-checker__restriction-block {
        padding: 2em;
        width: 600px;
    }

    .age-checker__restriction-block--modal {
        background: var(--age-checker-modal-background-color);
    }

    .age-checker__restriction-block {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .age-checker__header {
        line-height: 1;
        font-size: 2em;
        font-weight: bold;
    }

    .age-checker__description {
        margin: 25px 0;
    }

    .age-checker__confirm-form {
        margin: 25px 0;
    }

    .age-checker__inner {
        width: 100%;
    }

    .age-checker__confirm-form--age-select {
        display: flex;
    }

    .age-checker__confirm-form--age-select select {
        width: 100%;
        padding: 10px;
        margin-left: 5px;
    }

    .age-checker__confirm-form--age-select:first-child {
        margin-left: 0;
    }

    .age-checker__warning {
        margin: 25px 0 0 0;
        font-weight: bold;
        display: none;
    }
</style>
<div data-age-checker-restriction-wrapper
     data-cookie-key="<?php echo esc_attr( Config::getCookieHashKey() ); ?>"
     data-cookie-duration-days="<?php echo esc_attr( Config::getCookieDuration() ); ?>"
     data-confirm-type="<?php echo esc_attr( $customizer->getConfirmationType() ); ?>"
     data-minimum-age="<?php echo esc_attr( Config::getMinimumAllowedAge() ); ?>"
     data-is-customizer="<?php echo esc_attr( is_customize_preview() ? 'yes' : 'no' ); ?>"
     data-cookie-confirmed-value="<?php echo esc_attr( Config::getCookieConfirmedValue() ); ?>"
     data-redirect-url="<?php echo Config::getProtectionType() === 'previous_page' ? esc_attr( 'previous_page' ) : esc_attr( Config::getFailureRedirectURL() ); ?>"

     class="age-checker__restriction-wrapper age-checker__restriction-wrapper--<?php echo esc_attr( $customizer->getViewMode() ) ?>">

    <div class="age-checker__restriction-block age-checker__restriction-block--<?php echo esc_attr( $customizer->getViewMode() ) ?>">
        <div class="age-checker__inner">

            <div class="age-checker__header"><?php echo esc_html( $customizer->getHeader() ); ?></div>

            <div class="age-checker__description">
				<?php echo esc_html( $customizer->getDescription() ); ?>
            </div>
			<?php if ( $customizer->getConfirmationType() === 'checkbox' ): ?>
				<?php
				$fileManager->includeTemplate( 'frontend/confirmations/checkbox.php', [
					'customizer' => $customizer,
				] );
				?>
			<?php elseif ( $customizer->getConfirmationType() === 'age_select' ): ?>
				<?php
				$fileManager->includeTemplate( 'frontend/confirmations/age-select.php', [
					'customizer' => $customizer,
				] );
				?>
			<?php else: ?>
				<?php
				$fileManager->includeTemplate( 'frontend/confirmations/buttons.php', [
					'customizer' => $customizer,
				] );
				?>
			<?php endif; ?>
        </div>
    </div>
</div>
<script>
    (function ($) {
        var AgeProtection = function () {
            this.init = function () {

                this.wrapper = jQuery('[data-age-checker-restriction-wrapper]');
                this.bodyOverflowPrevValue = jQuery('body').css('overflow');

                jQuery('body').css('overflow', 'hidden');

                if (this.wrapper.data('confirm-type') === 'checkbox') {

                    jQuery('[data-age-checker-confirm-button]').on('click', (function () {
                        if (jQuery('[ data-age-checker-confirm-checkbox]').is(':checked')) {
                            this.confirm();
                        } else {
                            this.denyAccess();
                        }
                    }).bind(this));

                } else if (this.wrapper.data('confirm-type') === 'buttons') {

                    jQuery('[data-age-checker-confirm-button]').on('click', this.confirm.bind(this));
                    jQuery('[data-age-checker-cancel-button]').on('click', this.denyAccess.bind(this));

                } else if (this.wrapper.data('confirm-type') === 'age_select') {

                    jQuery('[data-age-checker-confirm-age-select] select').change((function () {
                        if (this.getSelectedFullYears()) {
                            if (!this.isSelectedAgeIsOK()) {
                                this.showAgeNotice();
                            } else {
                                this.hideAgeNotice();
                            }
                        } else {
                            this.hideAgeNotice();
                        }
                    }).bind(this));

                    jQuery('[data-age-checker-confirm-age-select]').submit((function (e) {
                        e.preventDefault();

                        this.isSelectedAgeIsOK() ? this.confirm() : this.denyAccess();
                    }).bind(this));
                }
            }

            this.confirm = function () {
                if (this.isCustomizer()) {
                    alert('Access provided.');
                } else {
                    this.setCookie();
                    this.closeProtection();
                }
            }

            this.denyAccess = function () {
                if (this.isCustomizer()) {
                    alert('Access denied.');
                } else {
                    var redirect = this.wrapper.data('redirect-url');

                    if (redirect === 'previous_page' || redirect === '' || redirect === undefined) {
                        window.history.back();
                    } else {
                        document.location.href = redirect;
                    }
                }
            }

            this.showAgeNotice = function () {
                jQuery('[data-age-checker-warning]').show();
            }

            this.hideAgeNotice = function () {
                jQuery('[data-age-checker-warning]').hide();
            }

            this.setCookie = function () {

                var name = this.wrapper.data('cookie-key');
                var days = parseInt(this.wrapper.data('cookie-duration-days'));
                var value = this.wrapper.data('cookie-confirmed-value');

                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }

            this.getSelectedFullYears = function () {
                var wrapper = jQuery('[data-age-checker-confirm-age-select]');
                var selectedYear = parseInt(wrapper.find('[name=year]').val());
                var selectedMonth = wrapper.find('[name=month]').val();
                var selectedDay = parseInt(wrapper.find('[name=day]').val());

                if (selectedDay && selectedMonth !== '' && selectedYear) {
                    var selectedDate = new Date(selectedYear, selectedMonth, selectedDay);
                    var now = new Date();

                    if (selectedDate) {
                        var yearDiff = now.getFullYear() - selectedDate.getFullYear();

                        yearDiff--;

                        if (selectedDate.getMonth() < now.getMonth()) {
                            yearDiff++;
                        } else if (selectedDate.getMonth() === now.getMonth()) {
                            if (selectedDate.getDate() <= now.getDate()) {
                                yearDiff++;
                            }
                        }

                        return yearDiff;
                    }
                }

                return false;
            }

            this.isSelectedAgeIsOK = function () {
                return this.getSelectedFullYears() && this.getSelectedFullYears() >= this.getMinimumAge();
            }

            this.getMinimumAge = function () {
                return parseInt(this.wrapper.data('minimum-age'));
            }

            this.closeProtection = function () {
                this.wrapper.remove();
                jQuery('body').css('overflow', this.bodyOverflowPrevValue);
            }

            this.isCustomizer = function () {
                return this.wrapper.data('is-customizer') === 'yes';
            }
        }

        var ageProtection = new AgeProtection();

        ageProtection.init();

    })(jQuery);
</script>