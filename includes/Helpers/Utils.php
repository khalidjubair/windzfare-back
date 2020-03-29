<?php

namespace Windzfare\Helpers;

class Utils {

    function __construct(){}

    public static function mk_class( $dirname ){
        $dirname = pathinfo( $dirname, PATHINFO_FILENAME );
        $class_name	 = explode( '-', $dirname );
        $class_name	 = array_map( 'ucfirst', $class_name );
        $class_name	 = implode( '_', $class_name );

        return $class_name;
    }

    public static function get_option( $option, $section, $default = '' ){
        $options = get_option( $section );
        if ( isset( $options[ $option ] ) ) {
            return $options[ $option ];
        }
        return $default;
    }

    public static function is_campaign( $product_id ){
        return ( get_post_meta( $product_id, '_windzfare', true ) == 'yes' ) ? true : false ;
    }

    public static function update_option( $key='windzfare_options', $c='widget_list', $value = '', $senitize_func = 'sanitize_text_field' ){
        $data_all = get_option( $key );
        $value = self::sanitize( $value, $senitize_func);
        $data_all[ $c ] = $value;
        update_option( $key, $data_all );
    }

    public static function register_hooks( $hooks, $type ) {

        // allow filtering the array with registered filters / actions
        if ( $type == 'filter' ) {
            $hooks = apply_filters( 'windzfare_filters', $hooks );
        } else if ( $type == 'action' ) {
            $hooks = apply_filters( 'windzfare_actions', $hooks );
        }

        foreach ( $hooks as $hook_name => $params ) {

            foreach ( $params as $callback => $val ) {

                if ( is_array( $val ) ) {

                    if ( count( $val ) == 2 ) {

                        $priority = $val[0];
                        $args = $val[1];
                    } else if ( count( $val ) == 1 ) {
                        $priority = $val[0];
                        $args = 1;
                    }

                    if ( $type == 'action' ) {
                        add_action( $hook_name, $callback, $priority, $args );
                    } else if ( $type == 'filter' ) {
                        add_filter( $hook_name, $callback, $priority, $args );
                    }
                } else {
                    if ( $type == 'action' ) {
                        add_action( $hook_name, $val );
                    } else if ( $type == 'filter' ) {
                        add_filter( $hook_name, $val );
                    }
                }
            }
        }

        if ( $type == 'filter' ) {
            do_action( 'windzfare_after_filters_setup' );
        } else if ( $type == 'action' ) {
            do_action( 'windzfare_after_actions_setup' );
        }

    }

    public static function logged_in_user_campaign_ids( $user_id = 0 ){
        global $wpdb;
        if ( $user_id == 0 )
            $user_id = get_current_user_id();

        $campaign_ids = $wpdb->get_col( "select ID from { $wpdb->posts } WHERE post_author = { $user_id } AND post_type = 'product'" );
        return $campaign_ids;
    }

    public static function get_author_name(){
        global $post;
        $author = get_user_by( 'id', $post->post_author );

        $author_name = $author->first_name . ' ' . $author->last_name;
        if ( empty( $author->first_name ) )
            $author_name = $author->display_name;

        return $author_name;
    }

    public static function get_author_name_by_login( $author_login ){
        $author = get_user_by( 'login', $author_login );

        $author_name = $author->first_name . ' ' . $author->last_name;
        if ( empty( $author->first_name ) )
            $author_name = $author->user_login;

        return $author_name;
    }

    public static function get_campaigns_location( $campaign_id=null ){
        global $post;
        if ( self::is_campaign( $campaign_id ) ){
            $wp_country = get_post_meta( $post->ID, '_windzfare_country', true );
            $location = get_post_meta( $post->ID, '_windzfare_location', true );
        }else{
            $wp_country = '';
            $location = '';
        }

        if (class_exists( 'WC_Countries' )) {
            $countries_obj = new \WC_Countries();
            $countries = $countries_obj->__get( 'countries' );

            if ( $wp_country ){
                $country_name = $countries[ $wp_country ];
                $location = $location . ', ' . $country_name;
            }
        }
        return $location;
    }

    public static function get_total_fund_raised_by_campaign( $campaign_id = 0 ){
        global $wpdb, $post;
        $db_prefix = $wpdb->prefix;

        if ( $campaign_id == 0 )
            $campaign_id = $post->ID;

        $query = "SELECT
                    SUM(ltoim.meta_value) as total_sales_amount
                FROM
                    { $db_prefix }woocommerce_order_itemmeta woim
                LEFT JOIN
                    { $db_prefix }woocommerce_order_items oi ON woim.order_item_id = oi.order_item_id
                LEFT JOIN
                    { $db_prefix }posts wpposts ON order_id = wpposts.ID
                LEFT JOIN
                    { $db_prefix }woocommerce_order_itemmeta ltoim ON ltoim.order_item_id = oi.order_item_id AND ltoim.meta_key = '_line_total'
                WHERE
                    woim.meta_key = '_product_id' AND woim.meta_value = %d AND wpposts.post_status = 'wc-completed';";

        $wp_sql = $wpdb->get_row( $wpdb->prepare( $query, $campaign_id ) );

        return $wp_sql->total_sales_amount;
    }

    public static function is_reach_target_goal( $campaign_id = null ){
        global $post;

        if ( self::is_campaign( $campaign_id ) ){
            $funding_goal = get_post_meta( $post->ID, '_windzfare_funding_goal', true );
        }else{
            $funding_goal = '0';
        }
        $raised = self::get_total_fund_raised_by_campaign();
        if ( $raised >= $funding_goal ) {
            return true;
        } else {
            return false;
        }
    }

    public static function get_total_goal_by_campaign( $campaign_id ){
        $windzfare_product = wc_get_product( $campaign_id );
        if( isset( $windzfare_product ) ){
            if ( self::is_campaign( $campaign_id ) ){
                $funding_goal = get_post_meta( $campaign_id, '_windzfare_funding_goal', true );
            }else{
                $funding_goal = '0';
            }
            return $funding_goal;
        }
    }
 
    public static function date_remaining( $campaign_id = 0 ){
        global $post;

        if ( $campaign_id == 0 ) $campaign_id = $post->ID;
    
        if ( self::is_campaign( $campaign_id ) ){
            $enddate = get_post_meta( $campaign_id, '_windzfare_duration_end', true );
        }else{
            $enddate = '';
        }
        if ( ( strtotime( $enddate)  + 86399 ) > time() ) {
            $diff = strtotime( $enddate ) - time();
            $temp = $diff / 86400; // 60 sec/min*60 min/hr*24 hr/day=86400 sec/day
            $days = floor( $temp );
            return $days >= 1 ? $days : 1; //Return min one days, though if remain only 1 min
        }
        return 0;
    }

    public static function is_campaign_valid( $campaign_id=null ){
        global $post;
        if ( self::is_campaign( $campaign_id ) ){
            $campaign_end_method = get_post_meta( $post->ID, '_windzfare_campaign_end_method', true );
        }else{
            $campaign_end_method = '';
        }
        switch ( $campaign_end_method ) {

            case 'target_goal':
                if ( self::is_reach_target_goal() ) {
                    return false;
                } else {
                    return true;
                }
                break;

            case 'target_date':
                if ( self::date_remaining() ) {
                    return true;
                } else {
                    return false;
                }
                break;

            case 'target_goal_and_date':
                if ( ! self::is_reach_target_goal() ) {
                    return true;
                }
                if ( self::date_remaining() ) {
                    return true;
                }
                return false;
                break;

            case 'never_end':
                return true;
                break;

            default :
                return false;
        }
    }

    public static function get_fund_raised_percent( $campaign_id = 0 ){

        global $post;
        $percent = 0;
        if ( $campaign_id == 0 ) {
            $campaign_id = $post->ID;
        }
        $total = self::get_total_fund_raised_by_campaign( $campaign_id );

        $goal = self::get_total_goal_by_campaign( $campaign_id );
        if ( $total > 0 && $goal > 0  ) {
            $percent = number_format( $total / $goal * 100, 2, '.', '' );
        }
        if( $percent > 100 ){
            return 100;
        } 
        return ceil( $percent );
    }

    public static function get_fund_raised_percent_format(){
        return ceil( self::get_fund_raised_percent() ) . '%';
    }

    public static function price( $price ){
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            return get_woocommerce_currency_symbol().number_format( 
                $price, 
                wc_get_price_decimals(), 
                wc_get_price_decimal_separator(), 
                wc_get_price_thousand_separator()
            );
        }else{
            return $price;
        }
    }


    public static function get_causes_cats(){
        $cats = array();
        $query_args = array(
            'post_type'     => 'product',
            'meta_query'    => array(
                array(
                    'key'       => '_windzfare',
                    'value'     => 'yes',
                    'compare'   => 'LIKE',
                ),
            ),
            'posts_per_page' => -1,
        );
        $w_cat = new \WP_Query( $query_args );
        while ( $w_cat->have_posts() ) : $w_cat->the_post();
            $id = get_the_ID();
            $categories = get_the_terms( $id, 'product_cat' );
            foreach($categories as $cat ){
                if( ! in_array( $cat->name, $cats ) ){
                    array_push( $cats, $cat->name );
                }
            }
    
        endwhile;
    
        wp_reset_postdata();
    
        return $cats;
    }

    public static function get_causes_list(){

        $user_id = get_current_user_id();
        $query_args = [
            'post_type'   => 'product',
            'author'      => $user_id,
            'meta_query'    => [
                [
                    'key'       => '_windzfare',
                    'value'     => 'yes',
                    'compare'   => 'LIKE',
                ],
            ],
            'posts_per_page' => -1
        ];
        $campaigns = array();
        query_posts( $query_args );
        if ( have_posts() ):
            while ( have_posts() ) : the_post();
                $campaigns[ get_the_ID() ] = get_the_title();
            endwhile;
        endif;

        wp_reset_query();
        return $campaigns;

    }

    public static function sanitize( $value, $senitize_func = 'sanitize_text_field' ){
        $senitize_func = ( in_array( $senitize_func, [
                'sanitize_email', 
                'sanitize_file_name', 
                'sanitize_hex_color', 
                'sanitize_hex_color_no_hash', 
                'sanitize_html_class', 
                'sanitize_key', 
                'sanitize_meta', 
                'sanitize_mime_type',
                'sanitize_sql_orderby',
                'sanitize_option',
                'sanitize_text_field',
                'sanitize_title',
                'sanitize_title_for_query',
                'sanitize_title_with_dashes',
                'sanitize_user',
                'esc_url_raw',
                'wp_filter_nohtml_kses',
            ] ) ) ? $senitize_func : 'sanitize_text_field';
        
        if( ! is_array( $value ) ){
            return $senitize_func( $value );
        }else{
            return array_map( function( $inner_value ) use ( $senitize_func ){
                return self::sanitize( $inner_value, $senitize_func );
            }, $value );
        }
    }

    public static function kses( $raw ){
			
        $allowed_tags = [
            'a'								 => [
                'class'	 => [],
                'href'	 => [],
                'rel'	 => [],
                'title'	 => [],
            ],
            'abbr'							 => [
                'title' => [],
            ],
            'b'								 => [],
            'blockquote'					 => [
                'cite' => [],
            ],
            'cite'							 => [
                'title' => [],
            ],
            'code'							 => [],
            'del'							 => [
                'datetime'	 => [],
                'title'		 => [],
            ],
            'dd'							 => [],
            'div'							 => [
                'class'	 => [],
                'title'	 => [],
                'style'	 => [],
            ],
            'dl'							 => [],
            'dt'							 => [],
            'em'							 => [],
            'h1'							 => [
                'class' => [],
            ],
            'h2'							 => [
                'class' => [],
            ],
            'h3'							 => [
                'class' => [],
            ],
            'h4'							 => [
                'class' => [],
            ],
            'h5'							 => [
                'class' => [],
            ],
            'h6'							 => [
                'class' => [],
            ],
            'i'								 => [
                'class' => [],
            ],
            'img'							 => [
                'alt'	 => [],
                'class'	 => [],
                'height' => [],
                'src'	 => [],
                'width'	 => [],
            ],
            'li'							 => [
                'class' => [],
            ],
            'ol'							 => [
                'class' => [],
            ],
            'p'								 => [
                'class' => [],
            ],
            'q'								 => [
                'cite'	 => [],
                'title'	 => [],
            ],
            'span'							 => [
                'class'	 => [],
                'title'	 => [],
                'style'	 => [],
            ],
            'iframe'						 => [
                'width'			 => [],
                'height'		 => [],
                'scrolling'		 => [],
                'frameborder'	 => [],
                'allow'			 => [],
                'src'			 => [],
            ],
            'strike'						 => [],
            'br'							 => [],
            'strong'						 => [],
            'data-wow-duration'				 => [],
            'data-wow-delay'				 => [],
            'data-wallpaper-options'		 => [],
            'data-stellar-background-ratio'	 => [],
            'ul'							 => [
                'class' => [],
            ],
        ];
        return ( function_exists( 'wp_kses' ) ) ? wp_kses( $raw, $allowed_tags ) : $raw;
    }

}