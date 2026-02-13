<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAPB_Admin_Page {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_admin_menu() {
		add_menu_page(
			'AI Page Builder',
			'AI Page Builder',
			'manage_options',
			'elementor-ai-page-builder',
			array( $this, 'render_admin_page' ),
			'dashicons-superhero',
			60
		);
	}

    public function register_settings() {
        register_setting( 'eapb_settings_group', 'eapb_enable_ai_content' );
        register_setting( 'eapb_settings_group', 'eapb_selected_provider' );
        register_setting( 'eapb_settings_group', 'eapb_openai_api_key' );
        register_setting( 'eapb_settings_group', 'eapb_gemini_api_key' );
        register_setting( 'eapb_settings_group', 'eapb_gemini_model' );
        register_setting( 'eapb_settings_group', 'eapb_request_delay' );
    }

	public function render_admin_page() {
        // Fetch Elementor Templates
        $templates = get_posts( array(
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
        ) );

        $selected_provider = get_option('eapb_selected_provider', 'openai');

        // Fetch Menus
        $menus = wp_get_nav_menus();
        $menu_items_map = [];
        if ( ! empty( $menus ) ) {
            foreach ( $menus as $menu ) {
                $items = wp_get_nav_menu_items( $menu->term_id );
                $menu_items_map[ $menu->term_id ] = [];
                if ( $items ) {
                    foreach ( $items as $item ) {
                        $menu_items_map[ $menu->term_id ][] = [
                            'id' => $item->ID,
                            'title' => $item->title,
                        ];
                    }
                }
            }
        }
		?>
        <script>
            var eapb_menu_items = <?php echo json_encode( $menu_items_map ); ?>;
        </script>
		<div class="wrap">
			<h1>AI Page Builder</h1>
            
            <form method="post" action="options.php" style="margin-bottom: 30px; padding: 20px; background: #fff; border: 1px solid #ccc;">
                <h2>Settings</h2>
                <?php settings_fields( 'eapb_settings_group' ); ?>
                <?php do_settings_sections( 'eapb_settings_group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Enable AI Content Generation</th>
                        <td>
                            <input type="checkbox" name="eapb_enable_ai_content" value="1" <?php checked(1, get_option('eapb_enable_ai_content', 1), true); ?> />
                            <p class="description">If unchecked, pages will be created using the template's original content without AI modification.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">AI Provider</th>
                        <td>
                            <select name="eapb_selected_provider" id="eapb-provider-select">
                                <option value="openai" <?php selected( $selected_provider, 'openai' ); ?>>OpenAI (ChatGPT)</option>
                                <option value="gemini" <?php selected( $selected_provider, 'gemini' ); ?>>Google Gemini</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top" class="provider-field provider-openai">
                        <th scope="row">OpenAI API Key</th>
                        <td><input type="text" name="eapb_openai_api_key" value="<?php echo esc_attr( get_option('eapb_openai_api_key') ); ?>" class="regular-text" /></td>
                    </tr>
                     <tr valign="top" class="provider-field provider-gemini">
                        <th scope="row">Gemini API Key</th>
                        <td><input type="text" name="eapb_gemini_api_key" value="<?php echo esc_attr( get_option('eapb_gemini_api_key') ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top" class="provider-field provider-gemini">
                        <th scope="row">Gemini Model</th>
                        <td>
                            <?php $current_model = get_option('eapb_gemini_model', 'gemini-2.0-flash'); ?>
                            <select name="eapb_gemini_model" style="width: 100%; max-width: 400px;">
                                <optgroup label="Gemini 3 (Preview)">
                                    <option value="gemini-3-pro-preview" <?php selected( $current_model, 'gemini-3-pro-preview' ); ?>>Gemini 3 Pro Preview</option>
                                </optgroup>
                                <optgroup label="Gemini 2.5">
                                    <option value="gemini-2.5-flash" <?php selected( $current_model, 'gemini-2.5-flash' ); ?>>Gemini 2.5 Flash (Balanced)</option>
                                    <option value="gemini-2.5-flash-lite" <?php selected( $current_model, 'gemini-2.5-flash-lite' ); ?>>Gemini 2.5 Flash-Lite (Fastest)</option>
                                    <option value="gemini-2.5-pro" <?php selected( $current_model, 'gemini-2.5-pro' ); ?>>Gemini 2.5 Pro (High Performance)</option>
                                </optgroup>
                                <optgroup label="Gemini 2.0">
                                    <option value="gemini-2.0-flash" <?php selected( $current_model, 'gemini-2.0-flash' ); ?>>Gemini 2.0 Flash</option>
                                    <option value="gemini-2.0-flash-001" <?php selected( $current_model, 'gemini-2.0-flash-001' ); ?>>Gemini 2.0 Flash (Stable 001)</option>
                                    <option value="gemini-2.0-flash-lite" <?php selected( $current_model, 'gemini-2.0-flash-lite' ); ?>>Gemini 2.0 Flash-Lite</option>
                                    <option value="gemini-2.0-flash-lite-001" <?php selected( $current_model, 'gemini-2.0-flash-lite-001' ); ?>>Gemini 2.0 Flash-Lite (Stable 001)</option>
                                </optgroup>
                            </select>
                            <p class="description">Select a Gemini model. Check Google AI Studio for availability.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">API Request Delay (Seconds)</th>
                        <td>
                            <input type="number" name="eapb_request_delay" value="<?php echo esc_attr( get_option('eapb_request_delay', '4') ); ?>" class="small-text" min="0" />
                            <p class="description">Increase this if you get "Quota Exceeded" errors. Gemini Free Tier requires ~4s delay (15 requests/min).</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <script>
                jQuery(document).ready(function($) {
                    function toggleFields() {
                        var provider = $('#eapb-provider-select').val();
                        $('.provider-field').hide();
                        $('.provider-' + provider).show();
                    }
                    $('#eapb-provider-select').on('change', toggleFields);
                    toggleFields(); // Init
                });
            </script>

            <hr>

            <div style="padding: 20px; background: #fff; border: 1px solid #ccc;">
                <h2>Generate New Page</h2>
                <div id="eapb-message"></div>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="eapb_page_titles">Page Titles (One per line)</label></th>
                        <td>
                            <textarea id="eapb_page_titles" class="large-text" rows="10" placeholder="Enter topics or titles (one per line)"></textarea>
                            <p class="description">Each line will generate a separate page.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eapb_template_id">Select Template</label></th>
                        <td>
                            <select id="eapb_template_id">
                                <option value="">Select an Elementor Template</option>
                                <?php foreach ( $templates as $template ) : ?>
                                    <option value="<?php echo esc_attr( $template->ID ); ?>"><?php echo esc_html( $template->post_title ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Add to Menu</th>
                        <td>
                            <label><input type="checkbox" id="eapb_add_to_menu" value="1"> Add generated pages to a menu</label>
                            
                            <div id="eapb-menu-settings" style="display:none; margin-top: 10px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9;">
                                <p>
                                    <label for="eapb_menu_id">Select Menu:</label><br>
                                    <select id="eapb_menu_id" style="width: 100%;">
                                        <option value="">Select a Menu</option>
                                        <?php foreach ( $menus as $menu ) : ?>
                                            <option value="<?php echo esc_attr( $menu->term_id ); ?>"><?php echo esc_html( $menu->name ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                                <p id="eapb-parent-wrapper" style="display:none;">
                                    <label for="eapb_parent_menu_id">Select Parent Item (Optional):</label><br>
                                    <select id="eapb_parent_menu_id" style="width: 100%;">
                                        <option value="0">-- No Parent (Top Level) --</option>
                                    </select>
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button id="eapb-generate-btn" class="button button-primary button-large">Generate Pages</button>
                    <span class="spinner" id="eapb-spinner" style="float:none;"></span>
                </p>
            </div>
		</div>

        <script>
        jQuery(document).ready(function($) {
            
            // Menu Logic
            $('#eapb_add_to_menu').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#eapb-menu-settings').slideDown();
                } else {
                    $('#eapb-menu-settings').slideUp();
                }
            });

            $('#eapb_menu_id').on('change', function() {
                var menuId = $(this).val();
                var $parentSelect = $('#eapb_parent_menu_id');
                var $parentWrapper = $('#eapb-parent-wrapper');
                
                $parentSelect.empty().append('<option value="0">-- No Parent (Top Level) --</option>');

                if (menuId && eapb_menu_items[menuId]) {
                    var items = eapb_menu_items[menuId];
                    if (items.length > 0) {
                        $.each(items, function(index, item) {
                            $parentSelect.append($('<option>', {
                                value: item.id,
                                text: item.title
                            }));
                        });
                        $parentWrapper.show();
                    } else {
                        $parentWrapper.hide();
                    }
                } else {
                    $parentWrapper.hide();
                }
            });

            // Generation Logic
            $('#eapb-generate-btn').on('click', function(e) {
                e.preventDefault();
                
                var titlesText = $('#eapb_page_titles').val();
                var template_id = $('#eapb_template_id').val();
                var add_to_menu = $('#eapb_add_to_menu').is(':checked');
                var menu_id = $('#eapb_menu_id').val();
                var parent_menu_id = $('#eapb_parent_menu_id').val();
                
                if (!titlesText || !template_id) {
                    alert('Please enter at least one title and select a template.');
                    return;
                }

                if (add_to_menu && !menu_id) {
                    alert('Please select a menu to add pages to.');
                    return;
                }

                var titles = titlesText.split(/\r?\n/).filter(function(t) { return t.trim() !== ''; });

                if (titles.length === 0) {
                     alert('Please enter at least one valid title.');
                     return;
                }

                $('#eapb-generate-btn').prop('disabled', true);
                $('#eapb-spinner').addClass('is-active');
                $('#eapb-message').html('<div class="notice notice-info inline"><p>Starting generation for ' + titles.length + ' pages...</p></div>');

                var total = titles.length;
                var current = 0;
                var successCount = 0;

                function processNext() {
                    if (current >= total) {
                        // All done
                        $('#eapb-generate-btn').prop('disabled', false);
                        $('#eapb-spinner').removeClass('is-active');
                        $('#eapb-message').append('<div class="notice notice-success inline"><p>Batch processing complete! ' + successCount + ' pages generated.</p></div>');
                        return;
                    }

                    var title = titles[current].trim();
                    current++;
                    
                    $('#eapb-message').append('<p>Generating page ' + current + ' of ' + total + ': <b>' + title + '</b>...</p>');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'eapb_generate_page',
                            title: title,
                            template_id: template_id,
                            add_to_menu: add_to_menu,
                            menu_id: menu_id,
                            parent_menu_id: parent_menu_id,
                            security: '<?php echo wp_create_nonce("eapb_generate_page_nonce"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                successCount++;
                                $('#eapb-message').append('<p style="color: green;">&#10004; Success: <a href="' + response.data.url + '" target="_blank">View Page</a></p>');
                            } else {
                                $('#eapb-message').append('<p style="color: red;">&#10008; Error: ' + response.data + '</p>');
                            }
                            processNext();
                        },
                        error: function() {
                            $('#eapb-message').append('<p style="color: red;">&#10008; System Error.</p>');
                            processNext();
                        }
                    });
                }

                // Start the loop
                processNext();
            });
        });
        </script>
		<?php
	}
}
