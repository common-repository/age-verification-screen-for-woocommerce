<?php namespace AlpakaWP\AgeVerification\Settings;

use AlpakaWP\AgeVerification\Admin\PostRestrictionManager;
use AlpakaWP\AgeVerification\Admin\TaxonomyRestrictionManager;
use AlpakaWP\AgeVerification\Config\Config;

class RestrictionRulesOption {
	const FIELD_TYPE = 'ac_restriction_type';
	const FIELD_ID = 'restriction_type';

	public function __construct() {
		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );
		add_action( 'woocommerce_admin_settings_sanitize_option_' . Settings::SETTINGS_PREFIX . self::FIELD_ID, array(
			$this,
			'sanitize'
		), 3, 10 );
	}

	public function render( $value ) {

		$visibility_class = array();

		$restrictionTypes = array(
			'full'    => __( 'Entire site', 'age-verification-screen-for-woocommerce' ),
			'partial' => __( 'Partial', 'age-verification-screen-for-woocommerce' )
		);

		$option_value = $this->sanitize( (array) $value['value'] );

		?>
        <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
            <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
            <td class="forminp forminp-checkbox">
				<?php foreach ( $restrictionTypes as $type => $label ): ?>
                    <fieldset>
                        <label for="<?php echo esc_attr( $value['id'] . '[' . $type . ']' ); ?>">
                            <input
                                    name="<?php echo esc_attr( $value['id'] ); ?>[restriction_type]"
                                    id="<?php echo esc_attr( $value['id'] . '[' . $type . ']' ); ?>"
                                    type="radio"
                                    class="age-checker-settings-restriction-type"
                                    value="<?php echo esc_attr( $type ) ?>"
								<?php checked( $option_value['restriction_type'], $type ); ?>
                            /> <span><?php echo esc_html( $label ); ?></span>
                        </label>
                    </fieldset>
				<?php endforeach; ?>

                <div class="age-checker-settings-partial-block"
                     style="<?php echo $option_value !== 'partial' ? 'display:none' : ''; ?>">

                    <h2><?php _e( 'Post types', 'age-verification-screen-for-woocommerce' ); ?></h2>

                    <p class="description">
						<?php _e( 'Pick up pages on which you want to display restriction pop-up. If website visitors confirm age on any of these pages, they\'d be allowed to access any other age - restricted page as well .', 'age-verification-screen-for-woocommerce' ); ?>
                    </p>

                    <div class="age-checker-settings-partial-block__entity-wrapper">
						<?php foreach ( Config::getSupportedPostTypes() as $type ): ?>

                            <fieldset class="age-checker-settings-partial-block-fieldset">
                                <legend class="age-checker-settings-partial-block-fieldset__legend">
									<?php echo $type->label; ?>
                                </legend>

                                <div class="age-checker-settings-partial-block__entity-block-type">
                                    <input type="radio" value="full"
										<?php checked( $option_value['post_type'][ $type->name ], 'full' ); ?>
                                           name="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>]"
                                           id="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>][full]">
                                    <label for="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>][full]">
										<?php echo __( 'Restrict All ', 'age-verification-screen-for-woocommerce' ) . $type->label; ?>
                                    </label>
                                </div>

                                <div class="age-checker-settings-partial-block__entity-block-type">
                                    <input type="radio" value="partial"
										<?php checked( $option_value['post_type'][ $type->name ], 'partial' ); ?>
                                           name="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>]"
                                           id="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>][partial]">
                                    <label for="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>][partial]">
										<?php _e( "Restrict partial", 'age-verification-screen-for-woocommerce' ); ?>
                                    </label>
                                </div>

                                <div class="age-checker-settings-partial-block__blocked_items">
									<?php

									$restrictedCount = intval( $this->getRestrictedPostCount( $type->name ) );

									$link = add_query_arg( [
										'age_restricted' => 'yes'
									], sprintf( admin_url( 'edit.php?post_type=%s' ), $type->name ) );

									?>

                                    <a target="_blank" href="<?php echo esc_attr( $link ); ?>">
										<?php printf( __( 'See restricted items (%d)', 'age-verification-screen-for-woocommerce' ), $restrictedCount ); ?>
                                    </a>

                                </div>

                            </fieldset>
						<?php endforeach; ?>

						<?php if ( ! avfw_fs()->is_premium() ): ?>
							<?php foreach ( $this->getPremiumPostTypes() as $type ): ?>

                                <fieldset
                                        class="age-checker-settings-partial-block-fieldset age-checker-settings-partial-block-fieldset--premium">
                                    <legend class="age-checker-settings-partial-block-fieldset__legend">
										<?php echo $type->label; ?>
                                    </legend>

                                    <div class="age-checker-settings-partial-block__entity-block-type">
                                        <input type="radio" value="full"
                                               name="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>]"
                                               id="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>][full]">
                                        <label for="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>][full]">
											<?php echo __( 'Restrict All ', 'age-verification-screen-for-woocommerce' ) . $type->label; ?>
                                        </label>
                                    </div>

                                    <div class="age-checker-settings-partial-block__entity-block-type">
                                        <input type="radio" value="partial"
                                               name="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>]"
                                               id="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>][partial]">
                                        <label for="<?php echo esc_attr( $value['id'] ); ?>[post_type][<?php echo $type->name; ?>][partial]">
											<?php _e( "Restrict partial", 'age-verification-screen-for-woocommerce' ); ?>
                                        </label>
                                    </div>

                                    <div class="age-checker-settings-partial-block__blocked_items">

                                    </div>
                                    <p style="color: red"><?php _e( 'Available only in the premium version', 'age-verification-screen-for-woocommerce' ); ?></p>
                                </fieldset>
							<?php endforeach; ?>
						<?php endif; ?>
                    </div>

                    <h2><?php _e( 'Taxonomies', 'age-verification-screen-for-woocommerce' ); ?></h2>
                    <p class="description">
						<?php
						_e( 'In case you restrict the whole taxonomy, each page, which belongs to this taxonomy will be restricted. E.g., if you restricted the category, each product page inside this category will be age-restricted, not only the shop/category page. ', 'age-verification-screen-for-woocommerce' );
						?>
                    </p>
                    <div class="age-checker-settings-partial-block__entity-wrapper">
						<?php foreach ( Config::getSupportedTaxonomies() as $type ): ?>

                            <fieldset class="age-checker-settings-partial-block-fieldset">
                                <legend class="age-checker-settings-partial-block-fieldset__legend">
									<?php echo $type->label; ?>
                                </legend>

                                <div class="age-checker-settings-partial-block__entity-block-type">
                                    <input type="radio" value="full"
										<?php checked( $option_value['taxonomy'][ $type->name ], 'full' ); ?>
                                           name="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>]"
                                           id="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>][full]">
                                    <label for="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>][full]">
										<?php echo __( 'Restrict All ', 'age-verification-screen-for-woocommerce' ) . $type->label; ?>
                                    </label>
                                </div>

                                <div class="age-checker-settings-partial-block__entity-block-type">
                                    <input type="radio" value="partial"
										<?php checked( $option_value['taxonomy'][ $type->name ], 'partial' ); ?>
                                           name="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>]"
                                           id="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>][partial]">
                                    <label for="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>][partial]">
										<?php _e( "Restrict partial", 'age-verification-screen-for-woocommerce' ); ?>
                                    </label>
                                </div>

                                <div class="age-checker-settings-partial-block__blocked_items">
									<?php

									$restrictedCount = intval( $this->getRestrictedTermsCount( $type->name ) );

									$link = add_query_arg( [
										'age_restricted' => 'yes'
									], sprintf( admin_url( 'edit-tags.php?taxonomy=%s' ), $type->name ) );

									?>

                                    <a target="_blank" href="<?php echo esc_attr( $link ); ?>">
										<?php printf( __( 'See restricted items (%d)', 'age-verification-screen-for-woocommerce' ), $restrictedCount ); ?>
                                    </a>
                                </div>

                            </fieldset>

						<?php endforeach; ?>
						<?php if ( ! avfw_fs()->is_premium() ): ?>
							<?php foreach ( $this->getPremiumTaxonomies() as $type ): ?>

                                <fieldset
                                        class="age-checker-settings-partial-block-fieldset age-checker-settings-partial-block-fieldset--premium">
                                    <legend class="age-checker-settings-partial-block-fieldset__legend">
										<?php echo $type->label; ?>
                                    </legend>

                                    <div class="age-checker-settings-partial-block__entity-block-type">
                                        <input type="radio" value="full"
                                               name="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>]"
                                               id="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>][full]">
                                        <label for="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>][full]">
											<?php echo __( 'Restrict All ', 'age-verification-screen-for-woocommerce' ) . $type->label; ?>
                                        </label>
                                    </div>

                                    <div class="age-checker-settings-partial-block__entity-block-type">
                                        <input type="radio" value="partial"
                                               name="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>]"
                                               id="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>][partial]">
                                        <label for="<?php echo esc_attr( $value['id'] ); ?>[taxonomy][<?php echo $type->name; ?>][partial]">
											<?php _e( "Restrict partial", 'age-verification-screen-for-woocommerce' ); ?>
                                        </label>
                                    </div>

                                    <div class="age-checker-settings-partial-block__blocked_items">

                                    </div>

                                    <p style="color: red"><?php _e( 'Available only in the premium version', 'age-verification-screen-for-woocommerce' ); ?></p>
                                </fieldset>

							<?php endforeach; ?>
						<?php endif; ?>
                    </div>
                </div>

            </td>
        </tr>
		<?php
	}

	public function getWooCommerceArrayFormat() {
		return array(
			'title'   => __( 'Restricting type', 'age-verification-screen-for-woocommerce' ),
			'id'      => Settings::SETTINGS_PREFIX . self::FIELD_ID,
			'type'    => self::FIELD_TYPE,
			'default' => $this->getDefaults(),
		);
	}

	public function getDefaults() {
		$value['restriction_type'] = 'full';

		foreach ( Config::getSupportedPostTypes() as $type ) {
			$value['post_type'][ $type->name ] = 'full';
		}

		foreach ( Config::getSupportedTaxonomies() as $taxonomy ) {
			$value['taxonomy'][ $taxonomy->name ] = 'full';
		}

		return $value;
	}

	public function sanitize( $value ) {

		$value['restriction_type'] = in_array( $value['restriction_type'], [
			'full',
			'partial'
		] ) ? $value['restriction_type'] : 'full';


		foreach ( Config::getSupportedPostTypes() as $type ) {
			$name                        = $type->name;
			$value['post_type'][ $name ] = ! empty( $value['post_type'][ $name ] ) && in_array( $value['post_type'][ $name ], [
				'partial',
				'full'
			] ) ? $value['post_type'][ $name ] : 'none';
		}

		foreach ( Config::getSupportedTaxonomies() as $taxonomy ) {
			$name                       = $taxonomy->name;
			$value['taxonomy'][ $name ] = ! empty( $value['taxonomy'][ $name ] ) && in_array( $value['taxonomy'][ $name ], [
				'partial',
				'full'
			] ) ? $value['taxonomy'][ $name ] : 'none';
		}

		return $value;
	}

	public function getPremiumPostTypes() {
		$postTypes          = get_post_types( [ 'public' => true ], 'objects' );
		$supportedPostTypes = Config::getSupportedPostTypes();

		$postTypes = array_filter( $postTypes, function ( $name ) use ( $supportedPostTypes ) {
			return ! array_key_exists( $name, $supportedPostTypes );
		}, ARRAY_FILTER_USE_KEY );

		return $postTypes;
	}

	public function getPremiumTaxonomies() {
		$postTypes           = get_taxonomies( [ 'public' => true, 'show_ui' => true ], 'objects' );
		$supportedTaxonomies = Config::getSupportedTaxonomies();

		return array_filter( $postTypes, function ( $name ) use ( $supportedTaxonomies ) {
			return ! array_key_exists( $name, $supportedTaxonomies );
		}, ARRAY_FILTER_USE_KEY );
	}

	public function getRestrictedPostCount( $postType ) {

		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => $postType,
			'post_status'    => 'any',
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => PostRestrictionManager::UNDER_RESTRICTION_META_KEY,
			'meta_value'     => 'yes'
		);

		$posts_query = new \WP_Query( $args );

		return $posts_query->post_count;
	}

	public function getRestrictedTermsCount( $taxonomy ) {

		$args = array(
			'taxonomy'   => $taxonomy,
			'meta_key'   => TaxonomyRestrictionManager::UNDER_RESTRICTION_META_KEY,
			'meta_value' => 'yes',
			'fields'     => 'count'
		);

		return intval( get_terms( $args ) );
	}
}