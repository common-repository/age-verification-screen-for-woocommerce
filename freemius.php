<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'avfw_fs' ) ) {
    // Create a helper function for easy SDK access.
    function avfw_fs() {
        global $avfw_fs;
        if ( !isset( $avfw_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_8011_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_8011_MULTISITE', true );
            }
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $avfw_fs = fs_dynamic_init( array(
                'id'             => '8011',
                'slug'           => 'age-verification-screen-for-wooCommerce',
                'premium_slug'   => 'age-verification-for-woocommerce-premium',
                'type'           => 'plugin',
                'public_key'     => 'pk_7c22a874aee30333c6fe87b2e7e6b',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                    'days'               => 10,
                    'is_require_payment' => true,
                ),
                'menu'           => array(
                    'first-path' => 'plugins.php',
                    'contact'    => false,
                    'support'    => false,
                ),
                'is_live'        => true,
            ) );
        }
        return $avfw_fs;
    }

    // Init Freemius.
    avfw_fs();
    // Signal that SDK was initiated.
    do_action( 'avfw_fs_loaded' );
}