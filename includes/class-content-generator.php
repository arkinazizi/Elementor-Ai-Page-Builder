<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAPB_Content_Generator {
    
    private $provider;
    private $page_title;

    public function __construct() {
        $this->init_provider();
    }

    private function init_provider() {
        $selected = get_option('eapb_selected_provider', 'openai');
        
        if ( 'gemini' === $selected ) {
            $key = get_option('eapb_gemini_api_key');
            $model = get_option('eapb_gemini_model', 'gemini-2.0-flash');
            $this->provider = new EAPB_Provider_Gemini( $key, $model );
        } else {
            $key = get_option('eapb_openai_api_key');
            $this->provider = new EAPB_Provider_OpenAI( $key );
        }
    }

    public function create_page( $title, $template_id ) {
        $this->page_title = $title;

        // 1. Create new page
        $page_id = wp_insert_post( array(
            'post_title'   => $title,
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ) );

        if ( is_wp_error( $page_id ) ) {
            return $page_id;
        }

        // 2. Get Elementor Data from Template
        $template_data = $this->get_elementor_data( $template_id );
        
        if ( empty( $template_data ) ) {
            return new WP_Error( 'empty_template', 'Selected template has no Elementor data.' );
        }

        // 3. Process Data with AI
        $enable_ai = get_option( 'eapb_enable_ai_content', 1 );
        if ( $enable_ai ) {
            $new_data = $this->process_elementor_data( $template_data );
        } else {
            $new_data = $template_data;
        }

        // 4. Save to new page
        update_post_meta( $page_id, '_elementor_data', wp_slash( json_encode( $new_data ) ) );
        update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
        update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );

        return $page_id;
    }

    private function get_elementor_data( $post_id ) {
        $data = get_post_meta( $post_id, '_elementor_data', true );
        if ( empty( $data ) ) {
            return array();
        }
        return json_decode( $data, true );
    }

    private function process_elementor_data( $data ) {
        foreach ( $data as &$element ) {
            $element = $this->process_element( $element );
        }
        return $data;
    }

    private function process_element( $element ) {
        if ( isset( $element['elements'] ) ) {
            foreach ( $element['elements'] as &$child ) {
                $child = $this->process_element( $child );
            }
        }

        if ( isset( $element['widgetType'] ) ) {
            $widget_type = $element['widgetType'];
            $settings = isset( $element['settings'] ) ? $element['settings'] : array();

            if ( 'heading' === $widget_type && ! empty( $settings['title'] ) ) {
                $element['settings']['title'] = $this->generate_ai_content( $settings['title'], 'heading' );
            }
            
            if ( 'text-editor' === $widget_type && ! empty( $settings['editor'] ) ) {
                $element['settings']['editor'] = $this->generate_ai_content( $settings['editor'], 'paragraph' );
            }

             if ( 'button' === $widget_type && ! empty( $settings['text'] ) ) {
                $element['settings']['text'] = $this->generate_ai_content( $settings['text'], 'short_text' );
            }
        }

        return $element;
    }

    private function generate_ai_content( $original_text, $type ) {
        if ( ! $this->provider ) {
            return $original_text . ' (No Provider)';
        }

        $prompt = "Rewrite the following website content for a page titled '{$this->page_title}'. The content type is '{$type}'. Original content: \"{$original_text}\". Keep it concise.";
        
        // Rate Limiting
        $delay = (int) get_option('eapb_request_delay', 4);
        if ( $delay > 0 ) {
            sleep( $delay );
        }

        $result = $this->provider->generate_text( $prompt, $type );

        if ( is_wp_error( $result ) ) {
            return $original_text . ' (AI Error: ' . $result->get_error_message() . ')';
        }

        $upload_dir = wp_upload_dir();
$file_path = $upload_dir['basedir'] . '/result.txt';

file_put_contents($file_path, $result);
        return $result;
    }

}
