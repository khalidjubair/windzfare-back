<?php
namespace Windzfare\Cptui;

if ( ! defined( 'ABSPATH' ) ) exit;

class Cpt {

  private $textdomain;
  private $windzfare_posts;

    public function __construct( $textdomain ){
        $this->textdomain = $textdomain;
        $this->windzfare_posts = [];
        add_action( 'init', [ $this, 'register' ] );
    }

    public function init( $type, $singular_label, $plural_label, $settings = [] ){
        
        $default_settings = [
            'labels' => [
                'name'                => __( $plural_label, $this->textdomain ),
                'singular_name'       => __( $singular_label, $this->textdomain ),
                'add_new_item'        => __( 'Add New '.$singular_label, $this->textdomain ),
                'edit_item'           => __( 'Edit '.$singular_label, $this->textdomain ),
                'new_item'            => __( 'New '.$singular_label, $this->textdomain ),
                'view_item'           => __( 'View '.$singular_label, $this->textdomain ),
                'search_items'        => __( 'Search '.$plural_label, $this->textdomain ),
                'not_found'           => __( 'No '.$plural_label.' found', $this->textdomain ),
                'not_found_in_trash'  => __( 'No '.$plural_label.' found in trash', $this->textdomain ),
                'parent_item_colon'   => __( 'Parent '.$singular_label, $this->textdomain ),
                'menu_name'           => __( $plural_label,$this->textdomain )
            ],
            'public'         => true,
            'has_archive'    => true,
            'menu_icon'      => '',
            'menu_position'  => 20,
            'supports'       => [
                'title',
                'editor',
                'thumbnail'
            ],
            'rewrite' => [
                'slug' => sanitize_title_with_dashes( $plural_label )
            ]
        ];
        $this->windzfare_posts[ $type ] = array_merge( $default_settings, $settings );
    }

    public function register(){
        foreach( $this->windzfare_posts as $key=>$value ) {
            register_post_type( $key, $value );
            flush_rewrite_rules( false );
        }
    }
}