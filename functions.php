<?php
/*
        _               _                  _____        _     _ _     _   _   _                         
       (_)             | |                | ____|      | |   (_) |   | | | | | |                        
  _ __  _  ___ ___  ___| |_ _ __ __ _ _ __| |__     ___| |__  _| | __| | | |_| |__   ___ _ __ ___   ___ 
 | '_ \| |/ __/ _ \/ __| __| '__/ _` | '_ \___ \   / __| '_ \| | |/ _` | | __| '_ \ / _ \ '_ ` _ \ / _ \
 | |_) | | (_| (_) \__ \ |_| | | (_| | |_) |__) | | (__| | | | | | (_| | | |_| | | |  __/ | | | | |  __/
 | .__/|_|\___\___/|___/\__|_|  \__,_| .__/____/   \___|_| |_|_|_|\__,_|  \__|_| |_|\___|_| |_| |_|\___|
 | |                                 | |                                                                
 |_|                                 |_|                                                                

                                                       
*************************************** WELCOME TO PICOSTRAP ***************************************

********************* THE BEST WAY TO EXPERIENCE SASS, BOOTSTRAP AND WORDPRESS *********************

    PLEASE WATCH THE VIDEOS FOR BEST RESULTS:
    https://www.youtube.com/playlist?list=PLtyHhWhkgYU8i11wu-5KJDBfA9C-D4Bfl

*/

//LOAD LC CONFIG TO DEFINE FRAMEWORK
require_once ("livecanvas/configuration.php");

// DE-ENQUEUE PARENT THEME BOOTSTRAP JS BUNDLE
add_action( 'wp_print_scripts', function(){
    wp_dequeue_script( 'bootstrap5' );
    //wp_dequeue_script( 'dark-mode-switch' );  //optionally
}, 100 );

// ENQUEUE THE BOOTSTRAP JS BUNDLE (AND EVENTUALLY MORE LIBS) FROM THE CHILD THEME DIRECTORY
add_action( 'wp_enqueue_scripts', function() {
    //enqueue js in footer, defer
    wp_enqueue_script( 'bootstrap5-childtheme', get_stylesheet_directory_uri() . "/js/bootstrap.bundle.min.js", array(), null, array('strategy' => 'defer', 'in_footer' => true)  );
    wp_enqueue_script( 'sb-one-bundle', get_stylesheet_directory_uri() . "/js/sb-one-bundle.js", array(), null, array('strategy' => 'defer', 'in_footer' => true)  );
    
    //optional: example of how to globally lazyload js files eg lottie player, using defer
    //wp_enqueue_script( 'lottie-player', 'https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js', array(), null, array('strategy' => 'defer', 'in_footer' => true)  );
}, 101);

// HACK HERE: ENQUEUE YOUR CUSTOM JS FILES, IF NEEDED 
add_action( 'wp_enqueue_scripts', function() {	   
    
    //UNCOMMENT next row to include the js/custom.js file globally
    wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/js/custom.js', array(/* 'jquery' */), null, array('strategy' => 'defer', 'in_footer' => true) ); 

    //UNCOMMENT next 3 rows to load the js file only on one page
    //if (is_page('mypageslug')) {
    //    wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/js/custom.js', array(/* 'jquery' */), null, array('strategy' => 'defer', 'in_footer' => true) ); 
    //}  

}, 102);

// OPTIONAL: ADD MORE NAV MENUS
//register_nav_menus( array( 'third' => __( 'Third Menu', 'picostrap' ), 'fourth' => __( 'Fourth Menu', 'picostrap' ), 'fifth' => __( 'Fifth Menu', 'picostrap' ), ) );
// THEN USE SHORTCODE:  [lc_nav_menu theme_location="third" container_class="" container_id="" menu_class="navbar-nav"]

// CHECK PARENT THEME VERSION
add_action( 'admin_notices', function  () {
    if( (pico_get_parent_theme_version())>=3.0) return; 
	$message = __( 'This Child Theme requires at least Picostrap Version 3.0.0  in order to work properly. Please update the parent theme.', 'picostrap' );
	printf( '<div class="%1$s"><h1>%2$s</h1></div>', esc_attr( 'notice notice-error' ), esc_html( $message ) );
} );

// OPTIONAL: FOR SECURITY: DISABLE APPLICATION PASSWORDS. Uncomment if needed
//add_filter( 'wp_is_application_passwords_available', '__return_false' );

// Keep stylesheet and preload URL versions in sync to avoid duplicate CSS downloads.
function wcg_get_bundle_css_version() {
    $bundle_path = get_stylesheet_directory() . '/css-output/bundle.css';

    if ( file_exists( $bundle_path ) ) {
        return (string) filemtime( $bundle_path );
    }

    return (string) get_theme_mod( 'css_bundle_version_number' );
}

// Cache-bust compiled CSS bundle so browser fetches updates after each build.
add_filter( 'style_loader_src', function( $src ) {
    if ( strpos( $src, 'css-output/bundle.css' ) === false ) {
        return $src;
    }

    $version = wcg_get_bundle_css_version();
    if ( $version === '' ) {
        return $src;
    }

    return add_query_arg( 'ver', $version, remove_query_arg( 'ver', $src ) );
}, 20 );

// Override parent preload hint so it uses the same version as the stylesheet URL.
add_action( 'after_setup_theme', function() {
    if ( function_exists( 'picostrap_hints' ) ) {
        remove_action( 'send_headers', 'picostrap_hints' );
    }

    add_action( 'send_headers', function() {
        if ( get_theme_mod( 'disable_bootstrap' ) || ! function_exists( 'picostrap_get_css_url' ) ) {
            return;
        }

        $bundle_url = picostrap_get_css_url();
        $version    = wcg_get_bundle_css_version();

        if ( $version !== '' ) {
            $bundle_url = add_query_arg( 'ver', $version, remove_query_arg( 'ver', $bundle_url ) );
        }

        $headers = 'link: <' . $bundle_url . '>; rel=preload; as=style';

        if ( ! get_theme_mod( 'disable_gutenberg' ) || ( function_exists( 'lc_plugin_option_is_set' ) && lc_plugin_option_is_set( 'gtblocks' ) ) ) {
            $headers .= ', <' . includes_url() . 'css/dist/block-library/style.min.css?ver=' . get_bloginfo( 'version' ) . '>; rel=preload; as=style';
        }

        header( $headers );
    } );
}, 20 );

// Load WooCommerce front-end assets only where they are needed.
function wcg_should_load_woocommerce_assets() {
    if ( is_admin() ) {
        return true;
    }

    if ( ! function_exists( 'is_woocommerce' ) ) {
        return false;
    }

    if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
        return true;
    }

    if ( ! is_singular() ) {
        return false;
    }

    $post = get_post();
    if ( ! $post instanceof WP_Post ) {
        return false;
    }

    if ( has_block( 'woocommerce/', $post ) ) {
        return true;
    }

    $shortcodes = array(
        'products',
        'product',
        'product_page',
        'add_to_cart',
        'add_to_cart_url',
        'product_categories',
        'featured_products',
        'sale_products',
        'best_selling_products',
        'recent_products',
        'top_rated_products',
        'woocommerce_cart',
        'woocommerce_checkout',
        'woocommerce_my_account',
    );

    foreach ( $shortcodes as $shortcode ) {
        if ( has_shortcode( $post->post_content, $shortcode ) ) {
            return true;
        }
    }

    return false;
}

add_filter( 'woocommerce_enqueue_styles', function( $styles ) {
    if ( wcg_should_load_woocommerce_assets() ) {
        return $styles;
    }

    return array();
}, 20 );

add_action( 'wp_enqueue_scripts', function() {
    if ( wcg_should_load_woocommerce_assets() ) {
        return;
    }

    $style_handles = array(
        'wc-blocks-style',
        'woocommerce-layout',
        'woocommerce-smallscreen',
        'woocommerce-general',
    );

    foreach ( $style_handles as $handle ) {
        wp_dequeue_style( $handle );
        wp_deregister_style( $handle );
    }

    $script_handles = array(
        'wc-jquery-blockui',
        'wc-js-cookie',
        'woocommerce',
        'wc-cart-fragments',
        'js-cookie',
    );

    foreach ( $script_handles as $handle ) {
        wp_dequeue_script( $handle );
        wp_deregister_script( $handle );
    }
}, 999 );

add_action( 'wp', function() {
    if ( wcg_should_load_woocommerce_assets() ) {
        return;
    }

    if ( function_exists( 'wc_gallery_noscript' ) ) {
        remove_action( 'wp_head', 'wc_gallery_noscript' );
    }
} );

// ADD YOUR CUSTOM PHP CODE DOWN BELOW /////////////////////////

require_once get_stylesheet_directory() . '/inc/product-fields.php';

// Skip GTM on local environments (for example: mysite.local).
function wcg_should_load_gtm() {
    $host = '';

    if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
        $host = (string) $_SERVER['HTTP_HOST'];
    } else {
        $host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
    }

    $host = strtolower( preg_replace( '/:\\d+$/', '', $host ) );

    if ( $host === 'localhost' || $host === '127.0.0.1' || $host === '::1' ) {
        return false;
    }

    if ( substr( $host, -6 ) === '.local' ) {
        return false;
    }

    return true;
}

if ( wcg_should_load_gtm() ) {
    // Google Tag Manager: load script early in <head> and noscript after <body> opens.
    add_action( 'wp_head', function() {
        ?>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-WKVZM35');</script>
        <!-- End Google Tag Manager -->
        <?php
    }, 1 );

    add_action( 'wp_body_open', function() {
        ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WKVZM35"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <?php
    }, 1 );
}

