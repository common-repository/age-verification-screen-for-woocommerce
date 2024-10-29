<?php if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * @var string $link
 */
?>

<div id="message" class="updated notice is-dismissible">
    <p>
        <strong>
			<?php printf(
				__( 'Thanks for installing Age Verification Screen for WooCommerce! You can customize it %s', 'age-verification-screen-for-woocommerce' ),
				'<a href="' . $link . '">' . sprintf( __( 'here', 'age-verification-screen-for-woocommerce' ) . '</a>' ) );
			?>
        </strong>
    </p>
    <button type="button" class="notice-dismiss"></button>
</div>