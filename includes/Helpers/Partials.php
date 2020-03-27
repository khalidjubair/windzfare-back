<?php

namespace Windzfare\Helpers; 
use Windzfare\Helpers\Utils as Utils;

class Partials{

    function __construct(){
        add_action ( 'wp_ajax_donation_level', 'output_donation_level' );
        add_action ( 'wp_ajax_nopriv_donation_level', 'output_donation_level' );
    }

    public static function output_donation_level( $campaign_id = null ){

        if(!isset($campaign_id)){
            $campaign_id = Utils::get_option( 'windzfare_featured_campaign', 'windzfare_options');
        }
        $donation_level_fields = get_post_meta($campaign_id, 'repeatable_donation_level_fields', true);
        ob_start();
        
        if ( $donation_level_fields ) : ?>

            <div class="donation_amount_tab">
                <div class="select_currency_box">
                    <div class="currency_dropdown">
                        <?php echo get_woocommerce_currency_symbol(); ?>
                    </div>
                    <div class="currency"><?php esc_html_e('Amount', 'windzfare'); ?></div>
                </div>
                <div class="select_amount_box">
                    <div class="selectdonate"><?php esc_html_e('Select Donation','windzfare');?></div>
                        <?php foreach ( $donation_level_fields as $field ) { ?>
                            <label class="select_amount radio_circle">
                                <input type="radio" name="dvalue" value="<?php echo esc_attr( $field['_donation_level_amount'] ); ?>">
                                <span class="checkmark"></span>
                                <span class="value"><?php echo Utils::price(esc_attr( $field['_donation_level_amount'] )); ?></span>
                            </label>
                        <?php } ?>
                        <label class="select_amount radio_circle custom">
                            <input type="radio" name="dvalue" value="custom">
                            <span class="checkmark"></span>
                            <input type="text" class="custom_field">
                            <span class="value"><?php esc_html_e('Custom','windzfare');?></span>
                        </label>
                </div>
                <div class="windzfare_button_group">
                    <input class="windzfare_button effect_1" type="submit" value="<?php esc_html_e('Donate Now', 'windzfare'); ?>" name="submit">
                </div>
            </div>
        <?php endif; ?>
        <?php
        $html = ob_get_clean();
        return $html;
    }

    public static function output_causes( $atts = [] ){

        $args = shortcode_atts(array(
            'cat'         => '',
            'number'      => -1,
            'col'      => '3',
            'style'      => '1',
            'filter'      => 'no',
            'donation'      => 'no',
            'author'      => 'yes',
            'show'      => '', // successful, expired, valid
        ), $atts );


        $paged = 1;
        if (get_query_var('paged')){
            $paged = absint( get_query_var( 'paged' ) );
        }elseif (get_query_var('page')){
            $paged = absint( get_query_var( 'page' ) );
        }

        
        $categories = Utils::get_causes_cats();
        ob_start();
        
            if ($args['cat']) {
                $cat_array = explode(',', $args['cat']);
                $query_args = array(
                'post_type'     => 'product',
                'tax_query'     => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' =>  $cat_array,
                    )
                ),
                'meta_query'    => array(
                    array(
                        'key'       => '_windzfare',
                        'value'     => 'yes',
                        'compare'   => 'LIKE',
                    ),
                ),
                'posts_per_page' => $args['number'],
                'paged' => $paged
            );
            }else{
                $query_args = array(
                    'post_type'     => 'product',
                    'meta_query'    => array(
                        array(
                            'key'       => '_windzfare',
                            'value'     => 'yes',
                            'compare'   => 'LIKE',
                        ),
                    ),
                    'posts_per_page' => $args['number'],
                    'paged' => $paged
                );
            }


            if (!empty($_GET['author'])) {
                $user_login     = sanitize_text_field( trim( $_GET['author'] ) );
                $user           = get_user_by( 'login', $user_login );
                if ($user) {
                    $user_id    = $user->ID;
                    $query_args = array(
                        'post_type'   => 'product',
                        'author'      => $user_id,
                        'meta_query'    => array(
                            array(
                                'key'       => '_windzfare',
                                'value'     => 'yes',
                                'compare'   => 'LIKE',
                            ),
                        ),
                        'posts_per_page' => $args['number'],
                        'paged' => $paged
                    );
                }
            }

            $c_query = new \WP_Query($query_args);
            if ($c_query->have_posts()): ?>
            <div class="row">
                <?php while ($c_query->have_posts()) : $c_query->the_post();
                
                    if($args['show'] == 'successful'):
                        if(is_reach_target_goal()):
                            self::output_causes_grid_part($args);
                        endif;
                    elseif($args['show'] == 'expired'):
                        if(Utils::date_remaining() == false):
                        self::output_causes_grid_part($args);
                        endif;
                    elseif($args['show'] == 'valid'):
                        if(is_campaign_valid()):
                            self::output_causes_grid_part($args);
                        endif;
                    else:
                        self::output_causes_grid_part($args);
                    endif;
                endwhile; ?>
                </div>
                <?php self::paging_nav(); ?>
            <?php
            else:
                self::output_causes_grid_part($args);
            endif;

        $html = ob_get_clean();
        wp_reset_postdata();
        return $html;
    
    }

    public static function output_causes_grid_part($args){

        $funding_goal   = Utils::get_total_goal_by_campaign(get_the_ID());
        $fund_raised_percent   = Utils::get_fund_raised_percent_format(get_the_ID());
        $image_link = wp_get_attachment_url(get_post_thumbnail_id());
        
        $raised = 0;
        $fund_raised =  Utils::get_total_fund_raised_by_campaign(get_the_ID());
        
        if ($fund_raised){
            $raised = $fund_raised;
        }
        
        $cols = $args['col'];
        $grid = 12/$cols;
        
        ?>
        <div class="col-lg-<?php echo $grid;?> col-md-6">
            <div class="power_causes">
                <div class="power_causes_wrapper">
                    <div class="power_causes_image">
                        <img class="primary_img" src="<?php echo $image_link; ?>" alt="">
                        <?php $categories = get_the_terms( get_the_ID(), 'product_cat' ); ?>
                        <div class="power_highlight_tag"><?php echo $categories[0]->name; ?></div>
                    </div>
                    <div class="power_causes_content">
                        <h4><?php the_title();?></h4>
                        <p><?php the_excerpt();?></p>
                        <div class="power_progress_content">
                            <div class="power_progress_bar_back">
                                <div class="power_progress_bar" style="max-width: 90%;"><span class="power_progress_value"><?php echo $fund_raised_percent; ?></span></div>
                            </div>
                            <div class="power_progress_amount"><span><i class="ion-md-wifi"></i> <b><?php esc_html_e('Goal:','windzfare'); ?></b> <?php echo wc_price($funding_goal); ?></span> <span><i class="ion-logo-usd"></i> <b><?php esc_html_e('Raised:', 'windzfare'); ?></b> <?php echo $raised; ?></span></div>
                        </div>
                    </div>
                    <div class="power_button_group">
                        <a href="<?php the_permalink();?>" class="power_button effect_1">
                            <span class="button_value"><?php esc_html_e('Donate Now','windzfare'); ?></span>
                        </a>
                        <a href="<?php the_permalink();?>" class="power_button effect_3">
                            <span class="button_value"><?php esc_html_e('Learn More','windzfare'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function output_causes_grid_carousel_part(){

        $funding_goal   = Utils::get_total_goal_by_campaign(get_the_ID());
        $fund_raised_percent   = Utils::get_fund_raised_percent_format(get_the_ID());
        $image_link = wp_get_attachment_url(get_post_thumbnail_id());
        
        $raised = 0;
        $fund_raised =  Utils::get_total_fund_raised_by_campaign(get_the_ID());
        
        if ($fund_raised){
            $raised = $fund_raised;
        }
        
        ?>
        <div class="item">
            <div class="power_causes">
                <div class="power_causes_wrapper">
                    <div class="power_causes_image">
                        <img class="primary_img" src="<?php echo $image_link; ?>" alt="">
                        <?php $categories = get_the_terms( get_the_ID(), 'product_cat' ); ?>
                        <div class="power_highlight_tag"><?php echo $categories[0]->name; ?></div>
                    </div>
                    <div class="power_causes_content">
                        <h4><?php the_title();?></h4>
                        <p><?php the_excerpt();?></p>
                        <div class="power_progress_content">
                            <div class="power_progress_bar_back">
                                <div class="power_progress_bar" style="max-width: 90%;"><span class="power_progress_value"><?php echo $fund_raised_percent; ?></span></div>
                            </div>
                            <div class="power_progress_amount"><span><i class="ion-md-wifi"></i> <b><?php esc_html_e('Goal:','windzfare'); ?></b> <?php echo wc_price($funding_goal); ?></span> <span><i class="ion-logo-usd"></i> <b><?php esc_html_e('Raised:', 'windzfare'); ?></b> <?php echo $raised; ?></span></div>
                        </div>
                    </div>
                    <div class="power_button_group">
                        <a href="<?php the_permalink();?>" class="power_button effect_1">
                            <span class="button_value"><?php esc_html_e('Donate Now','windzfare'); ?></span>
                        </a>`
                        <a href="<?php the_permalink();?>" class="power_button effect_3">
                            <span class="button_value"><?php esc_html_e('Learn More','windzfare'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function paging_nav() {

		if ( is_singular() )
			return;

		global $wp_query;

		/** Stop execution if there's only 1 page */
		if ( $wp_query->max_num_pages <= 1 )
			return;

        $paged = 1;
        if ( get_query_var('paged') ) $paged = get_query_var('paged');
        if ( get_query_var('page') ) $paged = get_query_var('page');

		$max	 = intval( $wp_query->max_num_pages );

		/** 	Add current page to the array */
		if ( $paged >= 1 )
			$links[] = $paged;

		/** 	Add the pages around the current page to the array */
		if ( $paged >= 3 ) {
			$links[] = $paged - 1;
			$links[] = $paged - 2;
		}

		if ( ( $paged + 2 ) <= $max ) {
			$links[] = $paged + 2;
			$links[] = $paged + 1;
		}

		echo '<div class="pagination-div"><ul class="pagination">' . "\n";

		/** 	Previous Post Link */
		if ( get_previous_posts_link() )
			printf( '<li>%s</li>' . "\n", get_previous_posts_link( '<i class="ion-ios-arrow-back"></i>' ) );

		/** 	Link to first page, plus ellipses if necessary */
		if ( !in_array( 1, $links ) ) {
			$class = 1 == $paged ? ' class="page-number current"' : 'class="page-number"';

			printf( '<li><a %s href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

			if ( !in_array( 2, $links ) )
				echo '<li>…</li>';
		}

		/** 	Link to current page, plus 2 pages in either direction if necessary */
		sort( $links );
		foreach ( (array) $links as $link ) {
			$class = $paged == $link ? ' class="page-number current"' : ' class="page-number"';
			printf( '<li><a %s href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
		}

		/** 	Link to last page, plus ellipses if necessary */
		if ( !in_array( $max, $links ) ) {
			if ( !in_array( $max - 1, $links ) )
				echo '<li>…</li>' . "\n";

			$class = $paged == $max ? ' class="page-number current"' : 'class="page-number"';
			printf( '<li><a %s href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
		}

		/** 	Next Post Link */
		if ( get_next_posts_link() )
			printf( '<li>%s</li>' . "\n", get_next_posts_link( '<i class="ion-ios-arrow-forward"></i>' ) );

		echo '</ul></div>' . "\n";
	}
}