<div class="entry-summary trimvia-legacy-entry-summary">
<?php if ( has_post_thumbnail() ) : ?>
<a class="trimvia-legacy-featured-image" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail(); ?></a>
<?php endif; ?>
<?php the_excerpt(); ?>
<?php if ( is_search() ) { ?>
<div class="entry-links trimvia-legacy-entry-links"><?php wp_link_pages(); ?></div>
<?php } ?>
</div>