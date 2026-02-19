<?php

add_filter( 'tribe_tickets_filter_registration_block', '__return_empty_string', 999 );
if ( class_exists( 'Tribe__Tickets__RSVP' ) ) {
    remove_filter( 'the_content', array( Tribe__Tickets__RSVP::get_instance(), 'front_end_tickets_form_in_content' ), 25 );
    remove_filter( 'the_content', array( Tribe__Tickets__RSVP::get_instance(), 'front_end_tickets_form' ), 25 );
}
if ( class_exists( 'Tribe__Tickets__Main' ) ) {
    remove_filter( 'the_content', array( Tribe__Tickets__Main::instance(), 'front_end_tickets_form_in_content' ), 25 );
}

get_header();
?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<div class="ufc-single-event">

    <a href="<?php echo esc_url( home_url( '/evenements/' ) ); ?>" class="ufc-back-link">
        <i class="fas fa-arrow-left"></i>
        <?php echo esc_html__( 'Retour aux événements', 'astra-child' ); ?>
    </a>

    <?php
    $event_image = get_the_post_thumbnail_url( get_the_ID(), 'ufc-hero' );
    if ( ! $event_image ) {
        $raw_content = get_post_field( 'post_content', get_the_ID() );
        if ( preg_match( '/<img[^>]+src=["\']([^"\']+)/i', $raw_content, $img_matches ) ) {
            $event_image = $img_matches[1];
        }
    }
    ?>
    <div class="ufc-event-hero ufc-animate"
         <?php if ( $event_image ) : ?>
            style="background-image: url(<?php echo esc_url( $event_image ); ?>);"
         <?php endif; ?>>
        <div class="ufc-event-hero-overlay"></div>
        <div class="ufc-event-hero-content">
            <h1 class="ufc-event-hero-title"><?php echo esc_html( get_the_title() ); ?></h1>
        </div>
    </div>

    <div class="ufc-event-meta ufc-animate">
        <?php $event_date = tribe_get_start_date( null, false, 'l j F Y' ); if ( $event_date ) : ?>
        <div class="ufc-event-meta-item">
            <i class="far fa-calendar-alt"></i>
            <div>
                <span class="ufc-event-meta-label"><?php echo esc_html__( 'Date', 'astra-child' ); ?></span>
                <span class="ufc-event-meta-value"><?php echo esc_html( $event_date ); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php
        $event_time_start = tribe_get_start_date( null, false, 'H:i' );
        $event_time_end   = tribe_get_end_date( null, false, 'H:i' );
        if ( $event_time_start ) : ?>
        <div class="ufc-event-meta-item">
            <i class="far fa-clock"></i>
            <div>
                <span class="ufc-event-meta-label"><?php echo esc_html__( 'Heure', 'astra-child' ); ?></span>
                <span class="ufc-event-meta-value">
                    <?php echo esc_html( $event_time_start ); ?>
                    <?php if ( $event_time_end ) : ?> - <?php echo esc_html( $event_time_end ); ?><?php endif; ?>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <?php $venue_name = tribe_get_venue(); if ( $venue_name ) : ?>
        <div class="ufc-event-meta-item">
            <i class="fas fa-map-marker-alt"></i>
            <div>
                <span class="ufc-event-meta-label"><?php echo esc_html__( 'Lieu', 'astra-child' ); ?></span>
                <span class="ufc-event-meta-value"><?php echo esc_html( $venue_name ); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php $venue_address = tribe_get_full_address(); if ( $venue_address && $venue_address !== $venue_name ) : ?>
        <div class="ufc-event-meta-item">
            <i class="fas fa-location-dot"></i>
            <div>
                <span class="ufc-event-meta-label"><?php echo esc_html__( 'Adresse', 'astra-child' ); ?></span>
                <span class="ufc-event-meta-value"><?php echo esc_html( wp_strip_all_tags( $venue_address ) ); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php $event_cost = tribe_get_cost( null, true ); if ( $event_cost ) : ?>
        <div class="ufc-event-meta-item">
            <i class="fas fa-ticket-alt"></i>
            <div>
                <span class="ufc-event-meta-label"><?php echo esc_html__( 'Prix', 'astra-child' ); ?></span>
                <span class="ufc-event-meta-value"><?php echo esc_html( $event_cost ); ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="ufc-event-content ufc-animate">
        <h2><?php echo esc_html__( 'Description', 'astra-child' ); ?></h2>
        <?php the_content(); ?>
    </div>

    <div class="ufc-event-tickets ufc-animate">
        <h3><i class="fas fa-ticket-alt"></i> <?php echo esc_html__( 'Réservation & Tickets', 'astra-child' ); ?></h3>
        <?php
        $rsvp_shown = false;

        if ( class_exists( 'Tribe__Tickets__RSVP' ) ) {
            $rsvp = Tribe__Tickets__RSVP::get_instance();

            if ( method_exists( $rsvp, 'front_end_tickets_form' ) ) {
                ob_start();
                $rsvp->front_end_tickets_form( '' );
                $output = ob_get_clean();
                if ( ! empty( trim( $output ) ) ) {
                    echo $output;
                    $rsvp_shown = true;
                }
            }
        }

        if ( ! $rsvp_shown ) {
            echo '<p class="ufc-notice">' . esc_html__( 'Le système de réservation sera bientôt disponible.', 'astra-child' ) . '</p>';
        }
        ?>
    </div>

    <div style="text-align: center; margin: 40px 0 60px;" class="ufc-animate">
        <a href="<?php echo esc_url( home_url( '/evenements/' ) ); ?>" class="ufc-hero-btn ufc-hero-btn-secondary">
            <i class="fas fa-calendar"></i>
            <?php echo esc_html__( 'Voir tous les événements', 'astra-child' ); ?>
        </a>
    </div>

</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
