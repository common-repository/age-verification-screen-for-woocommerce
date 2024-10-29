<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var string $accountUrl
 * @var string $contactUsUrl
 */
?>
<div class="avfw-alert">

    <div class="avfw-alert__text">
        <div class="avfw-alert__inner">
            <?php
                _e( 'Thanks! You are using premium version of the plugin!', 'age-verification-screen-for-woocommerce' );
            ?>
        </div>
    </div>

    <div class="avfw-alert__buttons">
        <div class="avfw-alert__inner">
            <a class="avfw-button avfw-button--accent" href="<?php echo $accountUrl; ?>"><?php _e( 'My Account',
			        'age-verification-screen-for-woocommerce' ); ?></a>
            <a class="avfw-button avfw-button--default" href="<?php echo $contactUsUrl; ?>"><?php _e( 'Contact us', 'age-verification-screen-for-woocommerce' ); ?></a>
        </div>
    </div>
</div>