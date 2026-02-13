<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAPB_Provider_OpenAI implements EAPB_AI_Provider {

    private $api_key;
    private $model;

    public function __construct( $api_key ) {
        $this->api_key = $api_key;
        $this->model = 'gpt-3.5-turbo';
    }

    public function generate_text( $prompt, $context_type ) {
        if ( empty( $this->api_key ) ) {
            return new WP_Error( 'missing_key', 'OpenAI API Key is missing.' );
        }

        $url = 'https://api.openai.com/v1/chat/completions';
        
        $body = array(
            'model' => $this->model,
            'messages' => array(
                array(
                    'role' => 'system', 
                    'content' => 'You are a professional web copywriter. Rewrite the given text to match the new page context. RULES: Output ONLY the replacement text. Do NOT provide options. Do NOT include conversational filler. Maintain the original tone and length.'
                ),
                array(
                    'role' => 'user', 
                    'content' => $prompt
                ),
            )
        );

        $args = array(
            'body'        => json_encode( $body ),
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ),
            'timeout'     => 30,
            'blocking'    => true,
        );

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['error'] ) ) {
             return new WP_Error( 'openai_error', $data['error']['message'] );
        }

        if ( isset( $data['choices'][0]['message']['content'] ) ) {
            return trim( $data['choices'][0]['message']['content'] );
        }

        return $prompt; // Fallback
    }
}
