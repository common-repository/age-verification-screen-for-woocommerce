<?php namespace AlpakaWP\AgeVerification;

use AlpakaWP\AgeVerification\Core\AdminNotifier;
use AlpakaWP\AgeVerification\Core\FileManager;
use AlpakaWP\AgeVerification\Core\ServiceContainerTrait;
use AlpakaWP\AgeVerification\Admin\Admin;
use AlpakaWP\AgeVerification\Customizer\Customizer;
use AlpakaWP\AgeVerification\Frontend\Frontend;
use AlpakaWP\AgeVerification\Settings\Settings;

/**
 * Class AgeVerificationPlugin
 *
 * @package AlpakaWP\AgeVerification
 */
class AgeVerificationPlugin {

	use ServiceContainerTrait;

	const VERSION = '1.1.0';

	/**
	 * @var FileManager
	 */
	private $fileManager;

	/**
	 * AgeVerificationPlugin constructor.
	 *
	 * @param string $mainFile
	 */
	public function __construct( $mainFile ) {

		FileManager::init( $mainFile, 'age-verification-screen-for-woocommerce' );

		add_action( 'plugins_loaded', [ $this, 'loadTextDomain' ] );
		add_action( 'admin_init', [ $this, 'checkRequirePlugins' ] );
	}

	/**
	 * Run plugin part
	 */
	public function run() {

		if ( count( $this->validateRequiredPlugins() ) === 0 ) {
			$this->initContainer();

			if ( is_admin() ) {
				new Admin();
			} else {
				new Frontend();
			}

			add_filter( 'plugin_action_links_' . plugin_basename( $this->getContainer()->getFileManager()->getMainFile() ), function ( $actions ) {
				$actions[] = '<a href="' . $this->getContainer()->getSettings()->getLink() . '">' . __( 'Settings', 'order-messenger-for-woocommerce' ) . '</a>';

				if ( ! avfw_fs()->is_anonymous() && avfw_fs()->is_installed_on_site() ) {
					$actions[] = '<a href="' . avfw_fs()->get_account_url() . '"><b style="color: green">' . __( 'Account',
							'todo' ) . '</b></a>';
				}

				$actions[] = '<a href="' . avfw_fs()->contact_url() . '"><b style="color: green">' . __( 'Contact us',
						'todo' ) . '</b></a>';

				if ( ! avfw_fs()->is_premium() ) {
					$actions[] = '<a href="' . avfw_fs()->get_upgrade_url() . '"><b style="color: red">' . __( 'Go premium',
							'todo' ) . '</b></a>';
				}

				return $actions;
			}, 10, 4 );
		}

	}

	public function validateRequiredPlugins() {

		$plugins = [];

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		/**
		 * Check if WooCommerce is active
		 **/
		if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) ) {
			$plugins[] = '<a target="_blank" href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>';
		}

		return $plugins;
	}

	/**
	 * Check required plugins and push notifications
	 */
	public function checkRequirePlugins() {
		$message = __( 'The %s plugin requires %s plugin to be active!', 'premmerce-url-manager' );

		$plugins = $this->validateRequiredPlugins();

		if ( count( $plugins ) ) {
			foreach ( $plugins as $plugin ) {
				$error = sprintf( $message, 'WooCommerce Permalink Manager', $plugin );
				$this->getContainer()->getAdminNotifier()->push( $error, AdminNotifier::ERROR, false );
			}
		}
	}

	public function initContainer() {

		$this->getContainer()->add( 'fileManager', FileManager::getInstance() );
		$this->getContainer()->add( 'adminNotifier', new AdminNotifier() );
		$this->getContainer()->add( 'settings', new Settings() );
		$this->getContainer()->add( 'customizer', new Customizer() );

		do_action( 'age_checker/container/main_services_init' );
	}

	/**
	 * Load plugin translations
	 */
	public function loadTextDomain() {
		$name = $this->getContainer()->getFileManager()->getPluginName();
		load_plugin_textdomain( 'age-verification-screen-for-woocommerce', false, $name . '/languages/' );
	}

	/**
	 * Fired when the plugin is activated
	 */
	public function activate() {
		set_transient( 'woocommerce_age_verification_activated', true, 100 );

	}

	/**
	 * Fired when the plugin is deactivated
	 */
	public function deactivate() {
		// TODO: Implement deactivate() method.
	}

	/**
	 * Fired during plugin uninstall
	 */
	public static function uninstall() {
		// TODO: Implement uninstall() method.
	}
}