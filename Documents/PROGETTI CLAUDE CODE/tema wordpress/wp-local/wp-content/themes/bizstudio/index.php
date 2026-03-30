<?php
/**
 * Default template — fallback for all pages.
 *
 * @package BizStudio
 */

get_header();
?>

<main class="biz-main">
	<div class="biz-container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'biz-article' ); ?>>
					<h2 class="biz-article__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<div class="biz-article__content">
						<?php the_excerpt(); ?>
					</div>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nessun contenuto trovato.', 'bizstudio' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
