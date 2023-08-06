<?php

/**
 * Slug class
 * This do filter on 'post-type_link' hook
 * This do action on 'pre_get_posts' and 'admin_init' hook
 * 
 * @package remove-slug-wc
 */

class Slug {
    public function __construct() {
        add_action( 'pre_get_posts', [ $this, 'rsw_change_slug_structure' ], 99 );
        add_action( 'admin_init', [ $this, 'rsw_change_pretty_permalink' ] );
        add_action( 'template_redirect', [ $this, 'handle_product_base' ] );

        add_filter( 'post_type_link', [ $this, 'rsw_remove_slug' ], 10, 3 );
        
    }

    function rsw_remove_slug( $post_link, $post, $leavename ) {
        if ( 'product' != $post->post_type || 'publish' != $post->post_status ) {
            return $post_link;
        }
        $woocommerce_base_slug = get_option('woocommerce_permalinks')['product_base'] . '/';
        
        $post_link = str_replace( $woocommerce_base_slug, '/', $post_link );
        return $post_link;
    }
    
    
    function rsw_change_slug_structure( $query ) {
        if ( ! $query->is_main_query() || 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
            return;
        }
        $post_types = get_post_types( array( 'public' => true ) );
        if ( ! empty( $query->query['name'] ) ) {
            $query->set( 'post_type', $post_types );
        } elseif ( ! empty( $query->query['pagename'] ) && false === strpos( $query->query['pagename'], '/' ) ) {
            $query->set( 'post_type', $post_types );
            $query->set( 'name', $query->query['pagename'] );
        }
    }
    
    
    
    function rsw_change_pretty_permalink() {
        global $wp_rewrite; 
    
        $wp_rewrite->set_permalink_structure('/%postname%/'); 
    
        update_option( "rewrite_rules", FALSE ); 
    
        $wp_rewrite->flush_rules( true );
    }

    public function handle_product_base() {
        global $wp_query, $wp;

        $current_url = home_url( add_query_arg( array( $_GET ), $wp->request ) );
        $woocommerce_base_slug = get_option( 'woocommerce_permalinks' )['product_base'] . '/';

        if ( false !== strpos( $current_url, $woocommerce_base_slug ) ) {
            $wp_query->set_404();
            status_header( 404 );
            get_template_part( 404 );
        }
    }
}

