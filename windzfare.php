<?php
	/*
	Plugin Name: Windzfare
	Plugin URI: https://wordpress.org/plugins/windzfare
	Description: Windzfare is woocommerce based donation and fundraising plugin.
	Version: 1.0.1
	Author: WindzWP
	Author URI: https://windzwp.com/
	License: GPLv3 or later
    */
    
    if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly. 
    }
    
    require_once __DIR__ . '/vendor/autoload.php';
    final class Windzfare{

        const version = '1.0.0';
        const api_url = 'http://api.windzwp.com/public/';

        private function __construct() {

            $this->define_constants();
            register_activation_hook( __FILE__, [ $this, 'activate' ] );
            add_action( 'plugins_loaded', [ $this, 'init' ] );
            add_action( 'init', [ $this, 'i18n' ] );

        }
 
        public static function instance() {

            static $instance = false;
			if ( ! $instance ) {
				$instance = new self();
			}
            return $instance;
            
		}
		
		public function i18n() {
			load_plugin_textdomain( 'windzfare', false, basename( dirname( __FILE__ ) ) . '/languages' );	
		}

        public function define_constants(){
            
			define( 'WINDZFARE_VERSION', self::version );
			
            define( 'WINDZFARE_MINIMUM_WOOCOMMERCE_VERSION', '4.0.0' ); 
			define( 'WINDZFARE_MINIMUM_PHP_VERSION', '5.6' );
			
            define( 'WINDZFARE_DIR_URL', plugin_dir_url( __FILE__ ) );
            define( 'WINDZFARE_DIR_PATH', plugin_dir_path( __FILE__ ) );

            define( 'WINDZFARE_INCLUDES_DIR_PATH', WINDZFARE_DIR_PATH . '/includes' );
			define( 'WINDZFARE_ADMIN_DIR_PATH', WINDZFARE_INCLUDES_DIR_PATH . '/Admin' );
			
            define( 'WINDZFARE_ASSETS_DIR_URL', WINDZFARE_DIR_URL . '/assets' );
            
            define( 'WINDZFARE_CSS_DIR_URL', WINDZFARE_ASSETS_DIR_URL . '/css' );
            define( 'WINDZFARE_JS_DIR_URL', WINDZFARE_ASSETS_DIR_URL . '/js' );
            define( 'WINDZFARE_IMG_DIR_URL', WINDZFARE_ASSETS_DIR_URL . '/images' );

            define( 'WINDZFARE_ADMIN_CSS_DIR_URL', WINDZFARE_CSS_DIR_URL . '/admin' );
            define( 'WINDZFARE_ADMIN_JS_DIR_URL', WINDZFARE_JS_DIR_URL . '/admin' );
            define( 'WINDZFARE_ADMIN_IMG_DIR_URL', WINDZFARE_IMG_DIR_URL . '/admin' );

			define( 'WINDZFARE_MENU_DIR_PATH', WINDZFARE_ADMIN_DIR_PATH . '/Menu' );
			
        }
        
        public function init(){
            if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { 
				add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
				return;
			}
			global $woocommerce;
			if ( ! version_compare( $woocommerce->version, WINDZFARE_MINIMUM_WOOCOMMERCE_VERSION, '>=' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
				
				return;
			}
			
			if ( version_compare( PHP_VERSION, WINDZFARE_MINIMUM_PHP_VERSION, '<' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
				
				return;
			}
			 
            new Windzfare\Hook\Init;
        }
        
        public function activate(){
            $installed = get_option( 'windzfare_installed' );
            if( ! $installed ){
                update_option( 'windzfare_installed', time() );
            }
            
            update_option( 'windzfare_version', WINDZFARE_VERSION );
		}
		
        public function admin_notice_missing_main_plugin() {
			
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			
			$message = sprintf(
				
				esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'windzfare' ),
				'<strong>' . esc_html__( 'Windzfare', 'windzfare' ) . '</strong>',
				'<strong>' . esc_html__( 'Woocommerce', 'windzfare' ) . '</strong>'
			);
			
			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
			
		}
		
		public function admin_notice_minimum_woocommerce_version() {
			
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			
			$message = sprintf(
				
				esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'windzfare' ),
				'<strong>' . esc_html__( 'Windzfare', 'windzfare' ) . '</strong>',
				'<strong>' . esc_html__( 'Woocommerce', 'windzfare' ) . '</strong>',
				WINDZFARE_MINIMUM_WOOCOMMERCE_VERSION
			);
			
			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
			
		}
		
		public function admin_notice_minimum_php_version() {
			
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			
			$message = sprintf(
				
				esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'windzfare' ),
				'<strong>' . esc_html__( 'Windzfare', 'windzfare' ) . '</strong>',
				'<strong>' . esc_html__( 'PHP', 'windzfare' ) . '</strong>',
				WINDZFARE_MINIMUM_PHP_VERSION
			);
			
			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
			
		}
    }

    function windzfare(){
        return Windzfare::instance();
    }
	windzfare();