<?php
namespace Windzfare\Admin\Menu;

defined( 'ABSPATH' ) || die();

class Init { 
    private static $page_slug	 = 'windzfare-dashboard';
    static $menu_slug = '';

    public function __construct() {

        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ], 21 );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
    }

    public static function enqueue_scripts( $hook ) {
        if ( self::$menu_slug !== $hook || ! current_user_can( 'manage_options' ) ) {
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

    public static function add_menu() {
        self::$menu_slug = add_menu_page(
            __( 'Windzfare Dashboard', 'windzfare' ),
            __( 'Windzfare', 'windzfare' ),
            'manage_options',
            self::$page_slug,
            [ __CLASS__, 'render_main' ],
            WINDZFARE_IMG_DIR_URL .'/fav.png',
            2
        );
    }

    private static function load_template( $template ) { 

        $file = WINDZFARE_MENU_DIR_PATH . '/view/' . $template . '.php';
        if ( is_readable( $file ) ) {
            include( $file );
        }
    }

    public static function render_main() {
        self::load_template( 'main' );
    }

}
