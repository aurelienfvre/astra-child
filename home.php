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
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php
                    $title   = esc_html( get_the_title() );
                    $link    = esc_url( get_permalink() );
                    $date    = esc_html( get_the_date( 'j M Y' ) );
                    $image   = get_the_post_thumbnail_url( get_the_ID(), 'ufc-card-thumb' );

                    if ( ! $image ) {
                        $post_content = get_post_field( 'post_content', get_the_ID() );
                        if ( preg_match( '/<img[^>]+src=["\']([^"\']+)/i', $post_content, $matches ) ) {
                            $image = $matches[1];
                        }
                    }

                    $excerpt = esc_html( wp_trim_words( get_the_excerpt(), 20, '...' ) );
                    $author  = esc_html( get_the_author() );
                    $cat     = get_the_category();
                    $cat_name = ! empty( $cat ) ? esc_html( $cat[0]->name ) : '';
                    ?>
                    <article class="ufc-post-card">
                        <?php if ( $image ) : ?>
                            <div class="ufc-post-image" style="background-image: url(<?php echo esc_url( $image ); ?>);">
                        <?php else : ?>
                            <div class="ufc-post-image ufc-post-no-image">
                        <?php endif; ?>
                            <?php if ( $cat_name ) : ?>
                                <span class="ufc-post-cat"><?php echo $cat_name; ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="ufc-post-body">
                            <span class="ufc-post-date"><i class="far fa-calendar"></i> <?php echo $date; ?></span>
                            <h3 class="ufc-post-title"><a href="<?php echo $link; ?>"><?php echo $title; ?></a></h3>
                            <p class="ufc-post-excerpt"><?php echo $excerpt; ?></p>
                            <div class="ufc-post-footer">
                                <span class="ufc-post-author"><i class="far fa-user"></i> <?php echo $author; ?></span>
                                <a href="<?php echo $link; ?>" class="ufc-post-read"><?php echo esc_html__( 'Lire', 'astra-child' ); ?> <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
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
