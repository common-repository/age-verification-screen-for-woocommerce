<?php

namespace AlpakaWP\AgeVerification\Customizer;

use AlpakaWP\AgeVerification\Core\ServiceContainerTrait;
use WP_Customize_Color_Control;
use WP_Customize_Manager;
class Customizer {
    const SECTION = 'age_checker_customizer_section';

    const SETTINGS_PREFIX = 'age_checker_customizer_';

    use ServiceContainerTrait;
    public function __construct() {
        add_action( 'wp_head', function () {
            ?>
            <style>
                :root {
                    --age-checker-background-blur: <?php 
            echo esc_html( $this->getOption( 'background_blur', 7 ) );
            ?>px;
                    --age-checker-modal-background-color: <?php 
            echo esc_html( $this->getOption( 'modal_background_color', '#ffffff' ) );
            ?>;
                    --age-checker-background-color: <?php 
            echo esc_html( $this->getOption( 'background_color', '#ffffff' ) );
            ?>;
                    --age-checker-background-image: url( <?php 
            echo esc_html( $this->getOption( 'background_image', '' ) );
            ?> );
                    --age-checker-text-color: <?php 
            echo esc_html( $this->getOption( 'text_color', '#000000' ) );
            ?>;
                }
            </style>
			<?php 
        } );
        add_action(
            'customize_controls_enqueue_scripts',
            function () {
                wp_register_script(
                    'age-checker-customizer',
                    $this->getContainer()->getFileManager()->locateAsset( 'frontend/customizer.js' ),
                    array('jquery', 'customize-preview'),
                    1
                );
                wp_localize_script( 'age-checker-customizer', 'ageCheckerCustomizerSettings', [
                    'section'    => self::SECTION,
                    'prefix'     => self::SETTINGS_PREFIX,
                    'previewKey' => 'age-checker-preview',
                ] );
                wp_enqueue_script( 'age-checker-customizer' );
            },
            999,
            1
        );
        add_action( 'customize_preview_init', function () {
            wp_register_script(
                'age-checker-customizer-preview',
                $this->getContainer()->getFileManager()->locateAsset( 'frontend/customizer-preview.js' ),
                array('jquery', 'customize-preview'),
                1,
                true
            );
            wp_localize_script( 'age-checker-customizer-preview', 'ageCheckerCustomizerSettings', [
                'section'    => self::SECTION,
                'prefix'     => self::SETTINGS_PREFIX,
                'previewKey' => 'age-checker-preview',
            ] );
            wp_enqueue_script( 'age-checker-customizer-preview' );
        } );
        add_action( 'customize_register', array($this, 'register') );
    }

    public function register( WP_Customize_Manager $customizer ) {
        $customizer->add_section( self::SECTION, array(
            'title'    => __( 'Age Verification Screen', 'age-verification-screen-for-woocommerce' ),
            'priority' => 100,
        ) );
        $this->registerSettings( $customizer );
        $this->addControls( $customizer );
    }

    protected function registerSettings( WP_Customize_Manager $customizer ) {
        foreach ( $this->getSettings() as $settingKey => $setting ) {
            $customizer->add_setting( self::SETTINGS_PREFIX . $settingKey, $setting );
        }
    }

    protected function addControls( WP_Customize_Manager $customizer ) {
        $customizer->add_control( $this->getSettingKey( 'view_mode' ), array(
            'type'        => 'radio',
            'section'     => self::SECTION,
            'label'       => __( 'Display type', 'age-verification-screen-for-woocommerce' ),
            'description' => __( "<br><strong>Modal box:</strong> Centered pop-up w/ blurred background of the destination page. <br><br> <strong>Full page:</strong> The pop-up that covers the entire page w/ image or filled color on the background.", 'age-verification-screen-for-woocommerce' ),
            'choices'     => array(
                'modal' => __( 'Modal box', 'age-verification-screen-for-woocommerce' ),
                'full'  => __( 'Full page', 'age-verification-screen-for-woocommerce' ),
            ),
        ) );
        $freeBadge = '';
        if ( !avfw_fs()->is_premium() ) {
            $freeBadge = ' (' . __( 'Available only in the premium version', 'age-verification-screen-for-woocommerce' ) . ')';
        }
        $customizer->add_control( $this->getSettingKey( 'confirmation_type' ), array(
            'type'    => 'radio',
            'section' => self::SECTION,
            'label'   => __( 'Confirmation type', 'age-verification-screen-for-woocommerce' ),
            'choices' => array(
                'checkbox'   => __( 'Checkbox', 'age-verification-screen-for-woocommerce' ),
                'buttons'    => __( 'Buttons', 'age-verification-screen-for-woocommerce' ),
                'age_select' => __( 'Age selection', 'age-verification-screen-for-woocommerce' ) . $freeBadge,
            ),
        ) );
        $customizer->add_control( $this->getSettingKey( 'header' ), array(
            'type'    => 'text',
            'section' => self::SECTION,
            'label'   => __( 'Heading', 'age-verification-screen-for-woocommerce' ),
        ) );
        $customizer->add_control( $this->getSettingKey( 'description' ), array(
            'type'    => 'text',
            'section' => self::SECTION,
            'label'   => __( 'Short description', 'age-verification-screen-for-woocommerce' ),
        ) );
        $customizer->add_control( $this->getSettingKey( 'checkbox_text' ), array(
            'type'            => 'text',
            'section'         => self::SECTION,
            'label'           => __( 'Text beside checkbox', 'age-verification-screen-for-woocommerce' ),
            'active_callback' => function () {
                return $this->getConfirmationType() === 'checkbox';
            },
        ) );
        $customizer->add_control( $this->getSettingKey( 'confirm_button_text' ), array(
            'type'    => 'text',
            'section' => self::SECTION,
            'label'   => __( 'Text on the Confirm button', 'age-verification-screen-for-woocommerce' ),
        ) );
        $customizer->add_control( $this->getSettingKey( 'cancel_button_text' ), array(
            'type'            => 'text',
            'section'         => self::SECTION,
            'label'           => __( 'Text on the Cancel button', 'age-verification-screen-for-woocommerce' ),
            'active_callback' => function () {
                return $this->getConfirmationType() === 'buttons';
            },
        ) );
        $customizer->add_control( $this->getSettingKey( 'background_blur' ), array(
            'type'            => 'range',
            'section'         => self::SECTION,
            'label'           => __( 'Blur opacity', 'age-verification-screen-for-woocommerce' ),
            'input_attrs'     => array(
                'min'   => 0,
                'max'   => 20,
                'step'  => 1,
                'style' => 'width: 100%',
            ),
            'active_callback' => function () {
                return $this->getViewMode() === 'modal';
            },
        ) );
        $customizer->add_control( new WP_Customize_Color_Control($customizer, $this->getSettingKey( 'modal_background_color' ), array(
            'label'           => __( 'Modal background color', 'age-verification-screen-for-woocommerce' ),
            'section'         => self::SECTION,
            'settings'        => $this->getSettingKey( 'modal_background_color' ),
            'active_callback' => function () {
                return $this->getViewMode() === 'modal';
            },
        )) );
        $customizer->add_control( new \WP_Customize_Image_Control($customizer, $this->getSettingKey( 'background_image' ), array(
            'label'           => __( 'Background image', 'age-verification-screen-for-woocommerce' ),
            'section'         => self::SECTION,
            'settings'        => $this->getSettingKey( 'background_image' ),
            'active_callback' => function () {
                return $this->getViewMode() === 'full';
            },
        )) );
        $customizer->add_control( new WP_Customize_Color_Control($customizer, $this->getSettingKey( 'background_color' ), array(
            'label'           => __( 'Background color', 'age-verification-screen-for-woocommerce' ),
            'section'         => self::SECTION,
            'settings'        => $this->getSettingKey( 'background_color' ),
            'active_callback' => function () {
                return $this->getViewMode() === 'full';
            },
        )) );
        $customizer->add_control( new WP_Customize_Color_Control($customizer, $this->getSettingKey( 'text_color' ), array(
            'label'    => __( 'Text color', 'age-verification-screen-for-woocommerce' ),
            'section'  => self::SECTION,
            'settings' => $this->getSettingKey( 'text_color' ),
        )) );
        $customizer->add_control( $this->getSettingKey( 'warning' ), array(
            'type'            => 'text',
            'section'         => self::SECTION,
            'label'           => __( 'Warning message', 'age-verification-screen-for-woocommerce' ),
            'active_callback' => function () {
                return $this->getConfirmationType() === 'age_select';
            },
        ) );
    }

    protected function getSettings() {
        return array(
            'view_mode'              => array(
                'type'      => 'option',
                'default'   => 'modal',
                'transport' => 'refresh',
            ),
            'confirmation_type'      => array(
                'type'              => 'option',
                'default'           => 'checkbox',
                'transport'         => 'refresh',
                'sanitize_callback' => function ( $val ) {
                    $availableValues = ['checkbox', 'buttons'];
                    return ( in_array( $val, $availableValues ) ? $val : 'checkbox' );
                },
            ),
            'background_blur'        => array(
                'type'      => 'option',
                'default'   => '7',
                'transport' => 'postMessage',
            ),
            'modal_background_color' => array(
                'default'   => "#ffffff",
                'type'      => 'option',
                'transport' => 'postMessage',
            ),
            'background_color'       => array(
                'default'   => "#ffffff",
                'type'      => 'option',
                'transport' => 'postMessage',
            ),
            'background_image'       => array(
                'default'           => '',
                'transport'         => 'postMessage',
                'sanitize_callback' => 'esc_url_raw',
            ),
            'text_color'             => array(
                'default'   => "#000000",
                'type'      => 'option',
                'transport' => 'postMessage',
            ),
            'header'                 => array(
                'default'   => __( 'Confirm your age', 'age-verification-screen-for-woocommerce' ),
                'type'      => 'option',
                'transport' => 'postMessage',
            ),
            'description'            => array(
                'default'   => __( 'We require users to be 18 years to use the site', 'age-verification-screen-for-woocommerce' ),
                'type'      => 'option',
                'transport' => 'postMessage',
            ),
            'checkbox_text'          => array(
                'default'   => __( 'I\'m over 18 y.o.', 'age-verification-screen-for-woocommerce' ),
                'type'      => 'option',
                'transport' => 'postMessage',
            ),
            'confirm_button_text'    => array(
                'default'   => __( 'Confirm', 'age-verification-screen-for-woocommerce' ),
                'type'      => 'option',
                'transport' => 'postMessage',
            ),
            'cancel_button_text'     => array(
                'default'   => __( 'Cancel', 'age-verification-screen-for-woocommerce' ),
                'type'      => 'option',
                'transport' => 'postMessage',
            ),
            'warning'                => array(
                'default'   => __( 'Your age is below the minimum required', 'age-verification-screen-for-woocommerce' ),
                'type'      => 'option',
                'transport' => 'postMessage',
            ),
        );
    }

    public function getViewMode() {
        return $this->getOption( 'view_mode', 'modal' );
    }

    public function getHeader() {
        return $this->getOption( 'header', __( 'Confirm your age', 'age-verification-screen-for-woocommerce' ) );
    }

    public function getDescription() {
        return $this->getOption( 'description', __( 'We require users to be 18 years to use the site', 'age-verification-screen-for-woocommerce' ) );
    }

    public function getConfirmButtonText() {
        return $this->getOption( 'confirm_button_text', __( 'Confirm', 'age-verification-screen-for-woocommerce' ) );
    }

    public function getCancelButtonText() {
        return $this->getOption( 'cancel_button_text', __( 'Cancel', 'age-verification-screen-for-woocommerce' ) );
    }

    public function getCheckboxText() {
        return $this->getOption( 'checkbox_text', __( 'Confirm', 'age-verification-screen-for-woocommerce' ) );
    }

    public function getWarning() {
        return $this->getOption( 'warning', __( 'Confirm', 'todo' ) );
    }

    public function getConfirmationType() {
        return $this->getOption( 'confirmation_type', 'checkbox' );
    }

    public function getOption( $key, $default = null ) {
        return get_option( $this->getSettingKey( $key ), $default );
    }

    public function getSettingKey( $key ) {
        return self::SETTINGS_PREFIX . $key;
    }

}
