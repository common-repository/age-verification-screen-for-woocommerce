<?php namespace AlpakaWP\AgeVerification\Settings;

class FailureRedirectOption {

	const FIELD_TYPE = 'ac_failure_redirect';
	const FIELD_ID = 'failure_redirect';

	public function __construct() {
		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );
		add_action( 'woocommerce_admin_settings_sanitize_option_' . Settings::SETTINGS_PREFIX . self::FIELD_ID, array(
			$this,
			'sanitize'
		), 3, 10 );
	}

	public function render( $value ) {

		$option_value     = $value['value'];
		$visibility_class = array();

		$option_value['failure_type'] = isset( $option_value['failure_type'] ) ? $option_value['failure_type'] : $this->getDefaults()['failure_type'];
		$option_value['redirect_url'] = isset( $option_value['redirect_url'] ) ? $option_value['redirect_url'] : $this->getDefaults()['redirect_url'];
		$option_value['site_page']    = isset( $option_value['site_page'] ) ? $option_value['site_page'] : $this->getDefaults()['site_page'];

		?>
        <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
            <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
            <td class="forminp forminp-checkbox">
                <fieldset>
                    <label for="<?php echo esc_attr( $value['id'] . '[failure_type][previous_page]' ); ?>">
                        <input
                                name="<?php echo esc_attr( $value['id'] ); ?>[failure_type]"
                                id="<?php echo esc_attr( $value['id'] . '[failure_type][previous_page]' ); ?>"
                                type="radio"
                                class="ac-failure-type"
                                value="<?php echo esc_attr( 'previous_page' ) ?>"
							<?php checked( $option_value['failure_type'], 'previous_page' ); ?>
                        /> <span><?php echo esc_html__( 'Previous page', 'age-verification-screen-for-woocommerce' ); ?></span>
                    </label>
                </fieldset>

                <fieldset>
                    <label for="<?php echo esc_attr( $value['id'] . '[failure_type][site_page]' ); ?>">
                        <input
                                name="<?php echo esc_attr( $value['id'] ); ?>[failure_type]"
                                id="<?php echo esc_attr( $value['id'] . '[failure_type][site_page]' ); ?>"
                                type="radio"
                                class="ac-failure-type"
                                value="<?php echo esc_attr( 'site_page' ) ?>"
							<?php checked( $option_value['failure_type'], 'site_page' ); ?>
                        /> <span><?php echo esc_html__( 'Site page', 'age-verification-screen-for-woocommerce' ); ?></span>
                    </label>

                    <fieldset data-ac-site-page style="margin: 8px 0">
						<?php
						$args = array(
							'name'             => $value['id'] . '[site_page]',
							'id'               => $value['id'] . '[site_page]',
							'sort_column'      => 'menu_order',
							'sort_order'       => 'ASC',
							'show_option_none' => ' ',
							'class'            => 'wc-enhanced-select-nostd',
							'echo'             => false,
							'selected'         => absint( $option_value['site_page'] ),
							'post_status'      => 'publish,private,draft',
						);

						echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'woocommerce' ) . "'  id=", wp_dropdown_pages( $args ) ); ?>

                    </fieldset>
                </fieldset>

                <fieldset>
                    <label for="<?php echo esc_attr( $value['id'] . '[failure_type][custom_url]' ); ?>">
                        <input
                                name="<?php echo esc_attr( $value['id'] ); ?>[failure_type]"
                                id="<?php echo esc_attr( $value['id'] . '[failure_type][custom_url]' ); ?>"
                                type="radio"
                                class="ac-failure-type"
                                value="<?php echo esc_attr( 'custom_url' ) ?>"
							<?php checked( $option_value['failure_type'], 'custom_url' ); ?>
                        /> <span><?php echo esc_html__( 'Custom URL', 'age-verification-screen-for-woocommerce' ); ?></span>
                    </label>

                    <fieldset data-ac-redirect-url style="margin: 8px 0">
                        <input type="text"
							<?php echo avfw_fs()->is_premium() ? '' : 'disabled' ?>
                               name="<?php echo esc_attr( $value['id'] ); ?>[redirect_url]"
                               id="<?php echo esc_attr( $value['id'] ); ?>[redirect_url]"
                               value="<?php echo esc_attr( $option_value['redirect_url'] ); ?>"
                        >
						<?php if ( ! avfw_fs()->is_premium() ): ?>
                            <p style="color: red;"><?php _e( 'Available only in the premium version', 'age-verification-screen-for-woocommerce' ); ?></p>
						<?php endif; ?>
                    </fieldset>
                </fieldset>

                <p class="description">
					<?php _e( 'Those users who turned out to be below the minimum age mark might be redirected to the page where your age policy is explained.', 'age-verification-screen-for-woocommerce' ); ?>
                </p>
            </td>
        </tr>
        <script>

        </script>
		<?php
	}

	public function getFailureTypes() {
		return array(
			'previous_page' => __( 'Previous page', 'age-verification-screen-for-woocommerce' ),
			'site_page'     => __( 'Site page', 'age-verification-screen-for-woocommerce' ),
			'custom_url'    => __( 'Custom URL', 'age-verification-screen-for-woocommerce' ),
		);

	}

	public function getWooCommerceArrayFormat() {
		return array(
			'title'   => __( 'Failure redirect', 'age-verification-screen-for-woocommerce' ),
			'id'      => Settings::SETTINGS_PREFIX . self::FIELD_ID,
			'type'    => self::FIELD_TYPE,
			'default' => $this->getDefaults(),
		);
	}

	public function getDefaults() {
		return array(
			'failure_type' => 'previous_page',
			'redirect_url' => '',
			'site_page'    => ''
		);
	}

	public function sanitize( $value ) {
		$value['failure_type'] = in_array( $value['failure_type'], array_keys( $this->getFailureTypes() ) ) ? $value['failure_type'] : 'previous_page';
		$value['redirect_url'] = isset( $value['redirect_url'] ) ? esc_url_raw( $value['redirect_url'] ) : '';
		$value['site_page']    = isset( $value['site_page'] ) ? intval( $value['site_page'] ) : 0;

		return $value;
	}
}