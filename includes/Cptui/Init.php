<?php
namespace Windzfare\Cptui;

if ( !defined( 'ABSPATH' ) )
	die( 'Direct access forbidden.' );

class Init {

	private static $initialized	 = false;
    public function __construct() {
    
		$cpt = new Cpt( 'windzfare' );		
		$cpt_tax = new  CptTax('windzfare');

		
		if ( self::$initialized ) {
			return;
		} else {
			self::$initialized = true;
		}

		$cpt->init( 
			'windzfare_event', 
			'Event', 
			'Events', 
			[ 'menu_icon'	 => 
				'dashicons-exerpt-view',
				'supports'	 => [
					'title', 
					'editor', 
					'excerpt', 
					'thumbnail' 
				],
				'rewrite'	 => [
					'slug' => 'windzfare_event' 
				]
			]
		);
		$cpt_tax->init(
			'windzfare_event_cat', 
			'Event Category', 
			'Event Categories', 
			'windzfare_event',
			'manage_categories'
		);
		
		add_action('single_template', [ __CLASS__, 'set_page_template' ], 10, 3 );
		add_action('init', [ __CLASS__, 'add_elementor_support' ], 10, 3 );
		 
	}
	
	public static function set_page_template( $template ) {
		global $post;
		$template_path = ELEMENTOR_PATH . '/modules/page-templates/templates/canvas.php';
		if ('windzfare_header_footer' === $post->post_type || 
			'windzfare_nested' === $post->post_type || 
			'windzfare_template' === $post->post_type || 
			'windzfare_form' === $post->post_type || 
			'windzfare_block' === $post->post_type 
		){
			if ( file_exists( $template_path ) ) {
				return $template_path;
			} else {
				return ELEMENTOR_PATH . '/includes/page-templates/canvas.php';
			}
		}
		return $template;
	}

	public static function add_elementor_support() {
		$elementor_support = get_option( 'elementor_cpt_support' );
		$default_supports = [ 'windzfare_event' ];
		if( ! $elementor_support ) {
			$elementor_support = $default_supports;
		}else{
			foreach($default_supports as $default_support){
				if( ! in_array( $default_support, $elementor_support ) ) {
					$elementor_support[] = $default_support;
				}
			}
		}
		update_option( 'elementor_cpt_support', $elementor_support );
	}
}