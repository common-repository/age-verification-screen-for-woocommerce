<?php

namespace AlpakaWP\AgeVerification\Settings;

use AlpakaWP\AgeVerification\AgeVerificationPlugin;
use AlpakaWP\AgeVerification\Core\ServiceContainerTrait;
/**
 * Class Settings
 *
 * @package Settings
 */
class Settings {
    use ServiceContainerTrait;
    const SETTINGS_PREFIX = 'age_checker_';

    const SETTINGS_PAGE = 'age_checker_settings';

    /**
     * Array with the settings
     *
     * @var array
     */
    private $settings;

    /**
     * Settings constructor.
     */
    public function __construct() {
        add_action( 'init', array($this, 'initSettings') );
        add_filter( 'woocommerce_settings_tabs_' . self::SETTINGS_PAGE, array($this, 'registerSettings') );
        add_filter( 'woocommerce_settings_tabs_array', array($this, 'addSettingsTab'), 50 );
        add_action( 'woocommerce_update_options_' . self::SETTINGS_PAGE, array($this, 'updateSettings') );
        $this->getContainer()->add( 'settings.restrictionRulesOption', new RestrictionRulesOption() );
        $this->getContainer()->add( 'settings.failureRedirectOption', new FailureRedirectOption() );
        $this->getContainer()->add( 'settings.customizeButtonOption', new CustomizeButtonOption() );
        $this->getContainer()->add( 'settings.cookieOption', new CookieOption() );
        if ( !avfw_fs()->is_premium() ) {
            $template = 'upgrade-alert.php';
            add_action( 'woocommerce_settings_' . self::SETTINGS_PAGE, function () use($template) {
                $upgradeUrl = avfw_fs()->get_upgrade_url();
                if ( !avfw_fs()->is_registered() && !avfw_fs()->is_anonymous() ) {
                    // $upgradeUrl = avfw_fs()->get_activation_url();
                }
                $this->getContainer()->getFileManager()->includeTemplate( 'admin/alerts/' . $template, [
                    'upgradeUrl'   => $upgradeUrl,
                    'contactUsUrl' => avfw_fs()->contact_url(),
                ] );
            } );
        }
    }

    /**
     * Handle updating settings
     */
    public function updateSettings() {
        woocommerce_update_options( $this->settings );
    }

    /**
     * Init all settings
     */
    public function initSettings() {
        $settings = array(
            'settings'                       => array(
                'title' => __( 'Age Verification Screen', 'age-verification-screen-for-woocommerce' ),
                'desc'  => __( 'This controls look and feel of Age Verification Screen at your store.', 'age-verification-screen-for-woocommerce' ),
                'id'    => self::SETTINGS_PREFIX . 'settings_begin',
                'type'  => 'title',
            ),
            CustomizeButtonOption::FIELD_ID  => $this->getContainer()->get( 'settings.customizeButtonOption' )->getWooCommerceArrayFormat(),
            RestrictionRulesOption::FIELD_ID => $this->getContainer()->get( 'settings.restrictionRulesOption' )->getWooCommerceArrayFormat(),
            'minimum_allowed_age'            => array(
                'title'             => __( 'Minimum allowed age', 'age-verification-screen-for-woocommerce' ),
                'desc'              => __( 'Select the minimum allowed age for your website. A website that promotes alcohol in the United States might set this to 21, for example.', 'age-verification-screen-for-woocommerce' ),
                'id'                => self::SETTINGS_PREFIX . 'minimum_allowed_age',
                'type'              => 'number',
                'custom_attributes' => array(
                    'min'  => 1,
                    'step' => 1,
                ),
                'default'           => 18,
            ),
            FailureRedirectOption::FIELD_ID  => $this->getContainer()->get( 'settings.failureRedirectOption' )->getWooCommerceArrayFormat(),
            CookieOption::FIELD_ID           => $this->getContainer()->get( 'settings.cookieOption' )->getWooCommerceArrayFormat(),
            'section_end'                    => array(
                'type' => 'sectionend',
                'id'   => self::SETTINGS_PREFIX . 'settings_end',
            ),
        );
        $this->settings = apply_filters( 'age_checker/settings/settings', $settings );
    }

    /**
     * Add own settings tab
     *
     * @param array $settings_tabs
     *
     * @return mixed
     */
    public function addSettingsTab( $settings_tabs ) {
        $settings_tabs[self::SETTINGS_PAGE] = __( 'Age Verification Screen', 'age-verification-screen-for-woocommerce' );
        return $settings_tabs;
    }

    /**
     * Add settings to WooCommerce
     */
    public function registerSettings() {
        wp_enqueue_style(
            'oc-admin-settings-css',
            $this->getContainer()->getFileManager()->locateAsset( 'admin/settings.css' ),
            [],
            AgeVerificationPlugin::VERSION
        );
        wp_enqueue_script(
            'oc-admin-settings-js',
            $this->getContainer()->getFileManager()->locateAsset( 'admin/settings.js' ),
            ['jquery'],
            AgeVerificationPlugin::VERSION
        );
        woocommerce_admin_fields( $this->settings );
    }

    /**
     * Get setting by name
     *
     * @param string $option_name
     * @param mixed $default
     *
     * @return mixed
     */
    public function get( $option_name, $default = null ) {
        return get_option( self::SETTINGS_PREFIX . $option_name, $default );
    }

    public function getAllSettings() {
        $settings = array_filter( $this->settings, function ( $setting ) {
            return !in_array( $setting['type'], array('section', 'sectionend', 'title') );
        } );
        return array_map( function ( $key, $value ) {
            return $this->get( $key, $value['default'] );
        }, array_keys( $settings ), $settings );
    }

    /**
     * Get url to settings page
     *
     * @return string
     */
    public function getLink() {
        return admin_url( 'admin.php?page=wc-settings&tab=' . self::SETTINGS_PAGE );
    }

}
