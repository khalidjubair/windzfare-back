<?php

namespace Windzfare\Hook;
use Windzfare\Helpers\Utils as Utils;

class Init{
    function __construct(){

        //Admin hook
        if( is_admin() ){
            new \Windzfare\Admin\Init;  
        }
 
        //Cptui
        new \Windzfare\Cptui\Init;

        new \Windzfare\Frontend\Init;
        new \Windzfare\Helpers\Shortcodes;

        add_action( 'woocommerce_add_to_cart_redirect', [ __CLASS__, 'redirect_to_checkout' ] );
        add_action( 'woocommerce_add_to_cart_validation', [ __CLASS__, 'remove_item_from_cart' ], 10, 3 );
        add_action( 'woocommerce_add_cart_item', [ __CLASS__, 'save_user_funding_to_cookie' ], 10, 2 ); 
        add_action( 'woocommerce_before_calculate_totals', [ __CLASS__, 'add_user_funding' ] );
        // add_filter( 'template_include', [ __CLASS__, 'include_template' ], 10, 2 ); 
    }
    public static function include_template( $template_path ) {
        if ( get_post_type() == 'product' ) {
            if ( is_single() ) {
                
                $template_path = WINDZFARE_TEMPLATES_DIR_PATH . '/single-campaign.php';
            }
        }
        return $template_path;
    }
    public static function redirect_to_checkout( $url ) {
        if ( ! empty( $_REQUEST['add-to-cart'] ) ){
            $product_id = absint( $_REQUEST['add-to-cart'] );
            if ( Utils::is_campaign( $product_id ) ){
                $checkout_url = wc_get_checkout_url();
                wc_clear_notices();

                return $checkout_url;
            }
        }
        return $url;
    }

    public static function redirect_to_cart( $url ) {

        global $woocommerce;
        if ( ! empty( $_REQUEST['add-to-cart'] ) ){
            $product_id = absint( $_REQUEST['add-to-cart'] );
            if ( Utils::is_campaign( $product_id ) ){
                $checkout_url = $woocommerce->cart->get_cart_url();
                wc_clear_notices();

                return $checkout_url;
            }
        }

        return $url;
    }
    
    public static function remove_item_from_cart( $passed, $product_id, $quantity ) {
        
        $product = wc_get_product( $product_id );

        if ( Utils::is_campaign( $product->get_id() ) ){
            foreach ( WC()->cart->cart_contents as $item_cart_key => $prod_in_cart ) {
                WC()->cart->remove_cart_item( $item_cart_key );
            }
        }

        foreach ( WC()->cart->cart_contents as $item_cart_key => $prod_in_cart ) {
            WC()->cart->remove_cart_item( $item_cart_key );
        }

        return $passed;
    }

    public static function save_user_funding_to_cookie( $array, $int ) {
        
        if ( Utils::is_campaign( $array['data']->get_id() ) ){
            if ( ! empty( $_POST['wp_fare_amount'] ) ){
                $donate_amount = sanitize_text_field( $_POST['wp_fare_amount'] );
                WC()->session->set( 'wp_donate_amount', $donate_amount );

                if ( ! empty( $_POST['wp_rewards_index'] ) ){
                    $wp_rewards_index = (int) sanitize_text_field( $_POST['wp_rewards_index'] ) -1;
                    $_cf_product_author_id = sanitize_text_field( $_POST['_cf_product_author_id'] );
                    $product_id = sanitize_text_field( $_POST['add-to-cart'] );
                    WC()->session->set( 'wp_rewards_data', [ 'rewards_index' => $wp_rewards_index, 'product_id' => $product_id, '_cf_product_author_id' => $_cf_product_author_id ] );
                }else{
                    WC()->session->__unset( 'wp_rewards_data' );
                }
            }
        }
        return $array;
    }

    public static function add_user_funding(){
        
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( Utils::is_campaign( $cart_item['data']->get_id() ) ){
                $donate_cart_amount = WC()->session->get('wp_donate_amount');
                
                if ( ! empty( $donate_cart_amount ) ){
                    $cart_item['data']->set_price( $donate_cart_amount );
                }
            }
        }
    }
}