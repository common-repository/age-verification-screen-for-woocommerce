<?php defined( "ABSPATH" ) || die;

use AlpakaWP\AgeVerification\Customizer\Customizer;

/**
 * @var Customizer $customizer
 */
?>
<div class="age-checker__confirm-form age-checker__confirm-form--checkbox">
	<input type="checkbox" name="age_confirmation" data-age-checker-confirm-checkbox
	       id="age-checker-checkbox-confirmation">
	<label for="age-checker-checkbox-confirmation" class="age-checker__checkbox-text">
		<?php echo esc_html( $customizer->getCheckboxText() ); ?>
	</label>
</div>

<button class="button age-checker__confirm-button button-primary" data-age-checker-confirm-button>
	<?php echo esc_html( $customizer->getConfirmButtonText() ); ?>
</button>
