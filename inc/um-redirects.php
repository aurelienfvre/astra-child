<?php

// --- Redirect wp-login.php vers page Connexion UM ---
add_action( 'login_init', 'ufc_redirect_login_to_um' );
function ufc_redirect_login_to_um() {
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) { return; }
    $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
    if ( in_array( $action, array( 'logout', 'lostpassword', 'rp', 'resetpass', 'postpass', 'confirmaction' ), true ) ) { return; }
    if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) { return; }
    if ( isset( $_GET['interim-login'] ) || ! class_exists( 'UM' ) ) { return; }

    $page = get_page_by_path( 'connexion' );
    if ( $page ) {
        $url = get_permalink( $page );
        if ( ! empty( $_GET['redirect_to'] ) ) {
            $url = add_query_arg( 'redirect_to', urlencode( $_GET['redirect_to'] ), $url );
        }
        wp_safe_redirect( esc_url_raw( $url ) );
        exit;
    }
}

// --- Redirect wp-login.php?action=register vers page Inscription UM ---
add_action( 'login_init', 'ufc_redirect_register_to_um' );
function ufc_redirect_register_to_um() {
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) { return; }
    if ( ( isset( $_GET['action'] ) ? $_GET['action'] : '' ) !== 'register' ) { return; }
    if ( ! class_exists( 'UM' ) ) { return; }

    $page = get_page_by_path( 'inscription' );
    if ( $page ) {
        wp_safe_redirect( esc_url_raw( get_permalink( $page ) ) );
        exit;
    }
}

// --- Fix URL Mon Compte UM ---
add_filter( 'um_account_page_default_tab_url', function( $url ) {
    $page = get_page_by_path( 'mon-compte' );
    return $page ? get_permalink( $page ) : $url;
} );
