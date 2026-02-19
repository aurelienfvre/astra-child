<?php
get_header();
?>

<div class="ufc-blog-page">

    <div class="ufc-page-header">
        <h1 class="ufc-page-title"><?php echo esc_html__( 'Actualit&eacute;s', 'astra-child' ); ?></h1>
        <p class="ufc-page-subtitle"><?php echo esc_html__( 'Toutes les news MMA et UFC', 'astra-child' ); ?></p>
    </div>

    <div class="ufc-section">
        <?php if ( have_posts() ) : ?>
            <div class="ufc-posts-grid">
                <?php while ( have_posts() ) : the_post();
                    $post = get_post();
                    get_template_part( 'template-parts/content-blog-card' );
                endwhile; ?>
            </div>

            <div class="ufc-pagination">
                <?php
                the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => '<i class="fas fa-chevron-left"></i> ' . esc_html__( 'Pr&eacute;c&eacute;dent', 'astra-child' ),
                    'next_text' => esc_html__( 'Suivant', 'astra-child' ) . ' <i class="fas fa-chevron-right"></i>',
                ) );
                ?>
            </div>
        <?php else : ?>
            <p class="ufc-notice"><?php echo esc_html__( 'Aucun article pour le moment.', 'astra-child' ); ?></p>
        <?php endif; ?>
    </div>

</div>

<?php get_footer(); ?>
