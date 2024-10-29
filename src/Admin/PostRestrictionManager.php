<?php namespace AlpakaWP\AgeVerification\Admin;

use AlpakaWP\AgeVerification\Config\Config;
use AlpakaWP\AgeVerification\Core\ServiceContainerTrait;

class PostRestrictionManager {

	use ServiceContainerTrait;

	const UNDER_RESTRICTION_META_KEY = '_age_checker_post_under_restriction';
	const UNDER_RESTRICTION_EXCEPT_META_KEY = '_age_checker_post_except_under_restriction';
	const UNDER_RESTRICTION_FILTER_KEY = 'age_restricted';

	public function __construct() {

		if ( Config::getProtectionType() !== 'full' ) {

			add_action( 'save_post', function ( $postID, $post ) {

				if ( in_array( $post->post_type, array_keys( Config::getSupportedPostTypes() ) ) ) {
					update_post_meta( $postID, self::UNDER_RESTRICTION_META_KEY, isset( $_POST[ self::UNDER_RESTRICTION_META_KEY ] ) ? 'yes' : 'no' );
					update_post_meta( $postID, self::UNDER_RESTRICTION_EXCEPT_META_KEY, isset( $_POST[ self::UNDER_RESTRICTION_EXCEPT_META_KEY ] ) ? 'yes' : 'no' );
				}
			}, 10, 2 );

			add_action( 'post_submitbox_misc_actions', function () {
				global $post;

				if ( in_array( $post->post_type, array_keys( Config::getSupportedPostTypes() ) ) ) {
					?>
                    <div class="misc-pub-section">
                        <input type="checkbox" <?php checked( self::isUnderRestriction( $post->ID ), true ) ?>
                               name="<?php echo esc_attr( self::UNDER_RESTRICTION_META_KEY ) ?>"
                               id="<?php echo esc_attr( self::UNDER_RESTRICTION_META_KEY ) ?>">
                        <label for="<?php echo esc_attr( self::UNDER_RESTRICTION_META_KEY ) ?>"
                               style="font-weight: 600;">
							<?php _e( 'The post is under age restriction', 'age-verification-screen-for-woocommerce' ); ?>
                        </label>
                    </div>
					<?php
				}
			}, 999 );

			add_action( 'restrict_manage_posts', function () {

				$type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'post';

				if ( in_array( $type, array_keys( Config::getSupportedPostTypes() ) ) ) {
					?>
                    <select name="<?php echo self::UNDER_RESTRICTION_FILTER_KEY ?>">
                        <option value="-1"><?php _e( 'Filter by age restriction', 'age-verification-screen-for-woocommerce' ); ?></option>
                        <option value="yes" <?php selected( isset( $_GET[ self::UNDER_RESTRICTION_FILTER_KEY ] ) && $_GET[ self::UNDER_RESTRICTION_FILTER_KEY ] === 'yes' ) ?>>
							<?php _e( 'Age restricted', 'age-verification-screen-for-woocommerce' ) ?>
                        </option>
                    </select>
					<?php
				}
			} );

			add_filter( 'parse_query', function ( $query ) {

				global $pagenow;

				$type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'post';

				if ( is_post_type_viewable( $type ) && is_admin() && $pagenow == 'edit.php' && isset( $_GET[ self::UNDER_RESTRICTION_FILTER_KEY ] ) && $_GET[ self::UNDER_RESTRICTION_FILTER_KEY ] === 'yes' ) {
					$query->query_vars['meta_key']   = self::UNDER_RESTRICTION_META_KEY;
					$query->query_vars['meta_value'] = 'yes';
				}
			} );

			add_action( 'init', function () {

				foreach ( Config::getSupportedPostTypes() as $postType ) {

					add_filter( 'manage_' . $postType->name . '_posts_columns', function ( $columns ) {
						$columns['age_restriction'] = __( 'Age restriction', 'age-verification-screen-for-woocommerce' );

						return $columns;
					} );


					add_action( 'manage_' . $postType->name . '_posts_custom_column', function ( $column, $post_id ) {
						switch ( $column ) {
							case 'age_restriction' :
								if ( get_post_meta( $post_id, self::UNDER_RESTRICTION_META_KEY, true ) === 'yes' ) {
									?>
                                    <span><?php _e( 'Restricted', 'age-verification-screen-for-woocommerce' ); ?></span>
									<?php
								}
								break;
						}
					}, 10, 2 );


					add_filter( 'bulk_actions-edit-' . $postType->name, function ( $bulkActions ) {
						$bulkActions['restrict_age__action']   = __( 'Restrict Age', 'age-verification-screen-for-woocommerce' );
						$bulkActions['unrestrict_age__action'] = __( 'Remove Age Restriction', 'age-verification-screen-for-woocommerce' );

						return $bulkActions;
					} );

					add_filter( 'handle_bulk_actions-edit-' . $postType->name, function ( $redirect, $doaction, $object_ids ) {

						$redirect = remove_query_arg( array(
							'restrict_age__action',
							'unrestrict_age__action'
						), $redirect );


						if ( $doaction == 'restrict_age__action' ) {

							foreach ( $object_ids as $post_id ) {
								update_post_meta( $post_id, self::UNDER_RESTRICTION_META_KEY, 'yes' );
							}

							$this->getContainer()->getAdminNotifier()->flash( __( 'Restriction items have been changed successfully.', 'age-verification-screen-for-woocommerce' ) );

						}

						if ( $doaction == 'unrestrict_age__action' ) {

							foreach ( $object_ids as $post_id ) {
								delete_post_meta( $post_id, self::UNDER_RESTRICTION_META_KEY );
							}

							$this->getContainer()->getAdminNotifier()->flash( __( 'Restriction items have been changed successfully.', 'age-verification-screen-for-woocommerce' ) );
						}

						return $redirect;

					}, 10, 3 );


				}
			} );
		}
	}

	public static function isUnderRestriction( $postId ) {
		// todo hook
		return get_post_meta( $postId, self::UNDER_RESTRICTION_META_KEY, true ) === 'yes';
	}

}