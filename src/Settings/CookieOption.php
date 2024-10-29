<?php namespace AlpakaWP\AgeVerification\Settings;

use AlpakaWP\AgeVerification\Core\AdminNotifier;
use AlpakaWP\AgeVerification\Core\ServiceContainer;
use AlpakaWP\AgeVerification\Core\ServiceContainerTrait;

class CookieOption {

	use ServiceContainerTrait;

	const FIELD_TYPE = 'ac_cookie';
	const FIELD_ID = 'cookie';
	const UPDATE_COOKIE_ACTION = 'ac_update_cookie__action';

	public function __construct() {
		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );
		add_action( 'woocommerce_admin_settings_sanitize_option_' . Settings::SETTINGS_PREFIX . self::FIELD_ID, array(
			$this,
			'sanitize'
		), 3, 10 );

		add_action( 'admin_post_' . self::UPDATE_COOKIE_ACTION, array( $this, 'updateCookieHash' ) );
	}

	public function render( $value ) {

		$option_value     = $value['value'];
		$visibility_class = array();


		$option_value['duration']    = isset( $option_value['duration'] ) ? $option_value['duration'] : $this->getDefaults()['duration'];
		$option_value['cookie_hash'] = isset( $option_value['cookie_hash'] ) ? $option_value['cookie_hash'] : $this->getDefaults()['cookie_hash'];

		?>
        <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
            <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
            <td class="forminp forminp-checkbox">

                <fieldset>
                    <label for="<?php echo esc_attr( $value['id'] . '[duration]' ); ?>">

                        <input
                                name="<?php echo esc_attr( $value['id'] ); ?>[duration]"
                                id="<?php echo esc_attr( $value['id'] . '[duration]' ); ?>"
                                type="number"
                                min="0"
                                max="999"
                                class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
                                value="<?php echo esc_attr( $option_value['duration'] ) ?>"
                        />
                    </label>
                    <p class="description"><?php _e( 'Choose for how many days you want to remember users\' choice. E.g., if you set up 30 days, the user would be able to access age-restricted pages all these days without re-confirmation of age.', 'age-verification-screen-for-woocommerce' ) ?></p>
                </fieldset>

                <input type="hidden" name="<?php echo esc_attr( $value['id'] ); ?>[cookie_hash]"
                       value="<?php echo esc_attr( $option_value['cookie_hash'] ); ?>">

                <fieldset>
					<?php

					$updateURL = add_query_arg( [
						'action'   => self::UPDATE_COOKIE_ACTION,
						'_wpnonce' => wp_create_nonce( self::UPDATE_COOKIE_ACTION )
					], admin_url( 'admin-post.php' ) )
					?>
                    <a href="<?php echo esc_attr( $updateURL ); ?>"
                       onclick="if(!confirm('Are you sure?')){ event.preventDefault(); return false}"
                       class="button"><?php esc_html_e( ' Purge users\' cookies', 'age-verification-screen-for-woocommerce' ); ?></a>
                </fieldset>
            </td>
        </tr>
		<?php
	}

	public function getWooCommerceArrayFormat() {
		return array(
			'title'   => __( 'Cookie lifetime (days)', 'age-verification-screen-for-woocommerce' ),
			'id'      => Settings::SETTINGS_PREFIX . self::FIELD_ID,
			'type'    => self::FIELD_TYPE,
			'default' => $this->getDefaults(),
		);
	}

	public function getDefaults() {
		return array(
			'duration'    => 30,
			'cookie_hash' => md5( site_url() )
		);
	}

	public function sanitize( $value ) {
		$value['duration'] = intval( $value['duration'] );

		return $value;
	}

	public function updateCookieHash() {

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : false;

		if ( wp_verify_nonce( $nonce, self::UPDATE_COOKIE_ACTION ) ) {

			$settings              = ServiceContainer::getInstance()->getSettings();
			$cookie                = $settings->get( self::FIELD_ID );
			$cookie['cookie_hash'] = wp_generate_password( 10 );

			update_option( Settings::SETTINGS_PREFIX . self::FIELD_ID, $cookie );

			$this->getContainer()->getAdminNotifier()->flash( __( 'Cookie hash has been successfully updated', 'age-verification-screen-for-woocommerce' ) );

		} else {
			$this->getContainer()->getAdminNotifier()->flash( __( 'Invalid nonce', 'age-verification-screen-for-woocommerce' ), AdminNotifier::ERROR );
		}

		return wp_safe_redirect( wp_get_referer() );
	}
}