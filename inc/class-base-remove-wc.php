<?php
/**
 * Base_Remove_WC class
 * This do filter on 'post-type_link' hook
 * This do action on 'pre_get_posts' and 'admin_init' hook
 * 
 * @package remove-slug-wc
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die();
}

/**
 * Base_Remove_RC class
 */
class Base_Remove_WC {

    /**
     * Instance property
     *
     * @static
     */
    protected static $_instance = null;

    /**
	 * Main WooCommerce Instance.
	 *
	 * Ensures only one instance of product base remove plugin is loaded or can be loaded.
	 *
	 * @access public
     * @static
	 * @return static - Main instance.
	 */
	public static function instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

    /**
	 * Product base remove constructor.
	 */
    public function __construct() {
        $this->init_hooks();
    }

    
    /**
     * These function is run with core hooks
     *
     * @access private
     * @return void
     */
    private function init_hooks() {
        add_action( 'pre_get_posts', array( $this, 'rsw_change_slug_structure' ), 99 );
        add_action( 'admin_init', array( $this, 'rsw_change_pretty_permalink' ) );
        add_action( 'template_redirect', array( $this, 'handle_product_base' ) );

        add_filter( 'post_type_link', array( $this, 'rsw_remove_slug' ), 10, 3 );
    }

    /**
     * Action hook's callback function to change slug structure
     * 
     * @access public
     * @param object|WP_Query $query
     * @return void
     */
    public function rsw_change_slug_structure( $query ) {

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
    
    /**
     * Action hook's callback function to change pretty permalink
     *
     * @access public
     * @return void
     */
    public function rsw_change_pretty_permalink() {
        global $wp_rewrite;
    
        $wp_rewrite->set_permalink_structure( '/%postname%/' );
    
        update_option( 'rewrite_rules', false );
    
        $wp_rewrite->flush_rules( true );
    }

    /**
     * Action hook's callback function to handle product base
     *
     * @access public
     * @return void
     */
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

    /**
     * Filter to remove the product base.
     *
     * @access public
     * @param string      $post_link    Optional. Is it a sample permalink. Default false.
     * @param int|WP_Post $post      Optional. Post ID or post object. Default is the global `$post`.
     * @param bool        $leavename Optional. Whether to keep post name. Default false.
     * @return string       $post_link
     */
    public function rsw_remove_slug( $post_link, $post, $leavename ) {
        
        if ( 'product' != $post->post_type || 'publish' != $post->post_status ) {
            return $post_link;
        }

        $woocommerce_permalink = get_option( 'woocommerce_permalinks' );
        $woocommerce_base_slug = $woocommerce_permalink['product_base'] . '/';
        $post_link = str_replace( $woocommerce_base_slug, '/', $post_link );

        return $post_link;
    }

}

