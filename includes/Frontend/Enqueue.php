<?php
namespace Windzfare\Frontend;

defined( 'ABSPATH' ) || die();

class Enqueue { 

    public function __construct() {
        add_action( 'elementor/frontend/after_register_styles', [$this, 'widget_register_styles']);
        add_action( 'wp_enqueue_scripts', [$this, 'widget_enqueue_styles']);
        add_action( 'elementor/editor/after_enqueue_styles', [$this, 'widget_enqueue_styles']);
        add_action( 'elementor/preview/enqueue_styles', [$this, 'widget_enqueue_styles']);
    
        add_action( 'elementor/frontend/after_enqueue_scripts', [$this, 'widget_script'] );
        add_action( 'elementor/editor/after_enqueue_styles', [$this, 'editor_enqueue_styles' ] );
         
    }

    public function widget_register_styles() {
        wp_register_style( 'windzfare-libraries',  
            WINDZFARE_CSS_DIR_URL . '/libraries.min.css');
        wp_register_style( 'windzfare-styles', 
            WINDZFARE_CSS_DIR_URL . '/styles.min.css' );
    } 
    public function widget_enqueue_styles() {
        wp_enqueue_style( 'windzfare-libraries' );
        wp_enqueue_style( 'windzfare-widgets' );
    } 
    
    public function widget_script() {
        wp_enqueue_script( 'windzfare-libraries', 
            WINDZFARE_JS_DIR_URL . '/libraries.min.js',
            [ 'jquery'],
            false,
            false 
        );
        wp_enqueue_script( 'windzfare-scripts',
            WINDZFARE_JS_DIR_URL . '/scripts.min.js',
            [ 'jquery'],
            '1.0',
            true
        );
    }
}
