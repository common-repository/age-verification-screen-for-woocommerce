<?php namespace AlpakaWP\AgeVerification\Settings;

use AlpakaWP\AgeVerification\Customizer\Customizer;

class CustomizeButtonOption {

	const FIELD_TYPE = 'ac_customize_button';
	const FIELD_ID = 'customize_button';

	public function __construct() {
		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );
	}

	public function render() {

		?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <a style="padding: 5px 15px"
                   href="<?php echo esc_attr( admin_url( 'customize.php?autofocus[section]=' . Customizer::SECTION ) ) ?>"
                   target="_blank" class="button-primary button"><?php esc_html_e( 'Customize view', 'age-verification-screen-for-woocommerce' ); ?>
                    <span style="vertical-align: text-bottom; margin-left: 10px"
                            class="dashicons dashicons-admin-customizer"></span>
                </a>
            </th>
            <td class="forminp forminp-checkbox">

            </td>
        </tr>
		<?php
	}

	public function getWooCommerceArrayFormat() {
		return array(
			'id'   => Settings::SETTINGS_PREFIX . self::FIELD_ID,
			'type' => self::FIELD_TYPE,
		);
	}

}