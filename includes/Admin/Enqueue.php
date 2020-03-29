<?php
namespace Windzfare\Admin;

defined( 'ABSPATH' ) || die();

class Enqueue { 

    public function __construct() {

        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts' ] );
         
    }
    
    public static function admin_enqueue_scripts( $hook ) {
        
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        wp_enqueue_style(
            'windzfare-admin',
            WINDZFARE_ADMIN_CSS_DIR_URL . '/admin.min.css',
            null,
            '1.0'
        );

        wp_enqueue_script(
            'windzfare-admin',
            WINDZFARE_ADMIN_JS_DIR_URL . '/admin.min.js',
            ['jquery'],
            '1.0',
            true
        );

    }
}
