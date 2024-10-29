<?php namespace AlpakaWP\AgeVerification\Config;

use AlpakaWP\AgeVerification\Core\ServiceContainer;
use WP_Post_Type;

class Config {

	public static function getCookieConfirmedValue() {
		return self::returnValue( 'cookie_confirmed_value', 'confirmed' );

	}

	/**
	 * @return WP_Post_Type[]
	 */
	public static function getSupportedPostTypes() {
		$freeTypes = [
			'post',
			'page',
			'product',
			'attachment'
		];

		$postTypes = get_post_types( [ 'public' => true ], 'objects' );

		if ( ! avfw_fs()->is_premium() ) {
			$postTypes = array_filter( $postTypes, function ( $type ) use ( $freeTypes ) {
				return in_array( $type, $freeTypes );
			}, ARRAY_FILTER_USE_KEY );
		}

		return self::returnValue( 'supported_post_types', $postTypes );
	}

	public static function getSupportedTaxonomies() {
		$freeTaxonomies = [
			'category',
			'post_tag',
			'product_cat',
			'product_tag'
		];

		$taxonomies = get_taxonomies( [ 'public' => true, 'show_ui' => true ], 'objects' );

		if ( ! avfw_fs()->is_premium() ) {
			$taxonomies = array_filter( $taxonomies, function ( $type ) use ( $freeTaxonomies ) {
				return in_array( $type, $freeTaxonomies );
			}, ARRAY_FILTER_USE_KEY );

		}

		return $taxonomies;
	}

	public static function getFailureRedirectURL() {

		if ( Config::getFailureRedirectType() === 'redirect_url' ) {
			return self::getFailureRedirectCustomURL();
		} else if ( Config::getFailureRedirectType() === 'site_page' ) {
			return self::getFailureRedirectPageURL();
		} else {
			return '#';
		}
	}

	public static function getFailureRedirectCustomURL() {
		$key = 'failure_redirect';

		$data = self::getFromSettings( $key, array(
			'redirect_url' => home_url()
		) );

		return self::returnValue( 'failure_redirect_redirect_url', $data['redirect_url'] );
	}

	public static function getFailureRedirectPageURL() {

		$pageId = self::getFailureRedirectPage();

		$url = false;

		if ( $pageId ) {
			$url = get_permalink( $pageId );
		}

		$url = $url ? $url : '#';

		return self::returnValue( 'failure_redirect_redirect_page_url', $url );
	}

	public static function getFailureRedirectPage() {
		$key = 'failure_redirect';

		$data = self::getFromSettings( $key, array(
			'site_page' => 0
		) );

		return self::returnValue( 'failure_redirect_redirect_page_id', intval($data['site_page']) );
	}

	public static function getFailureRedirectType() {
		$key = 'failure_redirect';

		$data = self::getFromSettings( $key, array(
			'failure_type' => 'previous_page'
		) );

		return self::returnValue( 'failure_redirect_type', $data['failure_type'] );
	}

	public static function getCookieHashKey() {
		$key = 'cookie';

		$data = self::getFromSettings( $key, array(
			'cookie_hash' => md5( site_url() )
		) );

		return self::returnValue( 'cookie_hash_key', $data['cookie_hash'] );
	}

	public static function getCookieDuration() {
		$key = 'cookie';

		$data = self::getFromSettings( $key, array(
			'duration' => 30
		) );

		return self::returnValue( 'cookie_duration', $data['duration'] );
	}

	public static function getProtectionType() {
		return self::getPartialProtectionRules()['restriction_type'];
	}

	public static function getPartialProtectionRules() {
		$key = 'restriction_type';

		$data = self::getFromSettings( $key, array(
			'restriction_type' => 'full',
			'post_type'        => [],
			'taxonomy'         => []
		) );

		return self::returnValue( 'restriction_rules', $data );
	}

	public static function getMinimumAllowedAge() {
		$key = 'minimum_allowed_age';

		return self::returnValue( $key, self::getFromSettings( $key, 18 ) );
	}

	private static function returnValue( $key, $value ) {
		return apply_filters( 'age_checker/config/get_' . $key, $value );
	}

	private static function getFromSettings( $key, $default ) {
		return ServiceContainer::getInstance()->getSettings()->get( $key, $default );
	}

	private static function getValue( $key, $subKey = null, $default = null ) {
		if ( $subKey ) {
			$data = self::getFromSettings( $key, array(
				$subKey => $default
			) );

			return self::returnValue( $key, $data[ $subKey ] );
		}

		return self::returnValue( $key, self::getFromSettings( $key, $default ) );
	}
}