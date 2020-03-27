<?php
namespace Windzfare\Frontend;

defined( 'ABSPATH' ) || die();

class Enqueue { 

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action( 'elementor/editor/after_enqueue_styles', [$this, 'enqueue_styles']);
        add_action( 'elementor/preview/enqueue_styles', [$this, 'enqueue_styles']);
    
        add_action( 'elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_styles'] );
         
    }
 
    public function enqueue_styles() {
        wp_enqueue_style( 'windzfare-libraries',  
            WINDZFARE_CSS_DIR_URL . '/libraries.min.css');

        wp_enqueue_style( 'windzfare-styles', 
            WINDZFARE_CSS_DIR_URL . '/styles.min.css' );

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
    
    public function enqueue_script() {

    }
}
