<?php

use AlpakaWP\AgeVerification\AgeVerificationPlugin;

/**
 *
 * Plugin Name:       Age Verification Screen for WooCommerce
 * Plugin URI:        https://alpakawp.com/plugins/age-verification-screen-for-woocommerce
 * Description:       A simple age verification screen to your shop. Can easily restrict any pages, categories, or products from visitors who are under legal age.
 * Version:           1.1.0
 * Author:            Alpaka WP
 * Author URI:        https://alpakawp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       age-verification-screen-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once 'freemius.php';

call_user_func( function () {

	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

	$main = new AgeVerificationPlugin( __FILE__ );

	register_activation_hook( __FILE__, [ $main, 'activate' ] );

	register_deactivation_hook( __FILE__, [ $main, 'deactivate' ] );

	register_uninstall_hook( __FILE__, [ AgeVerificationPlugin::class, 'uninstall' ] );

	$main->run();
} );