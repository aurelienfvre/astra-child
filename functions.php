<?php
add_action( 'wp_enqueue_scripts', 'ufc_enqueue_assets' );
function ufc_enqueue_assets() {
    wp_enqueue_style(
        'astra-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme( 'astra' )->get( 'Version' )
    );

    wp_enqueue_style(
        'ufc-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'astra-parent-style' ),
        filemtime( get_stylesheet_directory() . '/style.css' )
    );

    wp_enqueue_style(
        'ufc-google-fonts',
        'https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'ufc-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        array(),
        '6.5.1'
    );

    wp_enqueue_script(
        'ufc-custom-script',
        get_stylesheet_directory_uri() . '/script.js',
        array(),
        filemtime( get_stylesheet_directory() . '/script.js' ),
        true
    );

    wp_localize_script( 'ufc-custom-script', 'ufcData', array(
        'ajaxUrl' => esc_url( admin_url( 'admin-ajax.php' ) ),
        'nonce'   => wp_create_nonce( 'ufc_nonce' ),
        'siteUrl' => esc_url( home_url( '/' ) ),
    ) );
}

add_action( 'after_setup_theme', 'ufc_theme_support' );
function ufc_theme_support() {
    add_theme_support( 'post-thumbnails' );

    add_theme_support( 'title-tag' );

    add_theme_support( 'custom-logo', array(
        'height'      => 80,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ) );

    add_image_size( 'ufc-card-thumb', 600, 400, true );

    add_image_size( 'ufc-hero', 1920, 800, true );
}

add_action( 'admin_init', 'ufc_auto_configuration' );
function ufc_auto_configuration() {
    if ( get_option( 'ufc_setup_done' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $um_login_shortcode    = '[ultimatemember form_id="7"]';
    $um_register_shortcode = '[ultimatemember form_id="7"]';
    if ( class_exists( 'UM' ) ) {
        $login_forms = get_posts( array(
            'post_type'      => 'um_form',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'meta_key'       => '_um_mode',
            'meta_value'     => 'login',
        ) );
        if ( ! empty( $login_forms ) ) {
            $um_login_shortcode = '[ultimatemember form_id="' . $login_forms[0]->ID . '"]';
        }
        $register_forms = get_posts( array(
            'post_type'      => 'um_form',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'meta_key'       => '_um_mode',
            'meta_value'     => 'register',
        ) );
        if ( ! empty( $register_forms ) ) {
            $um_register_shortcode = '[ultimatemember form_id="' . $register_forms[0]->ID . '"]';
        }
    }

    $pages = array(
        'Accueil'      => '<!-- Page d\'accueil — le contenu est géré par front-page.php -->',
        'Actualités'   => '<!-- Page blog — WordPress y affiche automatiquement les articles -->',
        'Événements'   => '<!-- Page événements — The Events Calendar gère le contenu -->',
        'Membres'      => '[ultimatemember_directory]',
        'Inscription'  => $um_register_shortcode,
        'Connexion'    => $um_login_shortcode,
        'Mon Compte'   => '[ultimatemember_account]',
    );

    $page_ids = array();
    foreach ( $pages as $title => $content ) {
        $existing = get_posts( array(
            'post_type'      => 'page',
            'title'          => $title,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
        ) );

        if ( ! empty( $existing ) ) {
            $page_ids[ $title ] = $existing[0]->ID;
        } else {
            $page_id = wp_insert_post( array(
                'post_title'   => sanitize_text_field( $title ),
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => get_current_user_id(),
            ) );
            if ( ! is_wp_error( $page_id ) ) {
                $page_ids[ $title ] = $page_id;
            }
        }
    }

    if ( isset( $page_ids['Accueil'], $page_ids['Actualités'] ) ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $page_ids['Accueil'] );
        update_option( 'page_for_posts', $page_ids['Actualités'] );
    }

    update_option( 'permalink_structure', '/%postname%/' );
    flush_rewrite_rules();
    update_option( 'users_can_register', 1 );
    update_option( 'default_role', 'subscriber' );

    update_option( 'blogname', 'UFC Community' );
    update_option( 'blogdescription', 'La communauté francophone des fans de MMA et UFC' );

    update_option( 'timezone_string', 'Europe/Paris' );
    update_option( 'date_format', 'j F Y' );
    update_option( 'time_format', 'H:i' );

    update_option( 'default_pingback_flag', 0 );
    update_option( 'default_ping_status', 'closed' );
    update_option( 'comment_moderation', 1 );
    update_option( 'comment_registration', 1 );
    
    $menu_name = 'Menu UFC Principal';
    $menu_exists = wp_get_nav_menu_object( $menu_name );

    if ( ! $menu_exists ) {
        $menu_id = wp_create_nav_menu( $menu_name );

        $menu_pages = array( 'Accueil', 'Actualités', 'Événements', 'Membres' );
        $position = 0;

        foreach ( $menu_pages as $page_title ) {
            if ( isset( $page_ids[ $page_title ] ) ) {
                $position++;
                wp_update_nav_menu_item( $menu_id, 0, array(
                    'menu-item-title'     => $page_title,
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => $page_ids[ $page_title ],
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                    'menu-item-position'  => $position,
                ) );
            }
        }

        $locations = get_theme_mod( 'nav_menu_locations', array() );
        $locations['primary'] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }

    $astra_settings = get_option( 'astra-settings', array() );

    $astra_settings['site-layout']                = 'ast-full-width-layout';
    $astra_settings['site-content-width']          = 1200;
    $astra_settings['site-layout-outside-bg-obj-responsive'] = array(
        'desktop' => array( 'background-color' => '#111111' ),
        'tablet'  => array( 'background-color' => '#111111' ),
        'mobile'  => array( 'background-color' => '#111111' ),
    );

    $astra_settings['header-main-layout-width'] = 'full';

    update_option( 'astra-settings', $astra_settings );
    if ( class_exists( 'UM' ) ) {
        $um_options = get_option( 'um_options', array() );
        if ( isset( $page_ids['Connexion'] ) ) {
            $um_options['core_login'] = $page_ids['Connexion'];
        }
        if ( isset( $page_ids['Inscription'] ) ) {
            $um_options['core_register'] = $page_ids['Inscription'];
        }
        if ( isset( $page_ids['Mon Compte'] ) ) {
            $um_options['core_user'] = $page_ids['Mon Compte'];
        }
        if ( isset( $page_ids['Membres'] ) ) {
            $um_options['core_members'] = $page_ids['Membres'];
        }
        update_option( 'um_options', $um_options );
    }

    if ( isset( $page_ids['Événements'] ) && class_exists( 'Tribe__Events__Main' ) ) {
        update_option( 'tribe_events_page_id', $page_ids['Événements'] );
    }

    update_option( 'ufc_setup_done', true );
}

add_action( 'admin_init', 'ufc_maybe_reset_config' );
function ufc_maybe_reset_config() {
    if ( isset( $_GET['ufc_reset'] ) && $_GET['ufc_reset'] === '1' && current_user_can( 'manage_options' ) ) {
        delete_option( 'ufc_setup_done' );
        delete_option( 'ufc_um_pages_fixed_v2' );
        delete_option( 'ufc_content_seeded' );
        delete_option( 'ufc_events_seeded_v2' );
        delete_option( 'ufc_rewrite_flushed_v3' );
        delete_option( 'ufc_um_directory_fixed_v2' );
        delete_option( 'ufc_um_directory_fixed_v3' );
        delete_option( 'ufc_um_directory_fixed_v4' );
        delete_option( 'ufc_rsvp_added_v2' );
        delete_option( 'ufc_rsvp_added_v3' );
        delete_option( 'ufc_rsvp_added_v4' );
        delete_option( 'ufc_rsvp_added_v5' );
        delete_option( 'ufc_ticket_pages_created_v1' );
        delete_option( 'ufc_images_attached_v1' );
        wp_safe_redirect( admin_url() );
        exit;
    }
}

add_action( 'astra_body_top', 'ufc_custom_header_bar', 1 );
function ufc_custom_header_bar() {
    ?>
    <header class="ufc-header" id="ufc-header">
        <div class="ufc-header-inner">
            <!-- Logo / Titre du site -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ufc-header-logo">
                <?php
                if ( has_custom_logo() ) {
                    the_custom_logo();
                } else {
                    echo esc_html( get_bloginfo( 'name' ) );
                }
                ?>
            </a>

            <nav class="ufc-desktop-nav" aria-label="<?php echo esc_attr__( 'Navigation principale', 'astra-child' ); ?>">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'ufc-desktop-menu',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                ) );
                ?>
            </nav>

            <div class="ufc-auth-nav">
                <?php if ( is_user_logged_in() ) : ?>
                    <?php
                    $current_user = wp_get_current_user();
                    $account_url  = get_permalink( get_page_by_path( 'mon-compte' ) );
                    $logout_url   = wp_logout_url( home_url( '/' ) );
                    ?>
                    <span class="ufc-auth-greeting">
                        <i class="fas fa-user"></i> <?php echo esc_html( $current_user->display_name ); ?>
                    </span>
                    <a href="<?php echo esc_url( $account_url ); ?>" class="ufc-auth-btn ufc-btn-account">
                        <i class="fas fa-cog"></i> <?php echo esc_html__( 'Mon Compte', 'astra-child' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $logout_url ); ?>" class="ufc-auth-btn ufc-btn-logout">
                        <i class="fas fa-sign-out-alt"></i> <?php echo esc_html__( 'Déconnexion', 'astra-child' ); ?>
                    </a>
                <?php else : ?>
                    <?php
                    $login_url    = wp_login_url( home_url( '/' ) );
                    $register_url = wp_registration_url();
                    if ( class_exists( 'UM' ) ) {
                        $lp = get_page_by_path( 'connexion' );
                        if ( $lp ) { $login_url = get_permalink( $lp ); }
                        $rp = get_page_by_path( 'inscription' );
                        if ( $rp ) { $register_url = get_permalink( $rp ); }
                    }
                    ?>
                    <a href="<?php echo esc_url( $login_url ); ?>" class="ufc-auth-btn ufc-btn-login">
                        <i class="fas fa-sign-in-alt"></i> <?php echo esc_html__( 'Connexion', 'astra-child' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $register_url ); ?>" class="ufc-auth-btn ufc-btn-register">
                        <i class="fas fa-user-plus"></i> <?php echo esc_html__( 'Inscription', 'astra-child' ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <button class="ufc-burger-btn" aria-label="<?php echo esc_attr__( 'Ouvrir le menu', 'astra-child' ); ?>" aria-expanded="false">
                <span class="ufc-burger-line"></span>
                <span class="ufc-burger-line"></span>
                <span class="ufc-burger-line"></span>
            </button>
        </div>
    </header>
    <?php
}

add_action( 'astra_body_top', 'ufc_mobile_menu_panel' );
function ufc_mobile_menu_panel() {
    ?>
    <div class="ufc-mobile-overlay" aria-hidden="true"></div>

    <nav class="ufc-mobile-panel" aria-label="<?php echo esc_attr__( 'Navigation mobile', 'astra-child' ); ?>">
        <div class="ufc-mobile-panel-header">
            <span class="ufc-mobile-panel-title"><?php echo esc_html__( 'UFC Community', 'astra-child' ); ?></span>
            <button class="ufc-mobile-close" aria-label="<?php echo esc_attr__( 'Fermer le menu', 'astra-child' ); ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="ufc-mobile-panel-body">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'ufc-mobile-menu-list',
                'fallback_cb'    => false,
                'depth'          => 2,
            ) );
            ?>

            <div class="ufc-mobile-auth">
                <?php if ( is_user_logged_in() ) : ?>
                    <?php $current_user = wp_get_current_user(); ?>
                    <div class="ufc-mobile-user">
                        <i class="fas fa-user-circle"></i>
                        <?php echo esc_html( $current_user->display_name ); ?>
                    </div>
                    <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'mon-compte' ) ) ); ?>" class="ufc-mobile-auth-link">
                        <i class="fas fa-cog"></i> <?php echo esc_html__( 'Mon Compte', 'astra-child' ); ?>
                    </a>
                    <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="ufc-mobile-auth-link ufc-mobile-logout">
                        <i class="fas fa-sign-out-alt"></i> <?php echo esc_html__( 'Déconnexion', 'astra-child' ); ?>
                    </a>
                <?php else : ?>
                    <?php
                    $login_url    = wp_login_url( home_url( '/' ) );
                    $register_url = wp_registration_url();
                    if ( class_exists( 'UM' ) ) {
                        $lp = get_page_by_path( 'connexion' );
                        if ( $lp ) { $login_url = get_permalink( $lp ); }
                        $rp = get_page_by_path( 'inscription' );
                        if ( $rp ) { $register_url = get_permalink( $rp ); }
                    }
                    ?>
                    <a href="<?php echo esc_url( $login_url ); ?>" class="ufc-mobile-auth-link">
                        <i class="fas fa-sign-in-alt"></i> <?php echo esc_html__( 'Connexion', 'astra-child' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $register_url ); ?>" class="ufc-mobile-auth-link ufc-mobile-register">
                        <i class="fas fa-user-plus"></i> <?php echo esc_html__( 'Inscription', 'astra-child' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <?php
}

add_shortcode( 'ufc_next_events', 'ufc_shortcode_next_events' );
function ufc_shortcode_next_events( $atts ) {
    $atts = shortcode_atts( array(
        'count' => 5,
    ), $atts, 'ufc_next_events' );

    $count = absint( $atts['count'] );

    if ( ! function_exists( 'tribe_get_events' ) ) {
        return '<p class="ufc-notice">' . esc_html__( 'Le plugin The Events Calendar est requis.', 'astra-child' ) . '</p>';
    }

    $events = tribe_get_events( array(
        'posts_per_page' => $count,
        'start_date'     => current_time( 'Y-m-d H:i:s' ),
        'eventDisplay'   => 'list',
    ) );

    if ( empty( $events ) ) {
        return '<p class="ufc-notice">' . esc_html__( 'Aucun événement à venir.', 'astra-child' ) . '</p>';
    }

    $output = '<div class="ufc-events-grid">';

    foreach ( $events as $index => $event ) {
        $title    = esc_html( get_the_title( $event ) );
        $link     = esc_url( get_permalink( $event ) );
        $date     = esc_html( tribe_get_start_date( $event, false, 'd M Y' ) );
        $time     = esc_html( tribe_get_start_date( $event, false, 'H:i' ) );
        $venue    = esc_html( tribe_get_venue( $event->ID ) );
        $image    = get_the_post_thumbnail_url( $event, 'ufc-card-thumb' );

        if ( ! $image ) {
            $post_content = get_post_field( 'post_content', $event->ID );
            if ( preg_match( '/<img[^>]+src=["\']([^"\']+)/i', $post_content, $matches ) ) {
                $image = $matches[1];
            }
        }

        $excerpt  = esc_html( wp_trim_words( get_the_excerpt( $event ), 15, '...' ) );

        $main_class = ( $index === 0 ) ? ' ufc-main-event' : '';

        $output .= '<article class="ufc-event-card' . esc_attr( $main_class ) . '">';

        if ( $image ) {
            $output .= '<div class="ufc-card-image" style="background-image: url(' . esc_url( $image ) . ');">';
        } else {
            $output .= '<div class="ufc-card-image ufc-card-no-image">';
        }

        $output .= '  <div class="ufc-card-overlay">';

        if ( $index === 0 ) {
            $output .= '<span class="ufc-badge-main">' . esc_html__( 'MAIN EVENT', 'astra-child' ) . '</span>';
        }

        $output .= '    <span class="ufc-card-date"><i class="far fa-calendar"></i> ' . $date . '</span>';
        $output .= '  </div>'; 
        $output .= '</div>';

        $output .= '<div class="ufc-card-body">';
        $output .= '  <h3 class="ufc-card-title">' . $title . '</h3>';

        if ( $venue ) {
            $output .= '<p class="ufc-card-venue"><i class="fas fa-map-marker-alt"></i> ' . $venue . '</p>';
        }

        $output .= '  <p class="ufc-card-time"><i class="far fa-clock"></i> ' . $time . '</p>';

        if ( $excerpt ) {
            $output .= '<p class="ufc-card-excerpt">' . $excerpt . '</p>';
        }

        $output .= '  <a href="' . $link . '" class="ufc-card-btn">';
        $output .= '    <i class="fas fa-ticket-alt"></i> ' . esc_html__( 'VOIR DÉTAILS & TICKETS', 'astra-child' );
        $output .= '  </a>';
        $output .= '</div>'; 

        $output .= '</article>';
    }

    $output .= '</div>';

    return $output;
}


add_shortcode( 'ufc_latest_posts', 'ufc_shortcode_latest_posts' );
function ufc_shortcode_latest_posts( $atts ) {
    $atts = shortcode_atts( array(
        'count' => 3,
    ), $atts, 'ufc_latest_posts' );

    $count = absint( $atts['count'] );

    $posts = get_posts( array(
        'post_type'      => 'post',
        'posts_per_page' => $count,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );

    if ( empty( $posts ) ) {
        return '<p class="ufc-notice">' . esc_html__( 'Aucun article pour le moment.', 'astra-child' ) . '</p>';
    }

    $output = '<div class="ufc-posts-grid">';

    foreach ( $posts as $post ) {
        $title   = esc_html( get_the_title( $post ) );
        $link    = esc_url( get_permalink( $post ) );
        $date    = esc_html( get_the_date( 'j M Y', $post ) );
        $image   = get_the_post_thumbnail_url( $post, 'ufc-card-thumb' );

        if ( ! $image ) {
            $post_content = get_post_field( 'post_content', $post->ID );
            if ( preg_match( '/<img[^>]+src=["\']([^"\']+)/i', $post_content, $matches ) ) {
                $image = $matches[1];
            }
        }

        $excerpt = esc_html( wp_trim_words( get_the_excerpt( $post ), 20, '...' ) );
        $author  = esc_html( get_the_author_meta( 'display_name', $post->post_author ) );
        $cat     = get_the_category( $post->ID );
        $cat_name = ! empty( $cat ) ? esc_html( $cat[0]->name ) : '';

        $output .= '<article class="ufc-post-card">';

        if ( $image ) {
            $output .= '<div class="ufc-post-image" style="background-image: url(' . esc_url( $image ) . ');">';
        } else {
            $output .= '<div class="ufc-post-image ufc-post-no-image">';
        }
        if ( $cat_name ) {
            $output .= '<span class="ufc-post-cat">' . $cat_name . '</span>';
        }
        $output .= '</div>';

        $output .= '<div class="ufc-post-body">';
        $output .= '  <span class="ufc-post-date"><i class="far fa-calendar"></i> ' . $date . '</span>';
        $output .= '  <h3 class="ufc-post-title"><a href="' . $link . '">' . $title . '</a></h3>';
        $output .= '  <p class="ufc-post-excerpt">' . $excerpt . '</p>';
        $output .= '  <div class="ufc-post-footer">';
        $output .= '    <span class="ufc-post-author"><i class="far fa-user"></i> ' . $author . '</span>';
        $output .= '    <a href="' . $link . '" class="ufc-post-read">' . esc_html__( 'Lire', 'astra-child' ) . ' <i class="fas fa-arrow-right"></i></a>';
        $output .= '  </div>';
        $output .= '</div>';

        $output .= '</article>';
    }

    $output .= '</div>';

    return $output;
}

add_action( 'wp_footer', 'ufc_custom_footer', 5 );
function ufc_custom_footer() {
    ?>
    <footer class="ufc-footer" role="contentinfo">
        <div class="ufc-footer-inner">
            <div class="ufc-footer-col">
                <h4 class="ufc-footer-title"><?php echo esc_html__( 'UFC Community', 'astra-child' ); ?></h4>
                <p><?php echo esc_html__( 'La communauté francophone dédiée aux fans de MMA et UFC. Retrouvez les événements, les actus et rejoignez notre communauté de passionnés.', 'astra-child' ); ?></p>
                <div class="ufc-footer-social">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="ufc-footer-col">
                <h4 class="ufc-footer-title"><?php echo esc_html__( 'Navigation', 'astra-child' ); ?></h4>
                <ul class="ufc-footer-links">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'Accueil', 'astra-child' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/actualites/' ) ); ?>"><?php echo esc_html__( 'Actualités', 'astra-child' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/evenements/' ) ); ?>"><?php echo esc_html__( 'Événements', 'astra-child' ); ?></a></li>
                </ul>
            </div>
            <div class="ufc-footer-col">
                <h4 class="ufc-footer-title"><?php echo esc_html__( 'Mon Espace', 'astra-child' ); ?></h4>
                <ul class="ufc-footer-links">
                    <?php if ( is_user_logged_in() ) : ?>
                        <li><a href="<?php echo esc_url( home_url( '/mon-compte/' ) ); ?>"><?php echo esc_html__( 'Mon Compte', 'astra-child' ); ?></a></li>
                        <li><a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php echo esc_html__( 'Déconnexion', 'astra-child' ); ?></a></li>
                    <?php else : ?>
                        <li><a href="<?php echo esc_url( home_url( '/connexion/' ) ); ?>"><?php echo esc_html__( 'Connexion', 'astra-child' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/inscription/' ) ); ?>"><?php echo esc_html__( 'Inscription', 'astra-child' ); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="ufc-footer-col">
                <h4 class="ufc-footer-title"><?php echo esc_html__( 'Contact', 'astra-child' ); ?></h4>
                <ul class="ufc-footer-contact">
                    <li><i class="fas fa-envelope"></i> <?php echo esc_html__( 'contact@ufc-community.fr', 'astra-child' ); ?></li>
                    <li><i class="fas fa-map-marker-alt"></i> <?php echo esc_html__( 'Paris, France', 'astra-child' ); ?></li>
                    <li><i class="fas fa-clock"></i> <?php echo esc_html__( 'Lun - Ven : 9h - 18h', 'astra-child' ); ?></li>
                </ul>
            </div>
        </div>
        <div class="ufc-footer-bottom">
            <p>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html__( 'UFC Community. Tous droits réservés.', 'astra-child' ); ?> — <?php echo esc_html__( 'Thème Astra Child', 'astra-child' ); ?></p>
        </div>
    </footer>
    <?php
}

add_filter( 'astra_footer_copyright_text', '__return_empty_string' );
add_action( 'wp_dashboard_setup', 'ufc_admin_dashboard_widget' );
function ufc_admin_dashboard_widget() {
    wp_add_dashboard_widget(
        'ufc_dashboard_overview',
        'UFC Community — Vue d\'ensemble',
        'ufc_dashboard_widget_content'
    );
}

function ufc_dashboard_widget_content() {
    $post_count  = wp_count_posts()->publish;
    $user_count  = count_users()['total_users'];
    $event_count = 0;

    if ( function_exists( 'tribe_get_events' ) ) {
        $upcoming = tribe_get_events( array(
            'posts_per_page' => -1,
            'start_date'     => current_time( 'Y-m-d H:i:s' ),
            'eventDisplay'   => 'list',
        ) );
        $event_count = count( $upcoming );
    }

    echo '<div style="display:flex;gap:20px;text-align:center;">';
    echo '<div style="flex:1;padding:15px;background:#1a1a1a;border-radius:8px;border-left:4px solid #d20a0a;">';
    echo '<h3 style="margin:0;color:#d20a0a;font-size:2em;">' . esc_html( $post_count ) . '</h3>';
    echo '<p style="margin:5px 0 0;color:#999;">Articles publiés</p></div>';
    echo '<div style="flex:1;padding:15px;background:#1a1a1a;border-radius:8px;border-left:4px solid #c59e5e;">';
    echo '<h3 style="margin:0;color:#c59e5e;font-size:2em;">' . esc_html( $event_count ) . '</h3>';
    echo '<p style="margin:5px 0 0;color:#999;">Événements à venir</p></div>';
    echo '<div style="flex:1;padding:15px;background:#1a1a1a;border-radius:8px;border-left:4px solid #28a745;">';
    echo '<h3 style="margin:0;color:#28a745;font-size:2em;">' . esc_html( $user_count ) . '</h3>';
    echo '<p style="margin:5px 0 0;color:#999;">Membres inscrits</p></div>';
    echo '</div>';
    echo '<p style="margin-top:15px;color:#666;font-size:12px;">Thème UFC Community — Astra Child | ';
    echo '<a href="' . esc_url( admin_url( '?ufc_reset=1' ) ) . '">Relancer la config auto</a></p>';
}
add_action( 'admin_head', 'ufc_admin_custom_css' );
function ufc_admin_custom_css() {
    echo '<style>
        #ufc_dashboard_overview .inside { background: #111; padding: 20px; border-radius: 8px; }
        #ufc_dashboard_overview h2 { background: #d20a0a; color: white; padding: 10px 15px; }
    </style>';
}

remove_action( 'wp_head', 'wp_generator' );
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}


add_filter( 'astra_page_layout', 'ufc_force_fullwidth_layout' );
function ufc_force_fullwidth_layout( $layout ) {
    // J'ai codé ici : Pas de sidebar sur AUCUNE page du site
    return 'no-sidebar';
}

add_filter( 'astra_get_content_layout', 'ufc_force_plain_container' );
function ufc_force_plain_container( $layout ) {
    // J'ai codé ici : Plain container sur TOUTES les pages (pas de boxed layout)
    return 'plain-container';
}

add_filter( 'wp_nav_menu_objects', 'ufc_remove_membres_from_menu', 10, 2 );
function ufc_remove_membres_from_menu( $items, $args ) {
    $hidden = array( 'membres', 'contact' );
    foreach ( $items as $key => $item ) {
        if ( in_array( strtolower( trim( $item->title ) ), $hidden, true ) ) {
            unset( $items[ $key ] );
        }
    }
    return $items;
}

add_filter( 'template_include', 'ufc_force_event_template', 99 );
function ufc_force_event_template( $template ) {
    if ( is_singular( 'tribe_events' ) ) {
        $custom = get_stylesheet_directory() . '/single-tribe_events.php';
        if ( file_exists( $custom ) ) {
            return $custom;
        }
    }
    return $template;
}


add_action( 'admin_init', 'ufc_fix_um_pages_v2' );
function ufc_fix_um_pages_v2() {
    if ( get_option( 'ufc_um_pages_fixed_v2' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) || ! class_exists( 'UM' ) ) {
        return;
    }

    $um_login_shortcode    = '[ultimatemember form_id="7"]';
    $um_register_shortcode = '[ultimatemember form_id="7"]';

    $login_forms = get_posts( array(
        'post_type'      => 'um_form',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_key'       => '_um_mode',
        'meta_value'     => 'login',
    ) );
    if ( ! empty( $login_forms ) ) {
        $um_login_shortcode = '[ultimatemember form_id="' . $login_forms[0]->ID . '"]';
    }

    $register_forms = get_posts( array(
        'post_type'      => 'um_form',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_key'       => '_um_mode',
        'meta_value'     => 'register',
    ) );
    if ( ! empty( $register_forms ) ) {
        $um_register_shortcode = '[ultimatemember form_id="' . $register_forms[0]->ID . '"]';
    }

    $pages_fix = array(
        'connexion'   => $um_login_shortcode,
        'inscription' => $um_register_shortcode,
        'mon-compte'  => '[ultimatemember_account]',
        'membres'     => '[ultimatemember_directory]',
    );

    foreach ( $pages_fix as $slug => $content ) {
        $page = get_page_by_path( $slug );
        if ( $page ) {
            wp_update_post( array(
                'ID'           => $page->ID,
                'post_content' => $content,
            ) );
        }
    }

    $um_options = get_option( 'um_options', array() );
    $connexion_page   = get_page_by_path( 'connexion' );
    $inscription_page = get_page_by_path( 'inscription' );
    $mon_compte_page  = get_page_by_path( 'mon-compte' );
    $membres_page     = get_page_by_path( 'membres' );

    if ( $connexion_page )   { $um_options['core_login']    = $connexion_page->ID; }
    if ( $inscription_page ) { $um_options['core_register'] = $inscription_page->ID; }
    if ( $mon_compte_page )  { $um_options['core_user']     = $mon_compte_page->ID; }
    if ( $membres_page )     { $um_options['core_members']  = $membres_page->ID; }

    update_option( 'um_options', $um_options );
    update_option( 'ufc_um_pages_fixed_v2', true );
}


add_action( 'admin_init', 'ufc_seed_content' );
function ufc_seed_content() {
    if ( get_option( 'ufc_content_seeded' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }


    $events = array(
        array(
            'title'       => 'UFC 313 : Pereira vs Ankalaev',
            'content'     => '<p>Le champion des mi-lourds Alex Pereira défend sa ceinture face au redoutable Magomed Ankalaev dans un combat très attendu.</p><h3>Carte principale</h3><ul><li><strong>Alex Pereira</strong> vs Magomed Ankalaev — Titre mi-lourd</li><li><strong>Justin Gaethje</strong> vs Dan Hooker — Légers</li><li><strong>Jailton Almeida</strong> vs Derrick Lewis — Lourds</li></ul><p>Ne manquez pas cette soirée explosive au T-Mobile Arena de Las Vegas !</p>',
            'start'       => date( 'Y-m-d', strtotime( '+3 weeks' ) ),
            'end'         => date( 'Y-m-d', strtotime( '+3 weeks' ) ),
            'start_time'  => '20:00:00',
            'end_time'    => '23:59:00',
            'venue'       => 'T-Mobile Arena, Las Vegas',
            'cost'        => 'Gratuit',
        ),
        array(
            'title'       => 'UFC Fight Night : Dariush vs Tsarukyan',
            'content'     => '<p>Une soirée Fight Night palpitante avec un main event chez les légers.</p><h3>Carte principale</h3><ul><li><strong>Beneil Dariush</strong> vs Arman Tsarukyan — Légers</li><li><strong>Ciryl Gane</strong> vs Tai Tuivasa — Lourds</li></ul>',
            'start'       => date( 'Y-m-d', strtotime( '+5 weeks' ) ),
            'end'         => date( 'Y-m-d', strtotime( '+5 weeks' ) ),
            'start_time'  => '19:00:00',
            'end_time'    => '23:00:00',
            'venue'       => 'UFC APEX, Las Vegas',
            'cost'        => 'Gratuit',
        ),
        array(
            'title'       => 'UFC 314 : Makhachev vs Oliveira 2',
            'content'     => '<p>La revanche tant attendue ! Islam Makhachev remet sa ceinture des légers en jeu face à Charles Oliveira.</p><h3>Carte principale</h3><ul><li><strong>Islam Makhachev</strong> vs Charles Oliveira — Titre léger</li><li><strong>Sean O\'Malley</strong> vs Merab Dvalishvili — Titre coq</li></ul><p>Un événement historique au Madison Square Garden !</p>',
            'start'       => date( 'Y-m-d', strtotime( '+7 weeks' ) ),
            'end'         => date( 'Y-m-d', strtotime( '+7 weeks' ) ),
            'start_time'  => '22:00:00',
            'end_time'    => '03:00:00',
            'venue'       => 'Madison Square Garden, New York',
            'cost'        => 'Gratuit',
        ),
        array(
            'title'       => 'UFC Fight Night : Soirée Paris',
            'content'     => '<p>L\'UFC revient à Paris ! Une soirée spéciale avec les meilleurs combattants français et internationaux.</p><h3>Carte principale</h3><ul><li><strong>Nassourdine Imavov</strong> vs Sean Strickland — Moyens</li><li><strong>Ciryl Gane</strong> vs Tom Aspinall — Lourds</li><li><strong>Benoît Saint Denis</strong> vs Renato Moicano — Légers</li></ul>',
            'start'       => date( 'Y-m-d', strtotime( '+10 weeks' ) ),
            'end'         => date( 'Y-m-d', strtotime( '+10 weeks' ) ),
            'start_time'  => '18:00:00',
            'end_time'    => '23:00:00',
            'venue'       => 'Accor Arena, Paris',
            'cost'        => 'Gratuit',
        ),
        array(
            'title'       => 'UFC 315 : Jones vs Aspinall',
            'content'     => '<p>Le combat le plus attendu de la décennie ! Jon Jones affronte Tom Aspinall pour l\'unification du titre des lourds.</p><h3>Carte principale</h3><ul><li><strong>Jon Jones</strong> vs Tom Aspinall — Unification titre lourd</li><li><strong>Ilia Topuria</strong> vs Alexander Volkanovski — Titre plume</li></ul><p>Le plus grand événement de l\'année au Wembley Stadium !</p>',
            'start'       => date( 'Y-m-d', strtotime( '+14 weeks' ) ),
            'end'         => date( 'Y-m-d', strtotime( '+14 weeks' ) ),
            'start_time'  => '21:00:00',
            'end_time'    => '03:00:00',
            'venue'       => 'Wembley Stadium, Londres',
            'cost'        => 'Gratuit',
        ),
    );

    foreach ( $events as $event ) {
        $existing = get_posts( array(
            'post_type'      => 'tribe_events',
            'title'          => $event['title'],
            'post_status'    => 'publish',
            'posts_per_page' => 1,
        ) );
        if ( ! empty( $existing ) ) {
            continue;
        }

        $event_id = wp_insert_post( array(
            'post_title'   => sanitize_text_field( $event['title'] ),
            'post_content' => wp_kses_post( $event['content'] ),
            'post_type'    => 'tribe_events',
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
        ) );

        if ( $event_id && ! is_wp_error( $event_id ) ) {
            $start_datetime = $event['start'] . ' ' . $event['start_time'];
            $end_datetime   = $event['end'] . ' ' . $event['end_time'];
            update_post_meta( $event_id, '_EventStartDate', $start_datetime );
            update_post_meta( $event_id, '_EventEndDate', $end_datetime );
            update_post_meta( $event_id, '_EventStartDateUTC', $start_datetime );
            update_post_meta( $event_id, '_EventEndDateUTC', $end_datetime );
            update_post_meta( $event_id, '_EventDuration', 14400 );
            update_post_meta( $event_id, '_EventCost', $event['cost'] );
            update_post_meta( $event_id, '_EventCurrencySymbol', '€' );
            update_post_meta( $event_id, '_EventCurrencyPosition', 'suffix' );

            if ( function_exists( 'tribe_create_venue' ) ) {
                $venue_id = tribe_create_venue( array(
                    'Venue' => $event['venue'],
                ) );
                if ( $venue_id ) {
                    update_post_meta( $event_id, '_EventVenueID', $venue_id );
                }
            }
        }
    }

    $articles = array(
        array(
            'title'   => 'Pereira confirme son statut de champion dominateur',
            'content' => '<p>Alex "Poatan" Pereira continue de régner sur la division des mi-lourds. Après une série de victoires impressionnantes, le Brésilien est considéré comme l\'un des meilleurs combattants livre pour livre de l\'UFC.</p><p>Son style de combat unique, mêlant kickboxing de haut niveau et une puissance dévastatrice, en fait un adversaire redouté par tous les combattants de la division.</p><p>« Je suis prêt pour tous les challengers », a déclaré Pereira lors de la conférence de presse.</p>',
            'category' => 'Combattants',
        ),
        array(
            'title'   => 'L\'UFC annonce une nouvelle soirée historique à Paris',
            'content' => '<p>L\'Ultimate Fighting Championship revient en France ! Après le succès retentissant des précédentes éditions parisiennes, l\'organisation a confirmé un nouvel événement à l\'Accor Arena.</p><p>Les fans français auront l\'occasion de voir les meilleurs combattants tricolores en action, dont Nassourdine Imavov, Ciryl Gane et Benoît Saint Denis.</p><p>La billetterie sera ouverte prochainement. Restez à l\'écoute pour plus d\'informations !</p>',
            'category' => 'Événements',
        ),
        array(
            'title'   => 'Guide du débutant : Comprendre les règles du MMA',
            'content' => '<p>Le MMA (Mixed Martial Arts) est un sport de combat qui combine plusieurs disciplines. Voici ce que vous devez savoir pour apprécier pleinement les combats.</p><h3>Les règles de base</h3><p>Un combat se déroule dans un octogone et dure généralement 3 rounds de 5 minutes (5 rounds pour les combats de championnat). Un combattant peut gagner par KO, soumission, décision des juges ou arrêt de l\'arbitre.</p><h3>Les catégories de poids</h3><p>L\'UFC compte plusieurs divisions, des poids paille (52 kg) jusqu\'aux poids lourds (120 kg), chacune avec son propre champion.</p>',
            'category' => 'Guides',
        ),
        array(
            'title'   => 'Top 5 des KO les plus spectaculaires de l\'année',
            'content' => '<p>L\'année en cours a été riche en finitions spectaculaires. Retour sur les 5 knockouts les plus impressionnants qui ont marqué les fans du monde entier.</p><p>Des head kicks dévastateurs aux uppercuts surprises, ces moments resteront gravés dans l\'histoire de l\'UFC. Chaque KO a non seulement démontré la puissance brute des combattants, mais aussi leur technique et leur timing parfait.</p>',
            'category' => 'Actualités',
        ),
        array(
            'title'   => 'Interview exclusive : un combattant français raconte son parcours',
            'content' => '<p>Nous avons eu le privilège de rencontrer l\'un des combattants français les plus prometteurs de l\'UFC. Il nous a raconté son parcours, des débuts en club jusqu\'à l\'octogone le plus prestigieux du monde.</p><p>« Le MMA m\'a tout donné. La discipline, le respect, et une famille. Je veux montrer au monde que la France a sa place au sommet », nous a-t-il confié.</p><p>Un témoignage inspirant qui montre que la persévérance et le travail acharné finissent toujours par payer.</p>',
            'category' => 'Interviews',
        ),
    );

    foreach ( $articles as $article ) {
        $existing = get_posts( array(
            'post_type'      => 'post',
            'title'          => $article['title'],
            'post_status'    => 'publish',
            'posts_per_page' => 1,
        ) );
        if ( ! empty( $existing ) ) {
            continue;
        }

        $cat_id = 0;
        $cat = get_term_by( 'name', $article['category'], 'category' );
        if ( $cat ) {
            $cat_id = $cat->term_id;
        } else {
            $new_cat = wp_insert_term( $article['category'], 'category' );
            if ( ! is_wp_error( $new_cat ) ) {
                $cat_id = $new_cat['term_id'];
            }
        }

        $post_id = wp_insert_post( array(
            'post_title'   => sanitize_text_field( $article['title'] ),
            'post_content' => wp_kses_post( $article['content'] ),
            'post_type'    => 'post',
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
            'post_category' => $cat_id ? array( $cat_id ) : array(),
        ) );
    }


    $events_page = get_page_by_path( 'evenements' );
    if ( $events_page ) {
        wp_update_post( array(
            'ID'           => $events_page->ID,
            'post_content' => '',
        ) );
    }

    update_option( 'ufc_content_seeded', true );
}


add_action( 'admin_init', 'ufc_seed_events_v2' );
function ufc_seed_events_v2() {
    if ( get_option( 'ufc_events_seeded_v2' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    // Vérifier que TEC est actif
    if ( ! post_type_exists( 'tribe_events' ) ) {
        return;
    }

    $events = array(
        array(
            'title'      => 'UFC 313 : Pereira vs Ankalaev',
            'content'    => '<p>Le champion des mi-lourds Alex Pereira défend sa ceinture face au redoutable Magomed Ankalaev.</p><h3>Carte principale</h3><ul><li><strong>Alex Pereira</strong> vs Magomed Ankalaev — Titre mi-lourd</li><li><strong>Justin Gaethje</strong> vs Dan Hooker — Légers</li><li><strong>Jailton Almeida</strong> vs Derrick Lewis — Lourds</li></ul>',
            'start'      => '+3 weeks',
            'venue'      => 'T-Mobile Arena, Las Vegas',
        ),
        array(
            'title'      => 'UFC Fight Night : Dariush vs Tsarukyan',
            'content'    => '<p>Une soirée Fight Night chez les légers.</p><h3>Carte principale</h3><ul><li><strong>Beneil Dariush</strong> vs Arman Tsarukyan — Légers</li><li><strong>Ciryl Gane</strong> vs Tai Tuivasa — Lourds</li></ul>',
            'start'      => '+5 weeks',
            'venue'      => 'UFC APEX, Las Vegas',
        ),
        array(
            'title'      => 'UFC 314 : Makhachev vs Oliveira 2',
            'content'    => '<p>La revanche ! Islam Makhachev remet sa ceinture des légers en jeu face à Charles Oliveira.</p><h3>Carte principale</h3><ul><li><strong>Islam Makhachev</strong> vs Charles Oliveira — Titre léger</li><li><strong>Sean O\'Malley</strong> vs Merab Dvalishvili — Titre coq</li></ul>',
            'start'      => '+7 weeks',
            'venue'      => 'Madison Square Garden, New York',
        ),
        array(
            'title'      => 'UFC Fight Night : Soirée Paris',
            'content'    => '<p>L\'UFC revient à Paris avec les meilleurs combattants français !</p><h3>Carte principale</h3><ul><li><strong>Nassourdine Imavov</strong> vs Sean Strickland — Moyens</li><li><strong>Ciryl Gane</strong> vs Tom Aspinall — Lourds</li><li><strong>Benoît Saint Denis</strong> vs Renato Moicano — Légers</li></ul>',
            'start'      => '+10 weeks',
            'venue'      => 'Accor Arena, Paris',
        ),
        array(
            'title'      => 'UFC 315 : Jones vs Aspinall',
            'content'    => '<p>Le combat de la décennie ! Jon Jones affronte Tom Aspinall pour l\'unification du titre des lourds.</p><h3>Carte principale</h3><ul><li><strong>Jon Jones</strong> vs Tom Aspinall — Unification titre lourd</li><li><strong>Ilia Topuria</strong> vs Alexander Volkanovski — Titre plume</li></ul>',
            'start'      => '+14 weeks',
            'venue'      => 'Wembley Stadium, Londres',
        ),
    );

    foreach ( $events as $event_data ) {
        $existing = get_posts( array(
            'post_type'      => 'tribe_events',
            'title'          => $event_data['title'],
            'post_status'    => 'any',
            'posts_per_page' => 1,
        ) );
        if ( ! empty( $existing ) ) {
            continue;
        }

        $start_date = date( 'Y-m-d', strtotime( $event_data['start'] ) );
        $start_dt   = $start_date . ' 20:00:00';
        $end_dt     = $start_date . ' 23:59:00';

        if ( function_exists( 'tribe_create_event' ) ) {
            $event_id = tribe_create_event( array(
                'post_title'     => $event_data['title'],
                'post_content'   => $event_data['content'],
                'post_status'    => 'publish',
                'EventStartDate' => $start_date,
                'EventStartTime' => '20:00:00',
                'EventEndDate'   => $start_date,
                'EventEndTime'   => '23:59:00',
                'EventTimezone'  => 'Europe/Paris',
                'EventCost'      => 'Gratuit',
                'Venue'          => array( 'Venue' => $event_data['venue'] ),
            ) );
        } else {
            $event_id = wp_insert_post( array(
                'post_title'   => sanitize_text_field( $event_data['title'] ),
                'post_content' => wp_kses_post( $event_data['content'] ),
                'post_type'    => 'tribe_events',
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
            ) );

            if ( $event_id && ! is_wp_error( $event_id ) ) {
                update_post_meta( $event_id, '_EventStartDate', $start_dt );
                update_post_meta( $event_id, '_EventEndDate', $end_dt );
                update_post_meta( $event_id, '_EventStartDateUTC', $start_dt );
                update_post_meta( $event_id, '_EventEndDateUTC', $end_dt );
                update_post_meta( $event_id, '_EventTimezone', 'Europe/Paris' );
                update_post_meta( $event_id, '_EventTimezoneAbbr', 'CET' );
                update_post_meta( $event_id, '_EventDuration', 14340 );
                update_post_meta( $event_id, '_EventCost', 'Gratuit' );
                update_post_meta( $event_id, '_EventCurrencySymbol', '€' );
                update_post_meta( $event_id, '_EventCurrencyPosition', 'suffix' );

                if ( function_exists( 'tribe_create_venue' ) ) {
                    $venue_id = tribe_create_venue( array( 'Venue' => $event_data['venue'] ) );
                    if ( $venue_id ) {
                        update_post_meta( $event_id, '_EventVenueID', $venue_id );
                    }
                }
            }
        }
    }

    update_option( 'ufc_events_seeded_v2', true );
}

add_action( 'init', 'ufc_flush_rewrite_once', 99 );
function ufc_flush_rewrite_once() {
    if ( get_option( 'ufc_rewrite_flushed_v3' ) ) {
        return;
    }
    flush_rewrite_rules();
    update_option( 'ufc_rewrite_flushed_v3', true );
}


add_action( 'login_init', 'ufc_redirect_login_to_um' );
function ufc_redirect_login_to_um() {
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        return;
    }

    $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
    if ( in_array( $action, array( 'logout', 'lostpassword', 'rp', 'resetpass', 'postpass', 'confirmaction' ), true ) ) {
        return;
    }

    if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_GET['interim-login'] ) ) {
        return;
    }

    if ( ! class_exists( 'UM' ) ) {
        return;
    }

    $connexion_page = get_page_by_path( 'connexion' );
    if ( $connexion_page ) {
        $redirect_url = get_permalink( $connexion_page );

        if ( ! empty( $_GET['redirect_to'] ) ) {
            $redirect_url = add_query_arg( 'redirect_to', urlencode( $_GET['redirect_to'] ), $redirect_url );
        }

        wp_safe_redirect( esc_url_raw( $redirect_url ) );
        exit;
    }
}


add_action( 'login_init', 'ufc_redirect_register_to_um' );
function ufc_redirect_register_to_um() {
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        return;
    }
    $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
    if ( $action !== 'register' ) {
        return;
    }
    if ( ! class_exists( 'UM' ) ) {
        return;
    }
    $inscription_page = get_page_by_path( 'inscription' );
    if ( $inscription_page ) {
        wp_safe_redirect( esc_url_raw( get_permalink( $inscription_page ) ) );
        exit;
    }
}

add_filter( 'um_account_page_default_tab_url', 'ufc_fix_account_url', 10, 1 );
function ufc_fix_account_url( $url ) {
    $page = get_page_by_path( 'mon-compte' );
    if ( $page ) {
        return get_permalink( $page );
    }
    return $url;
}


add_filter( 'the_content', 'ufc_append_events_cta_to_posts', 20 );
function ufc_append_events_cta_to_posts( $content ) {
    if ( ! is_singular( 'post' ) || is_admin() || doing_filter( 'get_the_excerpt' ) ) {
        return $content;
    }

    if ( ! function_exists( 'tribe_get_events' ) ) {
        return $content;
    }

    $events = tribe_get_events( array(
        'posts_per_page' => 3,
        'start_date'     => current_time( 'Y-m-d H:i:s' ),
        'eventDisplay'   => 'list',
    ) );

    if ( empty( $events ) ) {
        return $content;
    }

    $cta = '<div class="ufc-article-events-cta">';
    $cta .= '<h3><i class="fas fa-fire"></i> ' . esc_html__( 'Prochains événements UFC', 'astra-child' ) . '</h3>';
    $cta .= '<p>' . esc_html__( 'Ne manquez pas les prochains combats — réservez vos places !', 'astra-child' ) . '</p>';
    $cta .= '<div class="ufc-cta-events-grid">';

    foreach ( $events as $event ) {
        $title = esc_html( get_the_title( $event ) );
        $link  = esc_url( get_permalink( $event ) );
        $date  = esc_html( tribe_get_start_date( $event, false, 'j M Y' ) );
        $venue = esc_html( tribe_get_venue( $event->ID ) );

        $cta .= '<div class="ufc-mini-event">';
        $cta .= '<a href="' . $link . '">';
        $cta .= '<div class="ufc-mini-event-title">' . $title . '</div>';
        $cta .= '<div class="ufc-mini-event-date"><i class="far fa-calendar"></i> ' . $date;
        if ( $venue ) {
            $cta .= ' &mdash; ' . $venue;
        }
        $cta .= '</div>';
        $cta .= '</a>';
        $cta .= '</div>';
    }

    $cta .= '</div>';
    $cta .= '<a href="' . esc_url( home_url( '/evenements/' ) ) . '" class="ufc-hero-btn ufc-hero-btn-secondary" style="margin-top:10px;">';
    $cta .= '<i class="fas fa-ticket-alt"></i> ' . esc_html__( 'Voir tous les événements', 'astra-child' );
    $cta .= '</a>';
    $cta .= '</div>';

    return $content . $cta;
}


add_action( 'admin_init', 'ufc_fix_um_directory_v3' );
function ufc_fix_um_directory_v3() {
    if ( get_option( 'ufc_um_directory_fixed_v4' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) || ! class_exists( 'UM' ) ) {
        return;
    }

    $directories = get_posts( array(
        'post_type'      => 'um_directory',
        'posts_per_page' => -1,
        'post_status'    => 'any',
    ) );

    $dir_id = 0;

    if ( ! empty( $directories ) ) {
        $dir_id = $directories[0]->ID;

        if ( get_post_status( $dir_id ) !== 'publish' ) {
            wp_update_post( array(
                'ID'          => $dir_id,
                'post_status' => 'publish',
            ) );
        }
    } else {
        $dir_id = wp_insert_post( array(
            'post_title'  => 'Members Directory',
            'post_type'   => 'um_directory',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ) );
    }

    if ( $dir_id && ! is_wp_error( $dir_id ) ) {
        update_post_meta( $dir_id, '_um_mode', 'directory' );
        update_post_meta( $dir_id, '_um_is_default', '1' );
        update_post_meta( $dir_id, '_um_roles', array() );
        update_post_meta( $dir_id, '_um_search_fields', array() );
        update_post_meta( $dir_id, '_um_search_filters', array() );
        update_post_meta( $dir_id, '_um_sortby', 'user_registered' );
        update_post_meta( $dir_id, '_um_sortby_custom_order', 'DESC' );
        update_post_meta( $dir_id, '_um_view_types', array( 'grid' ) );
        update_post_meta( $dir_id, '_um_directory_token', wp_generate_password( 16, false ) );
    }

    $membres_page = get_page_by_path( 'membres' );
    if ( $membres_page && $dir_id ) {
        wp_update_post( array(
            'ID'           => $membres_page->ID,
            'post_content' => '[ultimatemember form_id="' . $dir_id . '"]',
        ) );
    }

    $um_options = get_option( 'um_options', array() );

    if ( $membres_page ) {
        $um_options['core_members'] = $membres_page->ID;
    }
    $connexion_page = get_page_by_path( 'connexion' );
    if ( $connexion_page ) {
        $um_options['core_login'] = $connexion_page->ID;
    }
    $inscription_page = get_page_by_path( 'inscription' );
    if ( $inscription_page ) {
        $um_options['core_register'] = $inscription_page->ID;
    }
    $mon_compte_page = get_page_by_path( 'mon-compte' );
    if ( $mon_compte_page ) {
        $um_options['core_user'] = $mon_compte_page->ID;
    }

    $um_options['members_page'] = 1;
    $um_options['use_gravatars'] = 1;

    update_option( 'um_options', $um_options );
    update_option( 'ufc_um_directory_fixed_v4', true );
}


add_action( 'admin_init', 'ufc_add_rsvp_to_events_v3', 99 );
function ufc_add_rsvp_to_events_v3() {
    if ( get_option( 'ufc_rsvp_added_v5' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( ! class_exists( 'Tribe__Tickets__RSVP' ) ) {
        return;
    }

    $rsvp = Tribe__Tickets__RSVP::get_instance();
    $post_type = $rsvp->ticket_object; // 'tribe_rsvp_tickets'

    $tiers = array(
        array(
            'name'        => 'Place Or — Cage Side',
            'description' => 'Place premium au plus près de l\'octogone. Vue imprenable sur les combats, accès VIP, boissons offertes.',
            'capacity'    => 20,
            'emoji'       => '🥇',
        ),
        array(
            'name'        => 'Place Argent — Tribune Haute',
            'description' => 'Excellente visibilité depuis les tribunes hautes. Bon rapport qualité/vue, ambiance garantie.',
            'capacity'    => 50,
            'emoji'       => '🥈',
        ),
        array(
            'name'        => 'Place Bronze — Gradins',
            'description' => 'Place standard dans les gradins. Profitez de l\'ambiance électrique de l\'arène à prix accessible.',
            'capacity'    => 100,
            'emoji'       => '🥉',
        ),
    );

    $events = get_posts( array(
        'post_type'      => 'tribe_events',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    foreach ( $events as $event ) {
        $existing = get_posts( array(
            'post_type'      => $post_type,
            'posts_per_page' => 5,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'   => '_tribe_rsvp_for_event',
                    'value' => $event->ID,
                ),
            ),
        ) );

        if ( count( $existing ) >= 3 ) {
            continue;
        }

        foreach ( $existing as $old_ticket ) {
            wp_delete_post( $old_ticket->ID, true );
        }

        $event_start = get_post_meta( $event->ID, '_EventStartDate', true );
        $total_capacity = 0;

        foreach ( $tiers as $tier ) {
            $ticket_id = wp_insert_post( array(
                'post_title'   => $tier['name'],
                'post_content' => $tier['description'],
                'post_excerpt' => $tier['description'],
                'post_type'    => $post_type,
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
            ) );

            if ( ! $ticket_id || is_wp_error( $ticket_id ) ) {
                continue;
            }

            update_post_meta( $ticket_id, '_tribe_rsvp_for_event', $event->ID );
            update_post_meta( $ticket_id, '_price', '0' );
            update_post_meta( $ticket_id, '_manage_stock', 'yes' );
            update_post_meta( $ticket_id, '_stock', (string) $tier['capacity'] );
            update_post_meta( $ticket_id, 'total_sales', '0' );
            update_post_meta( $ticket_id, '_tribe_ticket_show_description', 'yes' );
            update_post_meta( $ticket_id, '_tribe_ticket_show_not_going', 'yes' );
            update_post_meta( $ticket_id, '_tribe_ticket_capacity', (string) $tier['capacity'] );
            update_post_meta( $ticket_id, '_capacity', (string) $tier['capacity'] );

            update_post_meta( $ticket_id, '_ticket_start_date', current_time( 'Y-m-d H:i:s' ) );
            if ( $event_start ) {
                update_post_meta( $ticket_id, '_ticket_end_date', $event_start );
            }

            $total_capacity += $tier['capacity'];
        }

        update_post_meta( $event->ID, '_tribe_default_ticket_provider', 'Tribe__Tickets__RSVP' );
        update_post_meta( $event->ID, '_tribe_ticket_capacity', (string) $total_capacity );
        update_post_meta( $event->ID, '_ticket_capacity', (string) $total_capacity );
    }

    update_option( 'ufc_rsvp_added_v5', true );
}


add_action( 'admin_init', 'ufc_create_ticket_pages' );
function ufc_create_ticket_pages() {
    if ( get_option( 'ufc_ticket_pages_created_v1' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( ! class_exists( 'Tribe__Tickets__Main' ) ) {
        return;
    }

    $checkout_page = get_page_by_path( 'commande-tickets' );
    if ( ! $checkout_page ) {
        $checkout_id = wp_insert_post( array(
            'post_title'   => 'Commande Tickets',
            'post_name'    => 'commande-tickets',
            'post_content' => '[tec_tickets_checkout]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id(),
        ) );
    } else {
        $checkout_id = $checkout_page->ID;
        wp_update_post( array(
            'ID'           => $checkout_id,
            'post_content' => '[tec_tickets_checkout]',
        ) );
    }

    $success_page = get_page_by_path( 'confirmation-tickets' );
    if ( ! $success_page ) {
        $success_id = wp_insert_post( array(
            'post_title'   => 'Confirmation Tickets',
            'post_name'    => 'confirmation-tickets',
            'post_content' => '[tec_tickets_success]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id(),
        ) );
    } else {
        $success_id = $success_page->ID;
        wp_update_post( array(
            'ID'           => $success_id,
            'post_content' => '[tec_tickets_success]',
        ) );
    }

    if ( ! empty( $checkout_id ) && ! is_wp_error( $checkout_id ) ) {
        update_option( 'tec_tickets_commerce_checkout_page', $checkout_id );
        $tec_options = get_option( 'tribe_tickets_settings', array() );
        $tec_options['tickets-commerce-checkout-page'] = $checkout_id;
        if ( ! empty( $success_id ) && ! is_wp_error( $success_id ) ) {
            $tec_options['tickets-commerce-success-page'] = $success_id;
            update_option( 'tec_tickets_commerce_success_page', $success_id );
        }
        update_option( 'tribe_tickets_settings', $tec_options );
    }

    update_option( 'ufc_ticket_pages_created_v1', true );
}


add_filter( 'comments_open', '__return_false' );
add_filter( 'pings_open', '__return_false' );
add_action( 'admin_init', 'ufc_disable_comments_admin' );
function ufc_disable_comments_admin() {
    remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
}
add_action( 'admin_menu', 'ufc_remove_comments_menu' );
function ufc_remove_comments_menu() {
    remove_menu_page( 'edit-comments.php' );
}


add_action( 'add_meta_boxes', 'ufc_add_rsvp_tiers_meta_box' );
function ufc_add_rsvp_tiers_meta_box() {
    add_meta_box(
        'ufc_rsvp_tiers',
        '🎟️ Configuration des Places UFC (Or / Argent / Bronze)',
        'ufc_render_rsvp_tiers_meta_box',
        'tribe_events',
        'normal',
        'high'
    );
}


function ufc_render_rsvp_tiers_meta_box( $post ) {
    wp_nonce_field( 'ufc_rsvp_tiers_save', 'ufc_rsvp_tiers_nonce' );

    if ( ! class_exists( 'Tribe__Tickets__RSVP' ) ) {
        echo '<p style="color:#d20a0a;">⚠️ Le plugin Event Tickets doit être activé.</p>';
        return;
    }

    $rsvp       = Tribe__Tickets__RSVP::get_instance();
    $post_type  = $rsvp->ticket_object;

    $existing_tickets = get_posts( array(
        'post_type'      => $post_type,
        'posts_per_page' => 10,
        'post_status'    => 'any',
        'meta_query'     => array(
            array(
                'key'   => '_tribe_rsvp_for_event',
                'value' => $post->ID,
            ),
        ),
        'orderby'        => 'date',
        'order'          => 'ASC',
    ) );

    $tier_data = array();
    foreach ( $existing_tickets as $ticket ) {
        $level = get_post_meta( $ticket->ID, '_ufc_tier_level', true );
        if ( $level ) {
            $tier_data[ $level ] = $ticket;
        }
    }

    $default_tiers = array(
        'or' => array(
            'label'    => '🥇 Place Or — Cage Side',
            'name'     => 'Place Or — Cage Side',
            'desc'     => 'Place premium au plus près de l\'octogone. Vue imprenable sur les combats, accès VIP, boissons offertes.',
            'capacity' => 20,
            'color'    => '#c59e5e',
        ),
        'argent' => array(
            'label'    => '🥈 Place Argent — Tribune Haute',
            'name'     => 'Place Argent — Tribune Haute',
            'desc'     => 'Excellente visibilité depuis les tribunes hautes. Bon rapport qualité/vue, ambiance garantie.',
            'capacity' => 50,
            'color'    => '#a0a0a0',
        ),
        'bronze' => array(
            'label'    => '🥉 Place Bronze — Gradins',
            'name'     => 'Place Bronze — Gradins',
            'desc'     => 'Place standard dans les gradins. Profitez de l\'ambiance électrique de l\'arène à prix accessible.',
            'capacity' => 100,
            'color'    => '#cd7f32',
        ),
    );

    echo '<style>
        .ufc-tier-box { border: 2px solid #333; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #1a1a1a; }
        .ufc-tier-box.active { border-color: var(--tier-color, #c59e5e); }
        .ufc-tier-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .ufc-tier-header label { font-size: 15px; font-weight: 700; color: #f0f0f0; cursor: pointer; }
        .ufc-tier-header input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; }
        .ufc-tier-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .ufc-tier-fields.hidden { display: none; }
        .ufc-tier-field { display: flex; flex-direction: column; gap: 4px; }
        .ufc-tier-field.full { grid-column: 1 / -1; }
        .ufc-tier-field label { font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; }
        .ufc-tier-field input, .ufc-tier-field textarea { background: #111; border: 1px solid #333; color: #f0f0f0; padding: 8px 12px; border-radius: 4px; font-size: 14px; }
        .ufc-tier-field textarea { min-height: 60px; resize: vertical; }
        .ufc-tier-field input:focus, .ufc-tier-field textarea:focus { border-color: #c59e5e; outline: none; }
        .ufc-tier-status { font-size: 12px; color: #666; margin-top: 8px; }
        .ufc-tier-status .exists { color: #28a745; }
        .ufc-tier-status .new { color: #c59e5e; }
        .ufc-tiers-info { background: #111; border: 1px solid #333; border-radius: 6px; padding: 12px; margin-bottom: 15px; color: #999; font-size: 13px; line-height: 1.6; }
        .ufc-tiers-info strong { color: #c59e5e; }
    </style>';

    echo '<div class="ufc-tiers-info">';
    echo '<strong>Configuration des places par événement</strong><br>';
    echo 'Cochez les niveaux de places à proposer pour cet événement. ';
    echo 'Vous pouvez personnaliser le nom, la description et la capacité de chaque niveau. ';
    echo 'Les modifications sont sauvegardées avec l\'événement.';
    echo '</div>';

    foreach ( $default_tiers as $key => $defaults ) {
        $ticket   = isset( $tier_data[ $key ] ) ? $tier_data[ $key ] : null;
        $is_active = ( $ticket !== null );
        $name     = $ticket ? $ticket->post_title : $defaults['name'];
        $desc     = $ticket ? $ticket->post_content : $defaults['desc'];
        $capacity = $ticket ? get_post_meta( $ticket->ID, '_tribe_ticket_capacity', true ) : $defaults['capacity'];
        $sales    = $ticket ? get_post_meta( $ticket->ID, 'total_sales', true ) : '0';

        $active_class = $is_active ? ' active' : '';

        echo '<div class="ufc-tier-box' . esc_attr( $active_class ) . '" style="--tier-color: ' . esc_attr( $defaults['color'] ) . ';">';

        echo '<div class="ufc-tier-header">';
        echo '<input type="checkbox" name="ufc_tier[' . esc_attr( $key ) . '][active]" value="1" id="ufc_tier_' . esc_attr( $key ) . '" ' . checked( $is_active, true, false ) . ' onchange="this.closest(\'.ufc-tier-box\').querySelector(\'.ufc-tier-fields\').classList.toggle(\'hidden\', !this.checked); this.closest(\'.ufc-tier-box\').classList.toggle(\'active\', this.checked);">';
        echo '<label for="ufc_tier_' . esc_attr( $key ) . '">' . esc_html( $defaults['label'] ) . '</label>';
        echo '</div>';

        $hidden_class = $is_active ? '' : ' hidden';
        echo '<div class="ufc-tier-fields' . esc_attr( $hidden_class ) . '">';

        echo '<div class="ufc-tier-field">';
        echo '<label>Nom du niveau</label>';
        echo '<input type="text" name="ufc_tier[' . esc_attr( $key ) . '][name]" value="' . esc_attr( $name ) . '">';
        echo '</div>';

        echo '<div class="ufc-tier-field">';
        echo '<label>Capacité (places)</label>';
        echo '<input type="number" name="ufc_tier[' . esc_attr( $key ) . '][capacity]" value="' . esc_attr( $capacity ) . '" min="1" max="10000">';
        echo '</div>';

        echo '<div class="ufc-tier-field full">';
        echo '<label>Description</label>';
        echo '<textarea name="ufc_tier[' . esc_attr( $key ) . '][desc]">' . esc_textarea( $desc ) . '</textarea>';
        echo '</div>';

        if ( $ticket ) {
            echo '<div class="ufc-tier-status"><span class="exists">✓ Ticket RSVP existant</span> — ' . esc_html( $sales ) . ' réservation(s)</div>';
        } else {
            echo '<div class="ufc-tier-status"><span class="new">● Sera créé à la sauvegarde</span></div>';
        }

        echo '</div>';
        echo '</div>';
    }
}


add_action( 'save_post_tribe_events', 'ufc_save_rsvp_tiers_meta_box', 20 );
function ufc_save_rsvp_tiers_meta_box( $post_id ) {
    if ( ! isset( $_POST['ufc_rsvp_tiers_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['ufc_rsvp_tiers_nonce'], 'ufc_rsvp_tiers_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( ! class_exists( 'Tribe__Tickets__RSVP' ) ) {
        return;
    }

    $rsvp      = Tribe__Tickets__RSVP::get_instance();
    $post_type = $rsvp->ticket_object;
    $tiers     = isset( $_POST['ufc_tier'] ) ? $_POST['ufc_tier'] : array();
    $tier_keys = array( 'or', 'argent', 'bronze' );

    $existing_tickets = get_posts( array(
        'post_type'      => $post_type,
        'posts_per_page' => 10,
        'post_status'    => 'any',
        'meta_query'     => array(
            array(
                'key'   => '_tribe_rsvp_for_event',
                'value' => $post_id,
            ),
        ),
    ) );

    $existing_by_level = array();
    foreach ( $existing_tickets as $ticket ) {
        $level = get_post_meta( $ticket->ID, '_ufc_tier_level', true );
        if ( $level ) {
            $existing_by_level[ $level ] = $ticket;
        }
    }

    $total_capacity = 0;
    $event_start    = get_post_meta( $post_id, '_EventStartDate', true );

    foreach ( $tier_keys as $key ) {
        $tier_config = isset( $tiers[ $key ] ) ? $tiers[ $key ] : array();
        $is_active   = ! empty( $tier_config['active'] );
        $ticket      = isset( $existing_by_level[ $key ] ) ? $existing_by_level[ $key ] : null;

        if ( $is_active ) {
            $name     = sanitize_text_field( isset( $tier_config['name'] ) ? $tier_config['name'] : '' );
            $desc     = wp_kses_post( isset( $tier_config['desc'] ) ? $tier_config['desc'] : '' );
            $capacity = absint( isset( $tier_config['capacity'] ) ? $tier_config['capacity'] : 100 );
            if ( $capacity < 1 ) { $capacity = 1; }

            if ( $ticket ) {
                wp_update_post( array(
                    'ID'           => $ticket->ID,
                    'post_title'   => $name,
                    'post_content' => $desc,
                    'post_excerpt' => $desc,
                    'post_status'  => 'publish',
                ) );
                $ticket_id = $ticket->ID;
            } else {
                $ticket_id = wp_insert_post( array(
                    'post_title'   => $name,
                    'post_content' => $desc,
                    'post_excerpt' => $desc,
                    'post_type'    => $post_type,
                    'post_status'  => 'publish',
                    'post_author'  => get_current_user_id(),
                ) );
            }

            if ( $ticket_id && ! is_wp_error( $ticket_id ) ) {
                update_post_meta( $ticket_id, '_tribe_rsvp_for_event', $post_id );
                update_post_meta( $ticket_id, '_ufc_tier_level', $key );
                update_post_meta( $ticket_id, '_price', '0' );
                update_post_meta( $ticket_id, '_manage_stock', 'yes' );
                update_post_meta( $ticket_id, '_stock', (string) $capacity );
                update_post_meta( $ticket_id, '_tribe_ticket_show_description', 'yes' );
                update_post_meta( $ticket_id, '_tribe_ticket_show_not_going', 'yes' );
                update_post_meta( $ticket_id, '_tribe_ticket_capacity', (string) $capacity );
                update_post_meta( $ticket_id, '_capacity', (string) $capacity );
                update_post_meta( $ticket_id, '_ticket_start_date', current_time( 'Y-m-d H:i:s' ) );
                if ( $event_start ) {
                    update_post_meta( $ticket_id, '_ticket_end_date', $event_start );
                }
                $total_capacity += $capacity;
            }

        } elseif ( $ticket ) {
            wp_delete_post( $ticket->ID, true );
        }
    }
    if ( $total_capacity > 0 ) {
        update_post_meta( $post_id, '_tribe_default_ticket_provider', 'Tribe__Tickets__RSVP' );
        update_post_meta( $post_id, '_tribe_ticket_capacity', (string) $total_capacity );
        update_post_meta( $post_id, '_ticket_capacity', (string) $total_capacity );
    }
}


add_action( 'admin_init', 'ufc_migrate_tier_levels' );
function ufc_migrate_tier_levels() {
    if ( get_option( 'ufc_tier_levels_migrated_v1' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) || ! class_exists( 'Tribe__Tickets__RSVP' ) ) {
        return;
    }

    $rsvp = Tribe__Tickets__RSVP::get_instance();
    $tickets = get_posts( array(
        'post_type'      => $rsvp->ticket_object,
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'meta_query'     => array(
            array(
                'key'     => '_ufc_tier_level',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ) );

    foreach ( $tickets as $ticket ) {
        $title = strtolower( $ticket->post_title );
        if ( strpos( $title, 'or' ) !== false && strpos( $title, 'cage' ) !== false ) {
            update_post_meta( $ticket->ID, '_ufc_tier_level', 'or' );
        } elseif ( strpos( $title, 'argent' ) !== false || strpos( $title, 'tribune' ) !== false ) {
            update_post_meta( $ticket->ID, '_ufc_tier_level', 'argent' );
        } elseif ( strpos( $title, 'bronze' ) !== false || strpos( $title, 'gradins' ) !== false ) {
            update_post_meta( $ticket->ID, '_ufc_tier_level', 'bronze' );
        }
    }

    update_option( 'ufc_tier_levels_migrated_v1', true );
}


add_filter( 'astra_get_content_layout', 'ufc_um_pages_plain_container' );
function ufc_um_pages_plain_container( $layout ) {
    $um_slugs = array( 'connexion', 'inscription', 'mon-compte', 'membres' );
    if ( is_page( $um_slugs ) ) {
        return 'plain-container';
    }
    return $layout;
}

add_action( 'admin_init', 'ufc_attach_placeholder_images' );
function ufc_attach_placeholder_images() {
    if ( get_option( 'ufc_images_attached_v1' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $events = get_posts( array(
        'post_type'      => 'tribe_events',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_thumbnail_id',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key'     => '_thumbnail_id',
                'value'   => '',
            ),
        ),
    ) );

    $articles = get_posts( array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_thumbnail_id',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key'     => '_thumbnail_id',
                'value'   => '',
            ),
        ),
    ) );

    
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $colors = array( '#d20a0a', '#a00808', '#c59e5e', '#8b0000', '#1a1a1a' );

    foreach ( array_merge( $events, $articles ) as $index => $post ) {
        $color = $colors[ $index % count( $colors ) ];
        $title = get_the_title( $post );

        if ( function_exists( 'imagecreatetruecolor' ) ) {
            $img = imagecreatetruecolor( 800, 450 );
            $r = hexdec( substr( $color, 1, 2 ) );
            $g = hexdec( substr( $color, 3, 2 ) );
            $b = hexdec( substr( $color, 5, 2 ) );
            $bg = imagecolorallocate( $img, $r, $g, $b );
            imagefill( $img, 0, 0, $bg );
            $white = imagecolorallocate( $img, 255, 255, 255 );
            imagestring( $img, 5, 50, 200, substr( $title, 0, 40 ), $white );

            $upload_dir = wp_upload_dir();
            $filename = 'ufc-placeholder-' . $post->ID . '.jpg';
            $filepath = $upload_dir['path'] . '/' . $filename;
            imagejpeg( $img, $filepath, 85 );
            imagedestroy( $img );

            $attachment = array(
                'post_mime_type' => 'image/jpeg',
                'post_title'     => 'UFC ' . $title,
                'post_content'   => '',
                'post_status'    => 'inherit',
            );

            $attach_id = wp_insert_attachment( $attachment, $filepath, $post->ID );
            if ( $attach_id && ! is_wp_error( $attach_id ) ) {
                $metadata = wp_generate_attachment_metadata( $attach_id, $filepath );
                wp_update_attachment_metadata( $attach_id, $metadata );
                set_post_thumbnail( $post->ID, $attach_id );
            }
        }
    }

    update_option( 'ufc_images_attached_v1', true );
}
