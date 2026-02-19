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
            echo do_shortcode( '[ufc_next_events count="20"]' );
        } else {
            echo '<p class="ufc-notice">' . esc_html__( 'Le plugin The Events Calendar est requis pour afficher les événements.', 'astra-child' ) . '</p>';
        }
        ?>
    </div>

</div>

<?php get_footer(); ?>
