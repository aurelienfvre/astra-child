<?php

// --- Enqueue ---
add_action( 'wp_enqueue_scripts', 'ufc_enqueue_assets' );
function ufc_enqueue_assets() {
    wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css', array(), wp_get_theme( 'astra' )->get( 'Version' ) );
    wp_enqueue_style( 'ufc-child-style', get_stylesheet_directory_uri() . '/style.css', array( 'astra-parent-style' ), filemtime( get_stylesheet_directory() . '/style.css' ) );
    wp_enqueue_style( 'ufc-google-fonts', 'https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap', array(), null );
    wp_enqueue_style( 'ufc-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1' );
    wp_enqueue_script( 'ufc-custom-script', get_stylesheet_directory_uri() . '/script.js', array(), filemtime( get_stylesheet_directory() . '/script.js' ), true );
}

// --- Theme support ---
add_action( 'after_setup_theme', 'ufc_theme_support' );
function ufc_theme_support() {
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 200, 'flex-height' => true, 'flex-width' => true ) );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
    add_image_size( 'ufc-card-thumb', 600, 400, true );
    add_image_size( 'ufc-hero', 1920, 800, true );
}

// --- Modules ---
require_once get_stylesheet_directory() . '/inc/header-footer.php';
require_once get_stylesheet_directory() . '/inc/shortcodes.php';
require_once get_stylesheet_directory() . '/inc/admin.php';
require_once get_stylesheet_directory() . '/inc/um-redirects.php';

// --- Securite ---
remove_action( 'wp_head', 'wp_generator' );
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}

// --- Layout : no-sidebar + plain container ---
add_filter( 'astra_page_layout', function() { return 'no-sidebar'; } );
add_filter( 'astra_get_content_layout', function() { return 'plain-container'; } );

// --- Force template custom pour les evenements ---
add_filter( 'template_include', function( $template ) {
    if ( is_singular( 'tribe_events' ) ) {
        $custom = get_stylesheet_directory() . '/single-tribe_events.php';
        if ( file_exists( $custom ) ) { return $custom; }
    }
    return $template;
}, 99 );

// --- Cacher certaines pages du menu ---
add_filter( 'wp_nav_menu_objects', function( $items ) {
    $hidden = array( 'membres' );
    foreach ( $items as $key => $item ) {
        if ( in_array( strtolower( trim( $item->title ) ), $hidden, true ) ) {
            unset( $items[ $key ] );
        }
    }
    return $items;
}, 10, 2 );

// --- Commentaires desactives ---
add_filter( 'comments_open', '__return_false' );
add_filter( 'pings_open', '__return_false' );
add_action( 'admin_init', function() {
    remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
} );
add_action( 'admin_menu', function() { remove_menu_page( 'edit-comments.php' ); } );
