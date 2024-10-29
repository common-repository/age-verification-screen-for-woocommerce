<?php namespace AlpakaWP\AgeVerification\Frontend;

use AlpakaWP\AgeVerification\Admin\PostRestrictionManager;
use AlpakaWP\AgeVerification\Admin\TaxonomyRestrictionManager;
use AlpakaWP\AgeVerification\Config\Config;
use AlpakaWP\AgeVerification\Core\ServiceContainerTrait;

/**
 * Class Frontend
 *
 * @package AlpakaWP\AgeVerification\Frontend
 */
class Frontend {

	use ServiceContainerTrait;

	/**
	 * Frontend constructor.
	 *
	 * Register menu items and handlers
	 */
	public function __construct() {

		add_action( 'wp_footer', function () {

			$isPageUnderRestriction = apply_filters( 'age_checker/frontend/is_page_under_restriction', $this->isPageUnderProtection() );

			if ( $isPageUnderRestriction ) {
				$this->renderProtectionLayer();
			} else if ( isset( $_GET['age-checker-preview'] ) && is_customize_preview() ) {
				$this->renderProtectionLayer();
			}
		} );
	}

	public function renderProtectionLayer() {
		$this->getContainer()->getFileManager()->includeTemplate( 'frontend/age-protection.php', [
			'customizer'  => $this->getContainer()->getCustomizer(),
			'fileManager' => $this->getContainer()->getFileManager()
		] );
	}


	public function isPageUnderProtection() {

		if ( is_admin() ) {
			return false;
		}

		if ( current_user_can( 'administrator' ) ) {
			return false;
		}

		if ( ! empty( $_COOKIE[ Config::getCookieHashKey() ] ) && $_COOKIE[ Config::getCookieHashKey() ] === Config::getCookieConfirmedValue() ) {
			return false;
		}

		if ( Config::getProtectionType() === 'full' ) {
			return true;
		} else {
			global $wp_query;

			$rules  = Config::getPartialProtectionRules();
			$object = $wp_query->get_queried_object();

			if ( $object instanceof \WP_Term ) {

				if ( ! empty( $rules['taxonomy'][ $object->taxonomy ] ) ) {
					if ( $rules['taxonomy'][ $object->taxonomy ] === 'full' ) {
						return true;
					} else if ( $rules['taxonomy'][ $object->taxonomy ] === 'partial' ) {
						if ( TaxonomyRestrictionManager::isUnderRestriction( $object->term_id ) ) {
							return true;
						}
					}
				}
			} else if ( $object instanceof \WP_Post_Type ) {
				if ( ! empty( $rules['post_type'][ $object->name ] ) && $rules['post_type'][ $object->name ] === 'full' ) {
					return true;
				}
			} else if ( $object instanceof \WP_Post ) {
				if ( Config::getFailureRedirectType() === 'site_page' && intval( Config::getFailureRedirectPage() ) === intval( $object->ID ) ) {
					return false;
				}

				if ( ! empty( $rules['post_type'][ $object->post_type ] ) && $rules['post_type'][ $object->post_type ] === 'full' ) {
					return true;
				}

				if ( PostRestrictionManager::isUnderRestriction( $object->ID ) ) {
					return true;
				}

				foreach ( get_object_taxonomies( $object ) as $taxonomyName ) {

					if ( array_key_exists( $taxonomyName, $rules['taxonomy'] ) ) {

						if ( $rules['taxonomy'][ $taxonomyName ] === 'full' ) {
							return true;
						}

						foreach ( wp_get_post_terms( $object->ID, $taxonomyName, [ 'fields' => 'ids' ] ) as $termId ) {
							if ( TaxonomyRestrictionManager::isUnderRestriction( $termId ) ) {
								return true;
							}
						}
					}
				}
			}
		}

		return false;
	}
}