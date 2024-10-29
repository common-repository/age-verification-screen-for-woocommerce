<?php namespace AlpakaWP\AgeVerification\Admin;

use AlpakaWP\AgeVerification\Core\ServiceContainerTrait;
use WP_Term;

class TaxonomyRestrictionManager {

	use ServiceContainerTrait;

	const UNDER_RESTRICTION_META_KEY = '_age_checker_taxonomy_under_restriction';
	const UNDER_RESTRICTION_EXCEPT_META_KEY = '_age_checker_taxonomy_except_under_restriction';
	const UNDER_RESTRICTION_FILTER_KEY = 'age_restricted';

	public function __construct() {

		add_action( 'edit_term', [ $this, 'saveTermFields' ], 10, 3 );

		foreach ( $this->getSupportedTaxonomies() as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", [ $this, 'renderAdd' ] );
			add_action( "{$taxonomy}_edit_form_fields", [ $this, 'renderEdit' ], 99 );

			add_action( "create_{$taxonomy}", [ $this, 'saveTermFields' ], 10, 1 );
		}

		add_filter( 'get_terms_args', function ( $args ) {
			if ( isset( $_GET[ self::UNDER_RESTRICTION_FILTER_KEY ] ) ) {

				$args['meta_query'] = array(
					array(
						'key'     => self::UNDER_RESTRICTION_META_KEY,
						'value'   => 'yes',
						'compare' => '='
					)
				);

			}

			return $args;
		} );

	}

	/**
	 * Save metadata to custom attributes terms
	 *
	 * @param int $term_id
	 * @param $tt_id
	 * @param $taxomomy
	 */
	public function saveTermFields( $term_id, $tt_id, $taxomomy ) {

		if ( in_array( $taxomomy, $this->getSupportedTaxonomies() ) ) {
			update_term_meta( $term_id, self::UNDER_RESTRICTION_META_KEY, isset( $_POST[ self::UNDER_RESTRICTION_META_KEY ] ) ? 'yes' : 'no' );
		}
	}

	public function renderEdit( WP_Term $term ) {

		?>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="attribute_label"><?php _e( 'Age restriction', 'age-verification-screen-for-woocommerce' ); ?></label>
            </th>
            <td>
                <input type="checkbox"
                       name="<?php echo esc_attr( self::UNDER_RESTRICTION_META_KEY ) ?>"
                       id="<?php echo esc_attr( self::UNDER_RESTRICTION_META_KEY ) ?>"
					<?php checked( self::isUnderRestriction( $term->term_id ), true ) ?>>
                <span> <?php _e( 'The taxonomy is under age restriction', 'age-verification-screen-for-woocommerce' ); ?></span>

                <p class="description">
	                <?php _e( 'Blocking taxonomy will block all its posts', 'age-verification-screen-for-woocommerce' ); ?>
                </p>
            </td>
        </tr>

		<?php
	}

	/**
	 * Render plugin's field at adding new taxonomy form.
	 *
	 * @param string $taxonomy
	 */
	public function renderAdd( $taxonomy ) {

		?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="<?php echo esc_attr( self::UNDER_RESTRICTION_META_KEY ) ?>">Age restriction</label>
            </th>
            <td>
                <input type="checkbox"
                       name="<?php echo esc_attr( self::UNDER_RESTRICTION_META_KEY ) ?>"
                       id="<?php echo esc_attr( self::UNDER_RESTRICTION_META_KEY ) ?>">
                <span> <?php _e( 'The taxonomy is under age restriction', 'age-verification-screen-for-woocommerce' ); ?></span>

                <p class="description">
	                <?php _e( 'Blocking taxonomy will block all its posts', 'age-verification-screen-for-woocommerce' ); ?>
                </p>
                <br>
            </td>
        </tr>
		<?php
	}

	public static function isUnderRestriction( $termId ) {
		// todo hook
		return get_term_meta( $termId, self::UNDER_RESTRICTION_META_KEY, true ) === 'yes';
	}

	public function getSupportedTaxonomies() {
		// todo hook
		return array(
			'product_cat',
			'category',
			'post_tag'
		);
	}
}