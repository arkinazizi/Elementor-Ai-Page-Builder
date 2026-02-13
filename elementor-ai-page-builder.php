<?php
/**
 * Plugin Name: Elementor AI Page Builder
 * Description: Generates Elementor pages with AI-written content based on selected templates.
 * Version: 1.4
 * Author: Arkin Azizi
 * Author URI: https://arkin.bio/
 * Text Domain: elementor-ai-page-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'EAPB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EAPB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include classes
require_once EAPB_PLUGIN_DIR . 'includes/interfaces/interface-ai-provider.php';
require_once EAPB_PLUGIN_DIR . 'includes/providers/class-provider-openai.php';
require_once EAPB_PLUGIN_DIR . 'includes/providers/class-provider-gemini.php';
require_once EAPB_PLUGIN_DIR . 'includes/class-admin-page.php';
require_once EAPB_PLUGIN_DIR . 'includes/class-content-generator.php';

class Elementor_AI_Page_Builder {

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function init() {
		// Initialize Admin Page
		new EAPB_Admin_Page();
        
        // Register AJAX handler
        add_action( 'wp_ajax_eapb_generate_page', array( $this, 'handle_generate_page' ) );
	}

    public function handle_generate_page() {
        check_ajax_referer( 'eapb_generate_page_nonce', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
        $template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : 0;

        if ( empty( $title ) || empty( $template_id ) ) {
            wp_send_json_error( 'Missing title or template.' );
        }

        $generator = new EAPB_Content_Generator();
        $result = $generator->create_page( $title, $template_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        } else {
            // Add to Menu Logic
            $add_to_menu = isset( $_POST['add_to_menu'] ) && 'true' === $_POST['add_to_menu'];
            $menu_id = isset( $_POST['menu_id'] ) ? intval( $_POST['menu_id'] ) : 0;
            $parent_menu_id = isset( $_POST['parent_menu_id'] ) ? intval( $_POST['parent_menu_id'] ) : 0;

            if ( $add_to_menu && $menu_id > 0 ) {
                $menu_item_data = array(
                    'menu-item-title'   => $title,
                    'menu-item-object-id' => $result,
                    'menu-item-object' => 'page',
                    'menu-item-status'  => 'publish',
                    'menu-item-type'    => 'post_type',
                    'menu-item-parent-id' => $parent_menu_id,
                );
                wp_update_nav_menu_item( $menu_id, 0, $menu_item_data );
            }

            wp_send_json_success( array( 
                'message' => 'Page generated successfully!', 
                'url' => get_permalink( $result )
            ) );
        }
    }

}

new Elementor_AI_Page_Builder();
