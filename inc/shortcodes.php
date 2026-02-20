<?php

// --- Shortcode : [ufc_next_events count="5"] ---
add_shortcode( 'ufc_next_events', 'ufc_shortcode_next_events' );
function ufc_shortcode_next_events( $atts ) {
    $atts = shortcode_atts( array( 'count' => 5 ), $atts, 'ufc_next_events' );
    $count = absint( $atts['count'] );

    if ( ! function_exists( 'tribe_get_events' ) ) {
        return '<p class="ufc-notice">' . esc_html__( 'Le plugin The Events Calendar est requis.', 'astra-child' ) . '</p>';
    }

    // =========================================================================
    // C'EST ICI — Selection manuelle des evenements en accueil
    // On recupere d'abord les evenements marques "Mettre en avant" via la meta
    // _ufc_featured_home, puis on complete avec les plus recents jusqu'au count.
    // Cela permet a l'admin de choisir quels evenements apparaissent en priorite.
    // =========================================================================
    $featured_events = tribe_get_events( array(
        'posts_per_page' => $count,
        'start_date'     => current_time( 'Y-m-d H:i:s' ),
        'eventDisplay'   => 'list',
        'meta_key'       => '_ufc_featured_home',
        'meta_value'     => '1',
    ) );

    $featured_ids = wp_list_pluck( $featured_events, 'ID' );
    $remaining    = $count - count( $featured_events );

    if ( $remaining > 0 ) {
        $other_events = tribe_get_events( array(
            'posts_per_page' => $remaining,
            'start_date'     => current_time( 'Y-m-d H:i:s' ),
            'eventDisplay'   => 'list',
            'post__not_in'   => $featured_ids,
        ) );
        $events = array_merge( $featured_events, $other_events );
    } else {
        $events = $featured_events;
    }
    // =========================================================================

    if ( empty( $events ) ) {
        return '<p class="ufc-notice">' . esc_html__( 'Aucun événement à venir.', 'astra-child' ) . '</p>';
    }

    $output = '<div class="ufc-events-grid">';
    foreach ( $events as $index => $event ) {
        $title = esc_html( get_the_title( $event ) );
        $link  = esc_url( get_permalink( $event ) );
        $date  = esc_html( tribe_get_start_date( $event, false, 'd M Y' ) );
        $time  = esc_html( tribe_get_start_date( $event, false, 'H:i' ) );
        $venue = esc_html( tribe_get_venue( $event->ID ) );
        $image = get_the_post_thumbnail_url( $event, 'ufc-card-thumb' );
        if ( ! $image ) {
            preg_match( '/<img[^>]+src=["\']([^"\']+)/i', get_post_field( 'post_content', $event->ID ), $m );
            if ( ! empty( $m[1] ) ) { $image = $m[1]; }
        }
        $excerpt = esc_html( wp_trim_words( get_the_excerpt( $event ), 15, '...' ) );

        $output .= '<article class="ufc-event-card' . ( $index === 0 ? ' ufc-main-event' : '' ) . '">';
        $output .= $image ? '<div class="ufc-card-image" style="background-image: url(' . esc_url( $image ) . ');">' : '<div class="ufc-card-image ufc-card-no-image">';
        $output .= '<div class="ufc-card-overlay">';
        if ( $index === 0 ) { $output .= '<span class="ufc-badge-main">' . esc_html__( 'MAIN EVENT', 'astra-child' ) . '</span>'; }
        $output .= '<span class="ufc-card-date"><i class="far fa-calendar"></i> ' . $date . '</span>';
        $output .= '</div></div>';
        $output .= '<div class="ufc-card-body">';
        $output .= '<h3 class="ufc-card-title">' . $title . '</h3>';
        if ( $venue ) { $output .= '<p class="ufc-card-venue"><i class="fas fa-map-marker-alt"></i> ' . $venue . '</p>'; }
        $output .= '<p class="ufc-card-time"><i class="far fa-clock"></i> ' . $time . '</p>';
        if ( $excerpt ) { $output .= '<p class="ufc-card-excerpt">' . $excerpt . '</p>'; }
        $output .= '<a href="' . $link . '" class="ufc-card-btn"><i class="fas fa-ticket-alt"></i> ' . esc_html__( 'VOIR DÉTAILS & TICKETS', 'astra-child' ) . '</a>';
        $output .= '</div></article>';
    }
    $output .= '</div>';
    return $output;
}

// --- Shortcode : [ufc_latest_posts count="3"] ---
add_shortcode( 'ufc_latest_posts', 'ufc_shortcode_latest_posts' );
function ufc_shortcode_latest_posts( $atts ) {
    $atts = shortcode_atts( array( 'count' => 3 ), $atts, 'ufc_latest_posts' );
    $count = absint( $atts['count'] );

    // =========================================================================
    // C'EST ICI — Selection manuelle des articles en accueil
    // Meme logique que pour les evenements : on recupere d'abord les articles
    // marques "Mettre en avant" (_ufc_featured_home = 1), puis on complete
    // avec les plus recents pour atteindre le nombre demande (count).
    // =========================================================================
    $featured_posts = get_posts( array(
        'post_type'      => 'post',
        'posts_per_page' => $count,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_key'       => '_ufc_featured_home',
        'meta_value'     => '1',
    ) );

    $featured_ids = wp_list_pluck( $featured_posts, 'ID' );
    $remaining    = $count - count( $featured_posts );

    if ( $remaining > 0 ) {
        $other_posts = get_posts( array(
            'post_type'      => 'post',
            'posts_per_page' => $remaining,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post__not_in'   => $featured_ids,
        ) );
        $posts = array_merge( $featured_posts, $other_posts );
    } else {
        $posts = $featured_posts;
    }
    // =========================================================================

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
            preg_match( '/<img[^>]+src=["\']([^"\']+)/i', get_post_field( 'post_content', $post->ID ), $m );
            if ( ! empty( $m[1] ) ) { $image = $m[1]; }
        }
        $excerpt  = esc_html( wp_trim_words( get_the_excerpt( $post ), 20, '...' ) );
        $author   = esc_html( get_the_author_meta( 'display_name', $post->post_author ) );
        $cat      = get_the_category( $post->ID );
        $cat_name = ! empty( $cat ) ? esc_html( $cat[0]->name ) : '';

        $output .= '<article class="ufc-post-card">';
        $output .= $image ? '<div class="ufc-post-image" style="background-image: url(' . esc_url( $image ) . ');">' : '<div class="ufc-post-image ufc-post-no-image">';
        if ( $cat_name ) { $output .= '<span class="ufc-post-cat">' . $cat_name . '</span>'; }
        $output .= '</div>';
        $output .= '<div class="ufc-post-body">';
        $output .= '<span class="ufc-post-date"><i class="far fa-calendar"></i> ' . $date . '</span>';
        $output .= '<h3 class="ufc-post-title"><a href="' . $link . '">' . $title . '</a></h3>';
        $output .= '<p class="ufc-post-excerpt">' . $excerpt . '</p>';
        $output .= '<div class="ufc-post-footer">';
        $output .= '<span class="ufc-post-author"><i class="far fa-user"></i> ' . $author . '</span>';
        $output .= '<a href="' . $link . '" class="ufc-post-read">' . esc_html__( 'Lire', 'astra-child' ) . ' <i class="fas fa-arrow-right"></i></a>';
        $output .= '</div></div></article>';
    }
    $output .= '</div>';
    return $output;
}

// --- CTA evenements en bas de chaque article ---
add_filter( 'the_content', 'ufc_append_events_cta_to_posts', 20 );
function ufc_append_events_cta_to_posts( $content ) {
    if ( ! is_singular( 'post' ) || is_admin() || doing_filter( 'get_the_excerpt' ) ) { return $content; }
    if ( ! function_exists( 'tribe_get_events' ) ) { return $content; }

    $events = tribe_get_events( array( 'posts_per_page' => 3, 'start_date' => current_time( 'Y-m-d H:i:s' ), 'eventDisplay' => 'list' ) );
    if ( empty( $events ) ) { return $content; }

    $cta = '<div class="ufc-article-events-cta">';
    $cta .= '<h3><i class="fas fa-fire"></i> ' . esc_html__( 'Prochains événements UFC', 'astra-child' ) . '</h3>';
    $cta .= '<p>' . esc_html__( 'Ne manquez pas les prochains combats — réservez vos places !', 'astra-child' ) . '</p>';
    $cta .= '<div class="ufc-cta-events-grid">';
    foreach ( $events as $event ) {
        $cta .= '<div class="ufc-mini-event"><a href="' . esc_url( get_permalink( $event ) ) . '">';
        $cta .= '<div class="ufc-mini-event-title">' . esc_html( get_the_title( $event ) ) . '</div>';
        $cta .= '<div class="ufc-mini-event-date"><i class="far fa-calendar"></i> ' . esc_html( tribe_get_start_date( $event, false, 'j M Y' ) );
        $venue = tribe_get_venue( $event->ID );
        if ( $venue ) { $cta .= ' &mdash; ' . esc_html( $venue ); }
        $cta .= '</div></a></div>';
    }
    $cta .= '</div>';
    $cta .= '<a href="' . esc_url( home_url( '/evenements/' ) ) . '" class="ufc-hero-btn ufc-hero-btn-secondary" style="margin-top:10px;"><i class="fas fa-ticket-alt"></i> ' . esc_html__( 'Voir tous les événements', 'astra-child' ) . '</a></div>';

    return $content . $cta;
}
