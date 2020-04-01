<?php

namespace Windzfare\Helpers; 
use Windzfare\Helpers\Utils as Utils;

class Shortcodes{

    function __construct(){
        add_shortcode( 'render_funding_goal', [ __CLASS__, 'render_funding_goal'] );
        add_shortcode( 'render_fund_raised', [ __CLASS__, 'render_fund_raised'] );
        add_shortcode( 'render_fund_raised_percentage', [__CLASS__, 'render_fund_raised_percentage'] );
        add_shortcode( 'render_causes_grid', [ __CLASS__, 'render_causes_grid'] );
        add_shortcode( 'render_causes_grid_carousel', [ __CLASS__, 'render_causes_grid_carousel'] );
        add_shortcode( 'render_progress_bar', [ __CLASS__, 'render_progress_bar'] );
        add_shortcode( 'render_progress_circle', [ __CLASS__, 'render_progress_circle'] );
        add_shortcode( 'render_donation_level', [ __CLASS__, 'render_donation_level'] );
    }
 
    public static function render_funding_goal( $atts = [] ){
        $args = shortcode_atts( [
            'campaign_id'         => get_the_ID(),
            'label'      => 'Goal:',
        ], $atts );

        return '<div class="windzfare_funding_goal"><span><b>'. $args['label'] .'</b> '. Utils::price( Utils::get_total_goal_by_campaign( $args['campaign_id'] ) ) .'</span></div>';
    }

    public static function render_fund_raised( $atts = [] ){
        $args = shortcode_atts( [
            'campaign_id'         => get_the_ID(),
            'label'      => 'Fund Raised:',
        ], $atts );

        return '<div class="windzfare_fund_raised"><span><b>'. $args['label'] .'</b> '. Utils::price( Utils::get_total_fund_raised_by_campaign( $args['campaign_id'] ) ) .'</span></div>';

    }

    public static function render_donation_level( $atts = [] ){
        $args = shortcode_atts( [
            'campaign_id'         => get_the_ID(),
        ], $atts );

        return Partials::output_donation_level( $args['campaign_id'] );
    }

    public static function render_fund_raised_percentage(){
        return Utils::get_fund_raised_percent();
    }

    public static function render_causes_grid( $atts = [] ){
        $args = shortcode_atts( [
            'cat'         => '',
            'number'      => -1,
            'col'      => '3',
            'show'      => '', // successful, expired, valid
        ], $atts );

        $paged = 1;
        if (get_query_var('paged')){
            $paged = absint( get_query_var( 'paged' ) );
        }elseif ( get_query_var('page') ){
            $paged = absint( get_query_var( 'page' ) );
        }

        ob_start();
        
            if ( $args['cat'] ) {
                $cat_array = explode(',', $args['cat']);
                $query_args = [
                'post_type'     => 'product',
                'tax_query'     => [
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' =>  $cat_array,
                    ]
                ],
                'meta_query'    => [
                    [
                        'key'       => '_windzfare',
                        'value'     => 'yes',
                        'compare'   => 'LIKE',
                    ],
                ],
                'posts_per_page' => $args['number'],
                'paged' => $paged
            ];
            }else{
                $query_args = [
                    'post_type'     => 'product',
                    'meta_query'    => [
                        [
                            'key'       => '_windzfare',
                            'value'     => 'yes',
                            'compare'   => 'LIKE',
                        ],
                    ],
                    'posts_per_page' => $args['number'],
                    'paged' => $paged
                ];
            }


            if ( ! empty($_GET['author'] ) ) {
                $user_login     = sanitize_text_field( trim( $_GET['author'] ) );
                $user           = get_user_by( 'login', $user_login );
                if ( $user ) {
                    $user_id    = $user->ID;
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
                        'posts_per_page' => $args['number'],
                        'paged' => $paged
                    ];
                }
            }
 
            $c_query = new \WP_Query( $query_args );
            if ( $c_query->have_posts() ): ?>
            <div class="windzfare-wrapper">
                <div class="row">
                    <?php while ( $c_query->have_posts() ) : $c_query->the_post();
                        if ( $args['show'] == 'successful' ):
                            if ( Utils::is_reach_target_goal() ):
                                Partials::output_causes_grid_part( $args );
                            endif;
                        elseif ( $args['show'] == 'expired' ):
                            if ( Utils::date_remaining() == false ):
                                Partials::output_causes_grid_part( $args );
                            endif;
                        elseif ( $args['show'] == 'valid' ):
                            if ( Utils::is_campaign_valid() ):
                                Partials::output_causes_grid_part( $args );
                            endif;
                        else:
                            Partials::output_causes_grid_part( $args );
                        endif;
                    endwhile; ?>
                    </div>
                <?php
                else:
                    Partials::output_causes_grid_part( $args );
                endif;
            ?></div><?php
        $html = ob_get_clean();
        wp_reset_postdata();
        return $html;
    }

    public static function render_causes_grid_carousel( $atts = [] ){
        $args = shortcode_atts( [
            'cat'         => '',
            'number'      => -1,
            'data_owl_carousel'      => '',
            'col'      => '3',
            'show'      => '', // successful, expired, valid


            //Owl Carousel Dat
            'items'         => 3,
            'margin'         => 30,
            'padding'         => 20,
            'autoplay'         => true,
            'responsiveClass'         => true,
            'dots'         => true,
            'nav'         => true,
            'responsive_0'         => 1,
            'responsive_540'         => 2,
            'responsive_860'         => 2,
            'responsive_1000'         => 3,
        ], $atts );

        $paged = 1;
        if ( get_query_var('paged')){
            $paged = absint( get_query_var( 'paged' ) );
        }elseif (get_query_var('page')){
            $paged = absint( get_query_var( 'page' ) );
        }

        ob_start();
        
            if ( $args['cat'] ) {
                $cat_array = explode(',', $args['cat']);
                $query_args = [
                'post_type'     => 'product',
                'tax_query'     => [
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' =>  $cat_array,
                    ]
                ],
                'meta_query'    => [
                    [
                        'key'       => '_windzfare',
                        'value'     => 'yes',
                        'compare'   => 'LIKE',
                    ],
                ],
                'posts_per_page' => $args['number'],
                'paged' => $paged
            ];
            }else{
                $query_args = [
                    'post_type'     => 'product',
                    'meta_query'    => [
                        [
                            'key'       => '_windzfare',
                            'value'     => 'yes',
                            'compare'   => 'LIKE',
                        ],
                    ],
                    'posts_per_page' => $args['number'],
                    'paged' => $paged
                ];
            }


            if ( ! empty( $_GET['author'] ) ) {
                $user_login     = sanitize_text_field( trim( $_GET['author'] ) );
                $user           = get_user_by( 'login', $user_login );
                if ( $user ) {
                    $user_id    = $user->ID;
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
                        'posts_per_page' => $args['number'],
                        'paged' => $paged
                    ];
                }
            }

            $c_query = new \WP_Query( $query_args );
            if ( $c_query->have_posts() ): ?>
            <div class="windzfare-wrapper">
                <div class="owl-carousel owl-theme windzfare_causes_carousel side_nav" 
                data-owl-carousel='{
							"items": <?php echo $args['items']?>,
							"margin": <?php echo $args['margin']?>,
							"padding": <?php echo $args['padding']?>,
							"autoplay": <?php echo $args['autoplay']?>,
							"responsiveClass": <?php echo $args['responsiveClass']; ?>,
							"dots": <?php echo $args['dots']?>,
							"nav": <?php echo $args['nav']?>,
							"responsive":{ "0" :{ "items": <?php echo $args['responsive_0']?>},
							"540" :{ "items": <?php echo $args['responsive_540']?> },
							"860" :{ "items" : <?php echo $args['responsive_860']?> } ,
							"1000":{ "items" : <?php echo $args['responsive_1000']?> }}}'>

                    <?php while ( $c_query->have_posts() ) : $c_query->the_post();
                        if ( $args['show'] == 'successful' ):
                            if ( Utils::is_reach_target_goal() ):
                                Partials::output_causes_grid_carousel_part( $args );
                            endif;
                        elseif ( $args['show'] == 'expired' ):
                            if ( Utils::date_remaining() == false ):
                                Partials::output_causes_grid_carousel_part( $args );
                            endif;
                        elseif ( $args['show'] == 'valid' ):
                            if ( Utils::is_campaign_valid() ):
                                Partials::output_causes_grid_carousel_part( $args );
                            endif;
                        else:
                            Partials::output_causes_grid_carousel_part( $args );
                        endif;
                    endwhile; ?>
                    </div>
                <?php
                else:
                    Partials::output_causes_grid_carousel_part();
                endif;
            ?></div><?php
        $html = ob_get_clean();
        wp_reset_postdata();
        return $html;
    }

    public static function render_progress_bar(){
        return '<div class="windzfare-wrapper">
                    <div class="windzfare_progress_content">
                        <div class="windzfare_progress_inner">
                            <div class="windzfare_progress_bar_back">
                                <div class="windzfare_progress_bar" style="max-width: '. Utils::get_fund_raised_percent() .'%;"><span class="windzfare_progress_value">'. Utils::get_fund_raised_percent() .'</span></div>
                            </div>
                        </div>
                    </div>
                </div>';
    }

    public static function render_progress_circle(){
        return '<div class="windzfare-wrapper">
                    <div class="windzfare_progress_inner">
                        <div class="windzfare_progress_bar_back">
                            <span class="windzfare_progress_left">
                                <span class="windzfare_progress_bar"></span>
                            </span>
                            <span class="windzfare_progress_right">
                                <span class="windzfare_progress_bar"></span>
                            </span>
                            <div class="windzfare_progress_value">'. Utils::get_fund_raised_percent() .'</div>
                        </div>
                    </div>
                </div>';
    }

}