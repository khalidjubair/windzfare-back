<?php

namespace Windzfare\Admin\Product;
use Windzfare\Helpers\Utils as Utils;


if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Init')) {

    class Init{

        public static $_instance;

        public function __construct(){
            $this->wp_plugin_init();
        }

        /**
         * Plugin Initialization
         * @since 1.0
         *
         */
        public static function instance() {

            static $instance = false;
			if ( ! $instance ) {
				$instance = new self();
			}
            return $instance;
            
		}

        public function wp_plugin_init(){
            add_action( 'admin_footer', [ &$this, 'custom_js' ] );

            add_filter( 'product_type_options', [ &$this, 'add_product_option' ] );
            add_action( 'woocommerce_process_product_meta_simple', [ &$this, 'save_windzfare_option_field'  ]  );

            // add_filter( 'product_type_selector', [ &$this, 'add_product' ]);
            add_filter( 'woocommerce_product_data_tabs', [ &$this, 'product_tabs' ] );

            add_action( 'woocommerce_product_data_panels', [ &$this, 'product_tab_content' ] );
            add_action( 'woocommerce_product_data_panels', [ &$this, 'product_donation_level_tab_content' ] );
            
            add_action( 'woocommerce_process_product_meta_simple', [ &$this, 'save_option_field'  ] );
            add_action( 'woocommerce_process_product_meta_simple', [ &$this, 'save_donation_level_option_field'  ] );
            
            add_filter( 'woocommerce_product_data_tabs', [ &$this, 'hide_tab_panel' ] );

        }
        
        public function add_product_option( $product_type_options ) {
            $product_type_options['windzfare'] = [
                'id'            => '_windzfare',
                'wrapper_class' => 'show_if_simple',
                'label'         => __( 'Windzfare', 'woocommerce' ),
                'description'   => __( '', 'woocommerce' ),
                'default'       => 'no'
            ];
        
            return $product_type_options;
        }
        
        public function save_windzfare_option_field( $post_id ) {
            $is_e_visa = isset( $_POST['_windzfare'] ) ? 'yes' : 'no';
            update_post_meta( $post_id, '_windzfare', $is_e_visa );
        }

        /**
         * Add to product type drop down.
         */
        public function add_product( $types ){

            // Key should be exactly the same as in the class
            $types[ '_windzfare' ] = esc_html__( 'Windzfare' );

            return $types;

        }

        /**
         * Show pricing fields for simple_rental product.
         */
        public function custom_js() {

            if ( 'product' != get_post_type() ) :
                return;
            endif;

            ?><script type='text/javascript'>
                jQuery( document ).ready( function() {
                    jQuery( '.options_group.pricing' ).addClass( 'show_if_windzfare' ).show();
                });

            </script><?php

        }

        /**
         * Add a custom product tab.
         */
        function product_tabs( $original_prodata_tabs) {

            $welfare_tab = [
                'welfare' => [ 
                    'label' => esc_html__( 'Welfare', 'windzfare' ), 
                    'target' => 'windzfare_options', 
                    'class' => [
                         'show_if_simple' 
                    ], 
                ],
                'donation_level' => [ 
                    'label' => esc_html__( 'Donation Level', 'windzfare' ), 
                    'target' => 'windzfare_donation_level_options', 
                    'class' => [ 
                        'show_if_simple' 
                    ],
                ],
            ];
            $insert_at_position = 2; // Change this for desire position
            $tabs = array_slice( $original_prodata_tabs, 0, $insert_at_position, true ); // First part of original tabs
            $tabs = array_merge( $tabs, $welfare_tab ); // Add new
            $tabs = array_merge( $tabs, array_slice( $original_prodata_tabs, $insert_at_position, null, true ) ); // Glue the second part of original
            return $tabs;
        }
        /**
         * Hide Attributes data panel.
         */
        public function hide_tab_panel( $tabs) {
            $tabs['welfare']['class'] = [ 'hide_if_external', 'hide_if_grouped', 'show_if_simple', 'hide_if_variable' ];
            return $tabs;
        }

        /**
         * Contents of the windzfare options product tab.
         */
        public function product_tab_content() {

            global $post;

            ?><div id='windzfare_options' class='panel woocommerce_options_panel'><?php

            ?><div class='options_group'><?php


            woocommerce_wp_text_input(
                [
                    'id'            => '_windzfare_funding_goal',
                    'label'         => esc_html__( 'Funding Goal ( ' . get_woocommerce_currency_symbol() . ')', 'windzfare' ),
                    'placeholder'   => esc_attr__( 'Funding goal','windzfare' ),
                    'description'   => esc_html__('Enter the funding goal', 'windzfare' ),
                    'desc_tip'      => true,
                    'type' 			=> 'text',
                ]
            );
            woocommerce_wp_text_input(
                [
                    'id'            => '_windzfare_duration_start',
                    'label'         => esc_html__( 'Start date- mm/dd/yyyy or dd-mm-yyyy', 'windzfare' ),
                    'placeholder'   => esc_attr__( 'Start time of this campaign', 'windzfare' ),
                    'description'   => esc_html__( 'Enter start of this campaign', 'windzfare' ),
                    'desc_tip'      => true,
                    'type' 			=> 'text',
                ]
            ); 
            
            woocommerce_wp_text_input(
                [
                    'id'            => '_windzfare_duration_end',
                    'label'         => esc_html__( 'End date- mm/dd/yyyy or dd-mm-yyyy', 'windzfare' ),
                    'placeholder'   => esc_attr__( 'End time of this campaign', 'windzfare' ),
                    'description'   => esc_html__( 'Enter end time of this campaign', 'windzfare' ),
                    'desc_tip'      => true,
                    'type' 			=> 'text',
                ]
            );

            woocommerce_wp_text_input(
                [
                    'id'            => '_windzfare_funding_video',
                    'label'         => esc_html__( 'Video Url', 'windzfare' ),
                    'placeholder'   => esc_attr__( 'Video url', 'windzfare' ),
                    'desc_tip'      => true,
                    'description'   => esc_html__( 'Enter a video url to show your video in campaign details page', 'windzfare' )
                ]
            );

            echo '<div class="options_group"></div>';
 
            $options = [];

            $options['target_goal'] = esc_html__( 'Target Goal', 'windzfare' );
            $options['target_date'] = esc_html__( 'Target Date', 'windzfare' );
            $options['target_goal_and_date'] = esc_html__( 'Target Goal & Date', 'windzfare' );
            $options['never_end'] = esc_html__( 'Campaign Never Ends', 'windzfare' );
            
            //Campaign end method
            woocommerce_wp_select(
                [
                    'id' => '_windzfare_campaign_end_method',
                    'label' => esc_html__( 'Campaign End Method', 'windzfare' ),
                    'placeholder' => esc_attr__( 'Country', 'windzfare' ),
                    'class' => 'select2 _windzfare_campaign_end_method',
                    'options' => $options
                ]
            );

            echo '<div class="options_group"></div>';


            //Get country select
            $countries_obj      = new \WC_Countries();
            $countries          = $countries_obj->__get('countries');
            array_unshift( $countries, 'Select a country' );

            //Country list
            woocommerce_wp_select(
                [
                    'id'            => '_windzfare_country',
                    'label'         => esc_html__( 'Country', 'windzfare' ),
                    'placeholder'   => esc_attr__( 'Country', 'windzfare' ),
                    'class'         => 'select2 _windzfare_country',
                    'options'       => $countries
                ]
            );

            // Location of this campaign
            woocommerce_wp_text_input(
                [
                    'id'            => '_windzfare_location',
                    'label'         => esc_html__( 'Location', 'windzfare' ),
                    'placeholder'   => esc_attr__( 'Location', 'windzfare' ),
                    'description'   => esc_html__( 'Location of this campaign','windzfare' ),
                    'desc_tip'      => true,
                    'type'          => 'text'
                ]
            );
            woocommerce_wp_text_input(
                [
                    'id'            => '_windzfare_primary_color',
                    'label'         => esc_html__( 'Primary Color', 'windzfare' ),
                    'placeholder'   => esc_attr__( '#ffffff','windzfare' ),
                    'description'   => esc_html__( 'Enter the color code', 'windzfare' ),
                    'desc_tip'      => true,
                    'type' 			=> 'text',
                ]
            );
            do_action( 'new_welfare_campaign_option' );

            echo '</div>';

            ?></div><?php


        }

        /**
         * Save the custom fields.
         */
        public function save_option_field( $post_id ) {

            if ( isset( $_POST['_windzfare_funding_goal'] ) ) :
                update_post_meta( $post_id, '_windzfare_funding_goal', sanitize_text_field( $_POST['_windzfare_funding_goal'] ) );
            endif;

            if ( isset( $_POST['_windzfare_duration_start'] ) ) :
                update_post_meta( $post_id, '_windzfare_duration_start', sanitize_text_field( $_POST['_windzfare_duration_start'] ) );
            endif;

            if ( isset( $_POST['_windzfare_duration_end'] ) ) :
                update_post_meta( $post_id, '_windzfare_duration_end', sanitize_text_field( $_POST['_windzfare_duration_end'] ) );
            endif;

            if ( isset( $_POST['_windzfare_funding_video'] ) ) :
                update_post_meta( $post_id, '_windzfare_funding_video', sanitize_text_field( $_POST['_windzfare_funding_video'] ) );
            endif;

            if ( isset( $_POST['_windzfare_campaign_end_method'] ) ) :
                update_post_meta( $post_id, '_windzfare_campaign_end_method', sanitize_text_field( $_POST['_windzfare_campaign_end_method'] ) );
            endif;

            if ( isset( $_POST['_windzfare_country'] ) ) :
                update_post_meta( $post_id, '_windzfare_country', sanitize_text_field( $_POST['_windzfare_country'] ) );
            endif;

            if ( isset( $_POST['_windzfare_location'] ) ) :
                update_post_meta( $post_id, '_windzfare_location', sanitize_text_field( $_POST['_windzfare_location'] ) );
            endif;

            if ( isset( $_POST['_windzfare_primary_color'] ) ) :
                update_post_meta( $post_id, '_windzfare_primary_color', sanitize_text_field( $_POST['_windzfare_primary_color'] ) );
            endif;

            update_post_meta( $post_id, '_sale_price', '0' );
            update_post_meta( $post_id, '_price', '0' );
        }


        
        /**
         * Contents of the windzwp_trust options product donation_level tab.
         */
        public function product_donation_level_tab_content() {

            ?><div id='windzfare_donation_level_options' class='panel woocommerce_options_panel'><?php

            global $post;

            $donation_level_fields = get_post_meta( $post->ID, 'repeatable_donation_level_fields', true );
            
            ?>
            <script type="text/javascript">
                jQuery( document ).ready( function( $ ){
                    $( '#add-donation-level-row' ).on( 'click', function() {
                        var row = $( '.empty-donation-level-row.screen-reader-text' ).clone( true );
                        row.removeClass( 'empty-donation-level-row screen-reader-text' );
                        row.insertBefore( '#windzfare-repeatable-donation-fieldset > div.donation_level-item:last' );
                        return false;
                    });

                    $( '.remove-donation-level-row' ).on( 'click', function() {
                        $( this ).parents('.donation_level-item').remove();
                        return false;
                    });
                });
            </script>

            <div id="windzfare-repeatable-donation-fieldset">
                <?php

                if ( $donation_level_fields ) :

                    foreach ( $donation_level_fields as $field ) { ?>

                        <div class="options_group donation_level-item">
                            <p class="form-field _windzfare_donation_level_amount_field ">
                                <label for="_windzfare_donation_level_amount"><?php esc_html_e( 'Amount','windzfare' );?></label>
                                <input type="text" class="short" name="_windzfare_donation_level_amount[]" value="<?php if( isset( $field['_windzfare_donation_level_amount'] ) && $field['_windzfare_donation_level_amount'] != '') echo sanitize_text_field( $field['_windzfare_donation_level_amount'] ); ?>" />
                            </p>
                            <p class="form-field _windzfare_donation_level_title_field ">
                                <label for="_windzfare_donation_level_title"><?php esc_html_e( 'Title','windzfare' );?></label>
                                <input type="text" class="short" name="_windzfare_donation_level_title[]" value="<?php if( isset( $field['_windzfare_donation_level_title'] ) && $field['_windzfare_donation_level_title'] != '') echo sanitize_text_field( $field['_windzfare_donation_level_title'] ); ?>" />
                            </p>
                            <p class="form-field "><a class="button remove-donation-level-row" href="#"><?php esc_html_e( 'Remove','windzfare' );?></a></p>

                        </div><?php
                    }

                else:
                ?><div class="options_group donation_level-item"><?php
                    ?>
                    <p class="form-field _windzfare_donation_level_amount_field ">
                        <label for="_windzfare_donation_level_amount"><?php esc_html_e( 'Amount','windzfare' );?></label>
                        <input type="text" class="short" name="_windzfare_donation_level_amount[]" />
                    </p>
                    <p class="form-field _windzfare_donation_level_title_field ">
                        <label for="_windzfare_donation_level_title"><?php esc_html_e( 'Title','windzfare' );?></label>
                        <input type="text" class="short" name="_windzfare_donation_level_title[]" />
                    </p>
                    <p class="form-field "><a class="button remove-donation-level-row" href="#"><?php esc_html_e( 'Remove','windzfare' );?></a></p>

                    </div><?php
                endif; ?>

                <div class="options_group donation_level-item empty-donation-level-row screen-reader-text">
                    <p class="form-field _windzfare_donation_level_amount_field ">
                        <label for="_windzfare_donation_level_amount"><?php esc_html_e( 'Amount','windzfare' );?></label>
                        <input type="text" class="short" name="_windzfare_donation_level_amount[]" />
                    </p>
                    <p class="form-field _windzfare_donation_level_title_field ">
                        <label for="_windzfare_donation_level_title"><?php esc_html_e( 'Title','windzfare' );?></label>
                        <input type="text" class="short" name="_windzfare_donation_level_title[]" />
                    </p>
                    <p class="form-field "><a class="button remove-donation-level-row" href="#"><?php esc_html_e( 'Remove','windzfare' );?></a></p>

                </div>
            </div>

            <p><a id="add-donation-level-row" class="button" href="#"><?php esc_html_e( 'Add another','windzfare' );?></a></p>

            <?php

            ?></div><?php


        }

        /**
         * Save the custom fields.
         */
        public function save_donation_level_option_field( $post_id ) {

            $old = get_post_meta( $post_id, 'repeatable_donation_level_fields', true );
            $new = array();

            $names = $_POST['_windzfare_donation_level_amount'];
            $title = $_POST['_windzfare_donation_level_title'];

            $count = count( $names );

            for ( $i = 0; $i < $count; $i++ ) {
                if ( $names[ $i ] != '' ) :
                    $new[ $i ]['_windzfare_donation_level_amount'] = stripslashes( strip_tags( $names[ $i ] ) );
                    $new[ $i ]['_windzfare_donation_level_title'] = stripslashes( strip_tags( $title[ $i ] ) );
                endif;
            }
            
            if ( ! empty( $new ) && $new != $old )
                update_post_meta( $post_id, 'repeatable_donation_level_fields', $new );
            elseif ( empty( $new ) && $old )
                delete_post_meta( $post_id, 'repeatable_donation_level_fields', $old );

        }

    }

}