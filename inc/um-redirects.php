<?php
/* J'ai codé ici — Redirections wp-login vers Ultimate Member */

// --- Helper : recuperer l'URL d'une page UM par son role ---
function ufc_get_um_page_url( $role ) {
    if ( ! class_exists( 'UM' ) ) { return ''; }

    // D'abord essayer via les options UM (fiable quel que soit le slug)
    $um_options = get_option( 'um_options', array() );
    $keys = array( 'login' => 'core_login', 'register' => 'core_register', 'user' => 'core_user', 'members' => 'core_members' );
    if ( isset( $keys[ $role ], $um_options[ $keys[ $role ] ] ) ) {
        $page_id = $um_options[ $keys[ $role ] ];
        if ( $page_id && get_post_status( $page_id ) === 'publish' ) {
            return get_permalink( $page_id );
        }
    }

    // Fallback : chercher par slug
    $slugs = array( 'login' => 'connexion', 'register' => 'inscription', 'user' => 'mon-compte', 'members' => 'membres' );
    if ( isset( $slugs[ $role ] ) ) {
        $page = get_page_by_path( $slugs[ $role ] );
        if ( $page && $page->post_status === 'publish' ) { return get_permalink( $page ); }
    }

    return '';
}

// --- Filtrer login_url pour pointer vers la page UM ---
add_filter( 'login_url', 'ufc_filter_login_url', 10, 3 );
function ufc_filter_login_url( $login_url, $redirect, $force_reauth ) {
    if ( ! class_exists( 'UM' ) ) { return $login_url; }
    $um_login = ufc_get_um_page_url( 'login' );
    if ( ! $um_login ) { return $login_url; }
    if ( $redirect ) {
        $um_login = add_query_arg( 'redirect_to', urlencode( $redirect ), $um_login );
    }
    return $um_login;
}

// --- Filtrer register_url pour pointer vers la page UM ---
add_filter( 'register_url', 'ufc_filter_register_url' );
function ufc_filter_register_url( $register_url ) {
    if ( ! class_exists( 'UM' ) ) { return $register_url; }
    $um_register = ufc_get_um_page_url( 'register' );
    return $um_register ? $um_register : $register_url;
}

// --- Redirect wp-login.php vers page Connexion UM ---
add_action( 'login_init', 'ufc_redirect_login_to_um' );
function ufc_redirect_login_to_um() {
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) { return; }
    $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
    if ( in_array( $action, array( 'logout', 'lostpassword', 'rp', 'resetpass', 'postpass', 'confirmaction' ), true ) ) { return; }
    if ( isset( $_GET['interim-login'] ) || ! class_exists( 'UM' ) ) { return; }

    $url = ufc_get_um_page_url( 'login' );
    if ( $url ) {
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

    $url = ufc_get_um_page_url( 'register' );
    if ( $url ) {
        wp_safe_redirect( esc_url_raw( $url ) );
        exit;
    }
}

// --- Apres connexion : rediriger tout le monde vers l'accueil ---
add_filter( 'login_redirect', 'ufc_login_redirect', 10, 3 );
function ufc_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
    if ( ! is_wp_error( $user ) && $requested_redirect_to === admin_url() ) {
        return home_url( '/' );
    }
    return $redirect_to;
}

// --- Fix URL Mon Compte UM ---
add_filter( 'um_account_page_default_tab_url', function( $url ) {
    $fixed = ufc_get_um_page_url( 'user' );
    return $fixed ? $fixed : $url;
} );
