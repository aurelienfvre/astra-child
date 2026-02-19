<?php
if ( ! isset( $post ) || ! $post ) return;

$blog_title   = esc_html( get_the_title( $post ) );
$blog_link    = esc_url( get_permalink( $post ) );
$blog_image   = get_the_post_thumbnail_url( $post, 'ufc-card-thumb' );
if ( ! $blog_image ) {
    preg_match( '/<img[^>]+src=["\']([^"\']+)/i', get_post_field( 'post_content', $post->ID ), $m );
    if ( ! empty( $m[1] ) ) { $blog_image = $m[1]; }
}
$blog_date    = esc_html( get_the_date( 'j M Y', $post ) );
$blog_excerpt = esc_html( wp_trim_words( get_the_excerpt( $post ), 20, '...' ) );
$blog_author  = esc_html( get_the_author_meta( 'display_name', $post->post_author ) );
$blog_cats    = get_the_category( $post->ID );
$blog_cat     = ! empty( $blog_cats ) ? esc_html( $blog_cats[0]->name ) : '';
?>

<article class="ufc-post-card">
    <?php if ( $blog_image ) : ?>
        <div class="ufc-post-image" style="background-image: url(<?php echo esc_url( $blog_image ); ?>);">
    <?php else : ?>
        <div class="ufc-post-image ufc-post-no-image">
    <?php endif; ?>
        <?php if ( $blog_cat ) : ?>
            <span class="ufc-post-cat"><?php echo $blog_cat; ?></span>
        <?php endif; ?>
    </div>

    <div class="ufc-post-body">
        <span class="ufc-post-date"><i class="far fa-calendar"></i> <?php echo $blog_date; ?></span>
        <h3 class="ufc-post-title">
            <a href="<?php echo $blog_link; ?>"><?php echo $blog_title; ?></a>
        </h3>
        <p class="ufc-post-excerpt"><?php echo $blog_excerpt; ?></p>
        <div class="ufc-post-footer">
            <span class="ufc-post-author"><i class="far fa-user"></i> <?php echo $blog_author; ?></span>
            <a href="<?php echo $blog_link; ?>" class="ufc-post-read">
                <?php echo esc_html__( 'Lire', 'astra-child' ); ?> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</article>
