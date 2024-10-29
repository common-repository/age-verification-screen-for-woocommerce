<?php defined( "ABSPATH" ) || die;

use AlpakaWP\AgeVerification\Customizer\Customizer;

/**
 * @var Customizer $customizer
 */
?>

<button class="button age-checker__confirm-button button-primary" data-age-checker-confirm-button>
	<?php echo esc_html( $customizer->getConfirmButtonText() ); ?>
</button>

<button class="button age-checker__cancel-button" data-age-checker-cancel-button>
	<?php echo esc_html( $customizer->getCancelButtonText() ); ?>
</button>