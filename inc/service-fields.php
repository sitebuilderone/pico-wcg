<?php
/**
 * Service/page head code fields for schema and similar markup.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return post types that support service head code.
 *
 * @return array<int,string>
 */
function wcg_get_head_code_post_types() {
    $post_types = array( 'page' );

    if ( post_type_exists( 'service' ) ) {
        $post_types[] = 'service';
    }

    return $post_types;
}

/**
 * Sanitize head code value.
 *
 * Allows trusted editors to store script markup (for example JSON-LD schema).
 *
 * @param mixed $value Raw submitted value.
 *
 * @return string
 */
function wcg_sanitize_head_code( $value ) {
    return trim( (string) $value );
}

/**
 * Register post meta for additional head code.
 */
function wcg_register_head_code_meta() {
    foreach ( wcg_get_head_code_post_types() as $post_type ) {
        register_post_meta(
            $post_type,
            'wcg_head_code',
            array(
                'single'            => true,
                'type'              => 'string',
                'show_in_rest'      => true,
                'sanitize_callback' => 'wcg_sanitize_head_code',
                'auth_callback'     => function() {
                    return current_user_can( 'edit_posts' );
                },
            )
        );
    }
}
add_action( 'init', 'wcg_register_head_code_meta' );

/**
 * Add metabox for head code.
 */
function wcg_add_head_code_metabox() {
    foreach ( wcg_get_head_code_post_types() as $post_type ) {
        add_meta_box(
            'wcg_head_code',
            'Additional Head Code (Schema / JSON-LD)',
            'wcg_render_head_code_metabox',
            $post_type,
            'normal',
            'default'
        );
    }
}
add_action( 'add_meta_boxes', 'wcg_add_head_code_metabox' );

/**
 * Render metabox UI.
 *
 * @param WP_Post $post Current post.
 */
function wcg_render_head_code_metabox( $post ) {
    wp_nonce_field( 'wcg_save_head_code', 'wcg_head_code_nonce' );

    $value = (string) get_post_meta( $post->ID, 'wcg_head_code', true );

    echo '<p>Add code that should be printed inside &lt;head&gt; for this page/service only.</p>';
    echo '<p><strong>Example:</strong> FAQ schema using <code>&lt;script type="application/ld+json"&gt;...&lt;/script&gt;</code>.</p>';

    printf(
        '<textarea id="wcg-head-code" name="wcg_head_code" rows="14" class="large-text code" placeholder="<script type=&quot;application/ld+json&quot;>{ ... }</script>">%s</textarea>',
        esc_textarea( $value )
    );
}

/**
 * Save metabox value.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Current post object.
 */
function wcg_save_head_code_metabox( $post_id, $post ) {
    if ( ! in_array( $post->post_type, wcg_get_head_code_post_types(), true ) ) {
        return;
    }

    if ( ! isset( $_POST['wcg_head_code_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcg_head_code_nonce'] ) ), 'wcg_save_head_code' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $raw = '';

    if ( isset( $_POST['wcg_head_code'] ) ) {
        $raw = wp_unslash( $_POST['wcg_head_code'] );
    }

    $sanitized = wcg_sanitize_head_code( $raw );

    if ( $sanitized === '' ) {
        delete_post_meta( $post_id, 'wcg_head_code' );
        return;
    }

    update_post_meta( $post_id, 'wcg_head_code', $sanitized );
}
add_action( 'save_post', 'wcg_save_head_code_metabox', 10, 2 );

/**
 * Print per-page/per-service head code in the front-end <head>.
 */
function wcg_output_head_code() {
    if ( is_admin() || ! is_singular() ) {
        return;
    }

    $post_id = get_queried_object_id();

    if ( ! $post_id ) {
        return;
    }

    $post = get_post( $post_id );

    if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, wcg_get_head_code_post_types(), true ) ) {
        return;
    }

    $head_code = (string) get_post_meta( $post_id, 'wcg_head_code', true );

    if ( trim( $head_code ) === '' ) {
        return;
    }

    echo "\n" . $head_code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'wcg_output_head_code', 50 );
