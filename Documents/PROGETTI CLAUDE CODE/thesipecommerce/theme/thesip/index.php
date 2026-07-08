<?php
/**
 * Default template fallback.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="container max-w-7xl mx-auto px-4 py-12">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article class="mb-8">
				<h1 class="text-3xl font-bold mb-4"><?php the_title(); ?></h1>
				<div class="prose"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<div class="rounded-lg border border-dashed p-12 text-center">
			<h1 class="text-2xl font-bold mb-2">Tema Pharmanow attivo · Fase 1.1</h1>
			<p class="text-muted-foreground mb-6">
				Lo scaffold del tema funziona. Le pagine reali vengono costruite nelle fasi successive di Piano 1.
			</p>
			<p class="text-sm">
				Header → Fase 1.2 · Footer → Fase 1.3 · Home → Fase 1.4 · Mini-cart → Fase 1.6 · Cart → Fase 1.7 · Checkout → Fase 1.9
			</p>
		</div>
	<?php endif; ?>
</div>

<?php
get_footer();
