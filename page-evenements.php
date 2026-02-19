<?php
get_header();
?>

<div class="ufc-events-page">

    <div class="ufc-page-header">
        <h1 class="ufc-page-title"><?php echo esc_html__( 'Événements', 'astra-child' ); ?></h1>
        <p class="ufc-page-subtitle"><?php echo esc_html__( 'Découvrez les prochains combats et réservez vos places', 'astra-child' ); ?></p>
    </div>

    <div class="ufc-section">
        <?php
        if ( function_exists( 'tribe_get_events' ) ) {
            $upcoming_events = tribe_get_events( array(
                'posts_per_page' => 20,
                'start_date'     => current_time( 'Y-m-d H:i:s' ),
                'eventDisplay'   => 'list',
            ) );

            if ( ! empty( $upcoming_events ) ) {
                echo do_shortcode( '[ufc_next_events count="20"]' );
            } else {
                $all_events = tribe_get_events( array(
                    'posts_per_page' => 20,
                    'eventDisplay'   => 'custom',
                    'order'          => 'DESC',
                ) );

                if ( ! empty( $all_events ) ) {
                    echo '<div class="ufc-events-grid">';
                    foreach ( $all_events as $index => $event ) {
                        $title = esc_html( get_the_title( $event ) );
                        $link  = esc_url( get_permalink( $event ) );
                        $date  = esc_html( tribe_get_start_date( $event, false, 'd M Y' ) );
                        $time  = esc_html( tribe_get_start_date( $event, false, 'H:i' ) );
                        $venue = esc_html( tribe_get_venue( $event->ID ) );
                        $image = get_the_post_thumbnail_url( $event, 'ufc-card-thumb' );

                        if ( ! $image ) {
                            $post_content = get_post_field( 'post_content', $event->ID );
                            if ( preg_match( '/<img[^>]+src=["\']([^"\']+)/i', $post_content, $matches ) ) {
                                $image = $matches[1];
                            }
                        }

                        $main_class = ( $index === 0 ) ? ' ufc-main-event' : '';

                        echo '<article class="ufc-event-card' . esc_attr( $main_class ) . '">';

                        if ( $image ) {
                            echo '<div class="ufc-card-image" style="background-image: url(' . esc_url( $image ) . ');">';
                        } else {
                            echo '<div class="ufc-card-image ufc-card-no-image">';
                        }
                        echo '<div class="ufc-card-overlay">';
                        if ( $index === 0 ) {
                            echo '<span class="ufc-badge-main">' . esc_html__( 'MAIN EVENT', 'astra-child' ) . '</span>';
                        }
                        echo '<span class="ufc-card-date"><i class="far fa-calendar"></i> ' . $date . '</span>';
                        echo '</div></div>';

                        echo '<div class="ufc-card-body">';
                        echo '<h3 class="ufc-card-title">' . $title . '</h3>';
                        if ( $venue ) {
                            echo '<p class="ufc-card-venue"><i class="fas fa-map-marker-alt"></i> ' . $venue . '</p>';
                        }
                        echo '<p class="ufc-card-time"><i class="far fa-clock"></i> ' . $time . '</p>';
                        echo '<a href="' . $link . '" class="ufc-card-btn"><i class="fas fa-ticket-alt"></i> ' . esc_html__( 'VOIR DÉTAILS & TICKETS', 'astra-child' ) . '</a>';
                        echo '</div></article>';
                    }
                    echo '</div>';
                } else {
                    echo '<p class="ufc-notice">' . esc_html__( 'Aucun événement disponible pour le moment.', 'astra-child' ) . '</p>';
                }
            }
        } else {
            echo '<p class="ufc-notice">' . esc_html__( 'Le plugin The Events Calendar est requis pour afficher les événements.', 'astra-child' ) . '</p>';
        }
        ?>
    </div>

</div>

<?php get_footer(); ?>
