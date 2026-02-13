<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAPB_Provider_Gemini implements EAPB_AI_Provider {

    private $api_key;
    private $model;

    public function __construct( $api_key, $model = 'gemini-2.0-flash' ) {
        $this->api_key = $api_key;
        $this->model = $model; 
    }

    public function generate_text( $prompt, $context_type ) {
        if ( empty( $this->api_key ) ) {
            return new WP_Error( 'missing_key', 'Gemini API Key is missing.' );
        }

        // Gemini API Endpoint
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent';
        
        // Gemini expects "parts" -> "text" structure
        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array( 'text' => "You are a professional web copywriter. Your task is to rewrite the provided website content to match the new context. 
                        RULES:
                        1. Output ONLY the rewritten content. Do NOT include phrases like 'Here are a few options' or 'Option 1'.
                        2. Do NOT use markdown bolding or headers unless the original had them.
                        3. Keep the length similar to the original.
                        4. Determine the best single version and return ONLY that.
                        
                        Input Text: \n" . $prompt )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
            )
        );

        $args = array(
            'body'        => json_encode( $body ),
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'x-goog-api-key' => $this->api_key,
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
            return new WP_Error( 'gemini_error', $data['error']['message'] );
        }

        // Parse Gemini response
        if ( ! empty( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
            return trim( $data['candidates'][0]['content']['parts'][0]['text'] );
        }
$upload_dir = wp_upload_dir();
$file_path = $upload_dir['basedir'] . '/prompt.txt';

file_put_contents($file_path, $prompt);
        
        return $prompt; // Fallback
    }
}
