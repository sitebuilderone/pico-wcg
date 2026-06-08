<?php
/**
 * Product custom fields (ACF replacement) for the child theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return product field definitions in one place.
 *
 * @return array<string,array<string,string>>
 */
function wcg_get_product_field_definitions() {
    return array(
        'brochure'       => array(
            'label' => 'Brochure',
            'type'  => 'media',
        ),
        'technical-data' => array(
            'label' => 'Technical Data Sheet',
            'type'  => 'media',
        ),
        'service-manual' => array(
            'label' => 'Service Manual',
            'type'  => 'media',
        ),
        'features'       => array(
            'label' => 'Features',
            'type'  => 'textarea',
        ),
        'technical'      => array(
            'label' => 'Technical',
            'type'  => 'textarea',
        ),
        'source_info'    => array(
            'label' => 'Source Info',
            'type'  => 'url',
        ),
        'banner-image'   => array(
            'label' => 'Banner Image',
            'type'  => 'media',
        ),
    );
}

/**
 * Accept either an attachment ID or a URL for media-backed fields.
 *
 * @param mixed $value Raw submitted value.
 *
 * @return string
 */
function wcg_sanitize_media_or_url( $value ) {
    $value = trim( (string) $value );

    if ( $value === '' ) {
        return '';
    }

    if ( ctype_digit( $value ) ) {
        $attachment_id = (int) $value;

        return ( $attachment_id > 0 ) ? (string) $attachment_id : '';
    }

    return esc_url_raw( $value );
}

/**
 * Resolve a stored media value (attachment ID or URL) to a usable URL.
 *
 * @param string $value Stored meta value.
 *
 * @return string
 */
function wcg_resolve_media_value_to_url( $value ) {
    $value = trim( (string) $value );

    if ( $value === '' ) {
        return '';
    }

    if ( ctype_digit( $value ) ) {
        $url = wp_get_attachment_url( (int) $value );
        return $url ? (string) $url : '';
    }

    return esc_url_raw( $value );
}

/**
 * Get a human-readable label for a stored media value.
 *
 * @param string $value Stored meta value.
 *
 * @return string
 */
function wcg_get_media_value_label( $value ) {
    $value = trim( (string) $value );

    if ( $value === '' ) {
        return '';
    }

    if ( ctype_digit( $value ) ) {
        $attachment_id = (int) $value;
        $file_path     = get_attached_file( $attachment_id );

        if ( $file_path ) {
            return wp_basename( $file_path );
        }

        $title = get_the_title( $attachment_id );
        return $title ? (string) $title : 'Attachment #' . $attachment_id;
    }

    $path = wp_parse_url( $value, PHP_URL_PATH );
    if ( is_string( $path ) && $path !== '' ) {
        return wp_basename( $path );
    }

    return $value;
}

/**
 * Register product meta so the values are available in REST and editor contexts.
 */
function wcg_register_product_meta() {
    $field_defs = wcg_get_product_field_definitions();

    foreach ( $field_defs as $meta_key => $field ) {
        $sanitize = 'wp_kses_post';

        if ( $field['type'] === 'url' ) {
            $sanitize = 'esc_url_raw';
        } elseif ( $field['type'] === 'media' ) {
            $sanitize = 'wcg_sanitize_media_or_url';
        }

        register_post_meta(
            'product',
            $meta_key,
            array(
                'single'            => true,
                'type'              => 'string',
                'show_in_rest'      => true,
                'sanitize_callback' => $sanitize,
                'auth_callback'     => function() {
                    return current_user_can( 'edit_posts' );
                },
            )
        );
    }
}
add_action( 'init', 'wcg_register_product_meta' );

/**
 * Add a classic metabox to manage product fields without ACF.
 */
function wcg_add_product_fields_metabox() {
    add_meta_box(
        'wcg_product_fields',
        'Product Resources & Notes',
        'wcg_render_product_fields_metabox',
        'product',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'wcg_add_product_fields_metabox' );

/**
 * Load media scripts only on product edit screens where this metabox appears.
 *
 * @param string $hook_suffix Current admin page.
 */
function wcg_enqueue_product_fields_admin_assets( $hook_suffix ) {
    if ( $hook_suffix !== 'post.php' && $hook_suffix !== 'post-new.php' ) {
        return;
    }

    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'product' ) {
        return;
    }

    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'wcg_enqueue_product_fields_admin_assets' );

/**
 * Render product fields metabox UI.
 *
 * @param WP_Post $post Current post.
 */
function wcg_render_product_fields_metabox( $post ) {
    wp_nonce_field( 'wcg_save_product_fields', 'wcg_product_fields_nonce' );

    $field_defs = wcg_get_product_field_definitions();

    echo '<table class="form-table" role="presentation">';

    foreach ( $field_defs as $meta_key => $field ) {
        $value = (string) get_post_meta( $post->ID, $meta_key, true );

        echo '<tr>';
        echo '<th scope="row"><label for="wcg-' . esc_attr( $meta_key ) . '">' . esc_html( $field['label'] ) . '</label></th>';
        echo '<td>';

        if ( $field['type'] === 'textarea' ) {
            printf(
                '<textarea id="wcg-%1$s" name="wcg_product_fields[%1$s]" rows="6" class="large-text">%2$s</textarea>',
                esc_attr( $meta_key ),
                esc_textarea( $value )
            );
        } elseif ( $field['type'] === 'media' ) {
            $value_url   = wcg_resolve_media_value_to_url( $value );
            $value_label = wcg_get_media_value_label( $value );

            printf(
                '<input id="wcg-%1$s" name="wcg_product_fields[%1$s]" type="hidden" class="wcg-media-value" value="%2$s" />',
                esc_attr( $meta_key ),
                esc_attr( $value )
            );
            printf(
                '<input id="wcg-%1$s-display" type="text" class="regular-text wcg-media-display" value="%2$s" placeholder="No file selected" readonly />',
                esc_attr( $meta_key ),
                esc_attr( $value_label )
            );
            printf(
                ' <button type="button" class="button wcg-media-select" data-value-target="#wcg-%1$s" data-display-target="#wcg-%1$s-display" data-preview-target="#wcg-%1$s-preview">%2$s</button>',
                esc_attr( $meta_key ),
                esc_html__( 'Select from Media Library', 'picostrap' )
            );
            printf(
                ' <button type="button" class="button-link-delete wcg-media-clear" data-value-target="#wcg-%1$s" data-display-target="#wcg-%1$s-display" data-preview-target="#wcg-%1$s-preview">%2$s</button>',
                esc_attr( $meta_key ),
                esc_html__( 'Clear', 'picostrap' )
            );
            echo '<p class="description">Select a media file. Existing IDs/URLs are still supported in stored data.</p>';

            if ( $value_url !== '' ) {
                printf(
                    '<p class="description" id="wcg-%1$s-preview"><a href="%2$s" target="_blank" rel="noopener">%3$s</a></p>',
                    esc_attr( $meta_key ),
                    esc_url( $value_url ),
                    esc_html__( 'Open selected file', 'picostrap' )
                );
            } else {
                printf(
                    '<p class="description" id="wcg-%1$s-preview"></p>',
                    esc_attr( $meta_key )
                );
            }
        } else {
            printf(
                '<input id="wcg-%1$s" name="wcg_product_fields[%1$s]" type="url" class="regular-text" value="%2$s" />',
                esc_attr( $meta_key ),
                esc_attr( $value )
            );
        }

        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';

    ?>
    <script>
    (function($){
        'use strict';

        var mediaFrame = null;

        $(document).on('click', '.wcg-media-select', function(e) {
            e.preventDefault();

            var valueTargetSelector = $(this).data('value-target');
            var displayTargetSelector = $(this).data('display-target');
            var previewTargetSelector = $(this).data('preview-target');
            var $valueTarget = $(valueTargetSelector);
            var $displayTarget = $(displayTargetSelector);
            var $previewTarget = $(previewTargetSelector);

            if (!$valueTarget.length || !$displayTarget.length || typeof wp === 'undefined' || !wp.media) {
                return;
            }

            mediaFrame = wp.media({
                title: 'Select file',
                button: {
                    text: 'Use this file'
                },
                multiple: false
            });

            mediaFrame.on('select', function() {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                if (!attachment || !attachment.id) {
                    return;
                }

                var label = attachment.filename || attachment.title || ('Attachment #' + attachment.id);

                $valueTarget.val(String(attachment.id)).trigger('change');
                $displayTarget.val(label).trigger('change');

                if ($previewTarget.length) {
                    if (attachment.url) {
                        $previewTarget.html('<a href="' + attachment.url + '" target="_blank" rel="noopener">Open selected file</a>');
                    } else {
                        $previewTarget.empty();
                    }
                }
            });

            mediaFrame.open();
        });

        $(document).on('click', '.wcg-media-clear', function(e) {
            e.preventDefault();

            var valueTargetSelector = $(this).data('value-target');
            var displayTargetSelector = $(this).data('display-target');
            var previewTargetSelector = $(this).data('preview-target');
            var $valueTarget = $(valueTargetSelector);
            var $displayTarget = $(displayTargetSelector);
            var $previewTarget = $(previewTargetSelector);

            if ($valueTarget.length) {
                $valueTarget.val('').trigger('change');
            }

            if ($displayTarget.length) {
                $displayTarget.val('').trigger('change');
            }

            if ($previewTarget.length) {
                $previewTarget.empty();
            }
        });
    })(jQuery);
    </script>
    <?php
}

/**
 * Save product fields from metabox.
 *
 * @param int $post_id Post ID.
 */
function wcg_save_product_fields_metabox( $post_id ) {
    if ( ! isset( $_POST['wcg_product_fields_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcg_product_fields_nonce'] ) ), 'wcg_save_product_fields' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $posted = array();

    if ( isset( $_POST['wcg_product_fields'] ) && is_array( $_POST['wcg_product_fields'] ) ) {
        $posted = wp_unslash( $_POST['wcg_product_fields'] );
    }

    foreach ( wcg_get_product_field_definitions() as $meta_key => $field ) {
        $raw = isset( $posted[ $meta_key ] ) ? trim( (string) $posted[ $meta_key ] ) : '';

        if ( $raw === '' ) {
            delete_post_meta( $post_id, $meta_key );
            continue;
        }

        $sanitized = ( $field['type'] === 'textarea' )
            ? wp_kses_post( $raw )
            : ( ( $field['type'] === 'media' ) ? wcg_sanitize_media_or_url( $raw ) : esc_url_raw( $raw ) );

        if ( $sanitized === '' ) {
            delete_post_meta( $post_id, $meta_key );
            continue;
        }

        update_post_meta( $post_id, $meta_key, $sanitized );
    }
}
add_action( 'save_post_product', 'wcg_save_product_fields_metabox' );

/**
 * Shortcode: [wcg_brochure_link]
 *
 * @return string
 */
function wcg_brochure_link_shortcode() {
    if ( ! is_singular( 'product' ) ) {
        return '';
    }

    $product_id = get_queried_object_id();
    $brochure   = wcg_resolve_media_value_to_url( (string) get_post_meta( $product_id, 'brochure', true ) );

    if ( $brochure === '' ) {
        return '';
    }

    return sprintf(
        '<a class="btn-pill btn-outline" href="%1$s" target="_blank" rel="noopener">%2$s</a>',
        esc_url( $brochure ),
        esc_html__( 'Download Brochure', 'picostrap' )
    );
}
add_shortcode( 'wcg_brochure_link', 'wcg_brochure_link_shortcode' );
