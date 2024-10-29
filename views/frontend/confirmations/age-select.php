<?php defined( "ABSPATH" ) || die;

use AlpakaWP\AgeVerification\Customizer\Customizer;

/**
 * @var Customizer $customizer
 */
?>
<form class="age-checker__confirm-form age-checker__confirm-form--age-select" data-age-checker-confirm-age-select id="age-checker-confirm-age-select">
    <select name="year" required>
        <option value=""><?php esc_html_e( 'Select year', 'age-verification-screen-for-woocommerce' ); ?></option>
		<?php
		foreach ( range( date( 'Y' ) - 5, 1940 ) as $x ) {
			print '<option value="' . $x . '"' . '>' . $x . '</option>';
		}
		?>
    </select>
    <select name="month" required>
        <option value=""><?php esc_html_e( 'Select month', 'age-verification-screen-for-woocommerce' ); ?></option>
		<?php

        // todo: use other method due to translates

		for ( $i = 0; $i < 12; $i ++ ) {
			$monthStr = date( 'M', strtotime( "+ $i months", strtotime( '01.01.2020' ) ) );
			echo "<option value='$i'>" . $monthStr . "</option>";
		} ?>
    </select>
    <select name="day" required>
        <option value=""><?php esc_html_e( 'Select day', 'todo' ); ?></option>
		<?php
		foreach ( range( 1, 31 ) as $x ) {
			print '<option value="' . $x . '"' . '>' . $x . '</option>';
		}
		?>
    </select>
</form>

<button class="button age-checker__confirm-button button-primary" data-age-checker-confirm-button type="submit" form="age-checker-confirm-age-select">
	<?php echo esc_html( $customizer->getConfirmButtonText() ); ?>
</button>


<p class="age-checker__warning" data-age-checker-warning>
	<?php echo esc_html( $customizer->getWarning() ); ?>
</p>