<?php
get_header();
?>

<section class="ufc-hero" role="banner">
    <div class="ufc-hero-content ufc-animate">
        <p class="ufc-hero-subtitle"><?php esc_html_e( 'Bienvenue sur', 'astra-child' ); ?></p>
        <h1 class="ufc-hero-title">
            <span class="ufc-text-red"><?php esc_html_e( 'UFC', 'astra-child' ); ?></span>
            <?php esc_html_e( 'Community', 'astra-child' ); ?>
        </h1>
        <p class="ufc-hero-description"><?php esc_html_e( 'La communauté francophone des passionnés de MMA et UFC. Suivez les événements, découvrez les actus et rejoignez des milliers de fans.', 'astra-child' ); ?></p>
        <div class="ufc-hero-actions">
            <a href="<?php echo esc_url( home_url( '/evenements/' ) ); ?>" class="ufc-hero-btn ufc-hero-btn-primary">
                <i class="fas fa-calendar-alt"></i> <?php esc_html_e( 'Voir les événements', 'astra-child' ); ?>
            </a>
            <a href="#ufc-upcoming-events" class="ufc-hero-btn ufc-hero-btn-secondary">
                <i class="fas fa-fire"></i> <?php esc_html_e( 'Prochains combats', 'astra-child' ); ?>
            </a>
        </div>
    </div>
</section>

<section id="ufc-upcoming-events" class="ufc-section">
    <div class="ufc-section-header ufc-animate">
        <h2 class="ufc-section-title"><?php esc_html_e( 'Prochains Événements', 'astra-child' ); ?></h2>
        <p class="ufc-section-subtitle"><?php esc_html_e( 'Ne manquez aucun combat — réservez vos places dès maintenant', 'astra-child' ); ?></p>
    </div>
    <div class="ufc-animate"><?php echo do_shortcode( '[ufc_next_events count="5"]' ); ?></div>
    <div style="text-align: center; margin-top: 40px;" class="ufc-animate">
        <a href="<?php echo esc_url( home_url( '/evenements/' ) ); ?>" class="ufc-hero-btn ufc-hero-btn-secondary">
            <i class="fas fa-calendar"></i> <?php esc_html_e( 'Voir tous les événements', 'astra-child' ); ?>
        </a>
    </div>
</section>

<section class="ufc-section">
    <div class="ufc-section-header ufc-animate">
        <h2 class="ufc-section-title"><?php esc_html_e( 'Dernières Actualités', 'astra-child' ); ?></h2>
        <p class="ufc-section-subtitle"><?php esc_html_e( 'Restez informé des dernières news MMA et UFC', 'astra-child' ); ?></p>
    </div>
    <div class="ufc-animate"><?php echo do_shortcode( '[ufc_latest_posts count="3"]' ); ?></div>
    <div style="text-align: center; margin-top: 40px;" class="ufc-animate">
        <a href="<?php echo esc_url( home_url( '/actualites/' ) ); ?>" class="ufc-hero-btn ufc-hero-btn-secondary">
            <i class="fas fa-newspaper"></i> <?php esc_html_e( 'Toutes les actus', 'astra-child' ); ?>
        </a>
    </div>
</section>

<section class="ufc-cta-section ufc-animate">
    <h2 class="ufc-cta-title"><?php esc_html_e( 'Rejoignez la communauté UFC', 'astra-child' ); ?></h2>
    <p class="ufc-cta-text"><?php esc_html_e( 'Créez votre compte gratuitement, participez aux discussions, inscrivez-vous aux événements et rejoignez des milliers de passionnés de MMA.', 'astra-child' ); ?></p>
    <?php
    if ( is_user_logged_in() ) :
        $cta_url  = home_url( '/mon-compte/' );
        $cta_text = __( 'Mon Compte', 'astra-child' );
        $cta_icon = 'fas fa-user-cog';
    else :
        $cta_url  = wp_registration_url();
        if ( class_exists( 'UM' ) ) {
            $rp = get_page_by_path( 'inscription' );
            if ( $rp ) { $cta_url = get_permalink( $rp ); }
        }
        $cta_text = __( 'S\'inscrire gratuitement', 'astra-child' );
        $cta_icon = 'fas fa-user-plus';
    endif;
    ?>
    <a href="<?php echo esc_url( $cta_url ); ?>" class="ufc-cta-btn">
        <i class="<?php echo esc_attr( $cta_icon ); ?>"></i> <?php echo esc_html( $cta_text ); ?>
    </a>
</section>

<?php get_footer(); ?>
