<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface EAPB_AI_Provider {
    /**
     * Generate text content using the AI provider.
     *
     * @param string $prompt The prompt to send to the AI.
     * @param string $context_type The context (e.g., 'heading', 'paragraph').
     * @return string|WP_Error The generated text or error.
     */
    public function generate_text( $prompt, $context_type );
}
