<?php
if ( ! isset( $post ) || ! $post ) return;

$card_title   = esc_html( get_the_title( $post ) );
$card_link    = esc_url( get_permalink( $post ) );
$card_image   = get_the_post_thumbnail_url( $post, 'ufc-card-thumb' );
$card_date    = function_exists( 'tribe_get_start_date' ) ? esc_html( tribe_get_start_date( $post, false, 'd M Y' ) ) : '';
$card_time    = function_exists( 'tribe_get_start_date' ) ? esc_html( tribe_get_start_date( $post, false, 'H:i' ) ) : '';
$card_venue   = function_exists( 'tribe_get_venue' ) ? esc_html( tribe_get_venue( $post->ID ) ) : '';
$card_excerpt = esc_html( wp_trim_words( get_the_excerpt( $post ), 15, '...' ) );
?>

<article class="ufc-event-card">
    <?php if ( $card_image ) : ?>
        <div class="ufc-card-image" style="background-image: url(<?php echo esc_url( $card_image ); ?>);">
    <?php else : ?>
        <div class="ufc-card-image ufc-card-no-image">
    <?php endif; ?>
        <div class="ufc-card-overlay">
            <?php if ( $card_date ) : ?>
                <span class="ufc-card-date"><i class="far fa-calendar"></i> <?php echo $card_date; ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="ufc-card-body">
        <h3 class="ufc-card-title"><?php echo $card_title; ?></h3>
        <?php if ( $card_venue ) : ?>
            <p class="ufc-card-venue"><i class="fas fa-map-marker-alt"></i> <?php echo $card_venue; ?></p>
        <?php endif; ?>
        <?php if ( $card_time ) : ?>
            <p class="ufc-card-time"><i class="far fa-clock"></i> <?php echo $card_time; ?></p>
        <?php endif; ?>
        <?php if ( $card_excerpt ) : ?>
            <p class="ufc-card-excerpt"><?php echo $card_excerpt; ?></p>
        <?php endif; ?>
        <a href="<?php echo $card_link; ?>" class="ufc-card-btn">
            <i class="fas fa-ticket-alt"></i> <?php echo esc_html__( 'VOIR DÉTAILS & TICKETS', 'astra-child' ); ?>
        </a>
    </div>
</article>
