<?php

// --- Helper : URLs auth avec support UM ---
function ufc_get_auth_urls() {
    $login_url    = ufc_get_um_page_url( 'login' );
    $register_url = ufc_get_um_page_url( 'register' );
    if ( ! $login_url )    { $login_url    = wp_login_url( home_url( '/' ) ); }
    if ( ! $register_url ) { $register_url = wp_registration_url(); }
    return array( 'login' => $login_url, 'register' => $register_url );
}

// --- Header ---
add_action( 'astra_body_top', 'ufc_custom_header_bar', 1 );
function ufc_custom_header_bar() {
    ?>
    <header class="ufc-header" id="ufc-header">
        <div class="ufc-header-inner">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ufc-header-logo">
                <?php has_custom_logo() ? the_custom_logo() : print esc_html( get_bloginfo( 'name' ) ); ?>
            </a>

            <nav class="ufc-desktop-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'astra-child' ); ?>">
                <?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'ufc-desktop-menu', 'fallback_cb' => false, 'depth' => 1 ) ); ?>
            </nav>

            <div class="ufc-auth-nav">
                <?php if ( is_user_logged_in() ) :
                    $current_user = wp_get_current_user();
                    $account_url  = get_permalink( get_page_by_path( 'mon-compte' ) );
                ?>
                    <span class="ufc-auth-greeting"><i class="fas fa-user"></i> <?php echo esc_html( $current_user->display_name ); ?></span>
                    <a href="<?php echo esc_url( $account_url ); ?>" class="ufc-auth-btn ufc-btn-account"><i class="fas fa-cog"></i> <?php esc_html_e( 'Mon Compte', 'astra-child' ); ?></a>
                    <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="ufc-auth-btn ufc-btn-logout"><i class="fas fa-sign-out-alt"></i> <?php esc_html_e( 'Déconnexion', 'astra-child' ); ?></a>
                <?php else :
                    $urls = ufc_get_auth_urls();
                ?>
                    <a href="<?php echo esc_url( $urls['login'] ); ?>" class="ufc-auth-btn ufc-btn-login"><i class="fas fa-sign-in-alt"></i> <?php esc_html_e( 'Connexion', 'astra-child' ); ?></a>
                    <a href="<?php echo esc_url( $urls['register'] ); ?>" class="ufc-auth-btn ufc-btn-register"><i class="fas fa-user-plus"></i> <?php esc_html_e( 'Inscription', 'astra-child' ); ?></a>
                <?php endif; ?>
            </div>

            <button class="ufc-burger-btn" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'astra-child' ); ?>" aria-expanded="false">
                <span class="ufc-burger-line"></span>
                <span class="ufc-burger-line"></span>
                <span class="ufc-burger-line"></span>
            </button>
        </div>
    </header>
    <?php
}

// --- Menu mobile ---
add_action( 'astra_body_top', 'ufc_mobile_menu_panel' );
function ufc_mobile_menu_panel() {
    ?>
    <div class="ufc-mobile-overlay" aria-hidden="true"></div>
    <nav class="ufc-mobile-panel" aria-label="<?php esc_attr_e( 'Navigation mobile', 'astra-child' ); ?>">
        <div class="ufc-mobile-panel-header">
            <span class="ufc-mobile-panel-title"><?php esc_html_e( 'UFC Community', 'astra-child' ); ?></span>
            <button class="ufc-mobile-close" aria-label="<?php esc_attr_e( 'Fermer le menu', 'astra-child' ); ?>"><i class="fas fa-times"></i></button>
        </div>
        <div class="ufc-mobile-panel-body">
            <?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'ufc-mobile-menu-list', 'fallback_cb' => false, 'depth' => 2 ) ); ?>
            <div class="ufc-mobile-auth">
                <?php if ( is_user_logged_in() ) : ?>
                    <div class="ufc-mobile-user"><i class="fas fa-user-circle"></i> <?php echo esc_html( wp_get_current_user()->display_name ); ?></div>
                    <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'mon-compte' ) ) ); ?>" class="ufc-mobile-auth-link"><i class="fas fa-cog"></i> <?php esc_html_e( 'Mon Compte', 'astra-child' ); ?></a>
                    <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="ufc-mobile-auth-link ufc-mobile-logout"><i class="fas fa-sign-out-alt"></i> <?php esc_html_e( 'Déconnexion', 'astra-child' ); ?></a>
                <?php else :
                    $urls = ufc_get_auth_urls();
                ?>
                    <a href="<?php echo esc_url( $urls['login'] ); ?>" class="ufc-mobile-auth-link"><i class="fas fa-sign-in-alt"></i> <?php esc_html_e( 'Connexion', 'astra-child' ); ?></a>
                    <a href="<?php echo esc_url( $urls['register'] ); ?>" class="ufc-mobile-auth-link ufc-mobile-register"><i class="fas fa-user-plus"></i> <?php esc_html_e( 'Inscription', 'astra-child' ); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <?php
}

// --- Footer ---
add_action( 'wp_footer', 'ufc_custom_footer', 5 );
function ufc_custom_footer() {
    ?>
    <footer class="ufc-footer" role="contentinfo">
        <div class="ufc-footer-inner">
            <div class="ufc-footer-col">
                <h4 class="ufc-footer-title"><?php esc_html_e( 'UFC Community', 'astra-child' ); ?></h4>
                <p><?php esc_html_e( 'La communauté francophone dédiée aux fans de MMA et UFC. Retrouvez les événements, les actus et rejoignez notre communauté de passionnés.', 'astra-child' ); ?></p>
                <div class="ufc-footer-social">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="ufc-footer-col">
                <h4 class="ufc-footer-title"><?php esc_html_e( 'Navigation', 'astra-child' ); ?></h4>
                <ul class="ufc-footer-links">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'astra-child' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/actualites/' ) ); ?>"><?php esc_html_e( 'Actualités', 'astra-child' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/evenements/' ) ); ?>"><?php esc_html_e( 'Événements', 'astra-child' ); ?></a></li>
                </ul>
            </div>
            <div class="ufc-footer-col">
                <h4 class="ufc-footer-title"><?php esc_html_e( 'Mon Espace', 'astra-child' ); ?></h4>
                <ul class="ufc-footer-links">
                    <?php if ( is_user_logged_in() ) : ?>
                        <li><a href="<?php echo esc_url( home_url( '/mon-compte/' ) ); ?>"><?php esc_html_e( 'Mon Compte', 'astra-child' ); ?></a></li>
                        <li><a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Déconnexion', 'astra-child' ); ?></a></li>
                    <?php else : ?>
                        <li><a href="<?php echo esc_url( home_url( '/connexion/' ) ); ?>"><?php esc_html_e( 'Connexion', 'astra-child' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/inscription/' ) ); ?>"><?php esc_html_e( 'Inscription', 'astra-child' ); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="ufc-footer-col">
                <h4 class="ufc-footer-title"><?php esc_html_e( 'Contact', 'astra-child' ); ?></h4>
                <ul class="ufc-footer-contact">
                    <li><i class="fas fa-envelope"></i> <?php esc_html_e( 'contact@ufc-community.fr', 'astra-child' ); ?></li>
                    <li><i class="fas fa-map-marker-alt"></i> <?php esc_html_e( 'Paris, France', 'astra-child' ); ?></li>
                    <li><i class="fas fa-clock"></i> <?php esc_html_e( 'Lun - Ven : 9h - 18h', 'astra-child' ); ?></li>
                </ul>
            </div>
        </div>
        <div class="ufc-footer-bottom">
            <p>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php esc_html_e( 'UFC Community. Tous droits réservés.', 'astra-child' ); ?></p>
        </div>
    </footer>
    <?php
}

add_filter( 'astra_footer_copyright_text', '__return_empty_string' );
