<?php
/**
 * Template Name: The SIP · Chi Siamo
 * Template Post Type: page
 *
 * Porting da components/shop/about/AboutPage.tsx (Next).
 * Statico — niente content editor; il copy vive nel template (come nel React).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$pn_settings = pn_shop_settings();

$pn_why_choose = array(
	array(
		'icon'  => 'sparkles',
		'title' => __( 'Arte e scienza insieme', 'pharmanow' ),
		'desc'  => __( 'Ogni carta unisce il rigore di due biologi marini alla mano di 25 illustratori: divulgazione bella da guardare e giusta da leggere.', 'pharmanow' ),
	),
	array(
		'icon'  => 'users',
		'title' => __( '25 illustratori', 'pharmanow' ),
		'desc'  => __( "Un collettivo di artisti ha dato vita alle creature del mare, ognuno con il proprio stile e la propria firma.", 'pharmanow' ),
	),
	array(
		'icon'  => 'package',
		'title' => __( '31 carte collezionabili', 'pharmanow' ),
		'desc'  => __( '30 carte più una speciale illustrata da Willy Guasti "Zoosparkle": un set da collezionare, scambiare e studiare.', 'pharmanow' ),
	),
	array(
		'icon'  => 'zap',
		'title' => __( 'Spedizione 24/48h', 'pharmanow' ),
		'desc'  => __( 'Prepariamo e spediamo i tuoi ordini in tempi rapidi, con consegna a mano gratuita a Napoli.', 'pharmanow' ),
	),
	array(
		'icon'  => 'heart',
		'title' => __( 'Divulgazione accessibile', 'pharmanow' ),
		'desc'  => __( "Sul retro di ogni carta Andrea, bambina curiosa, ti accompagna nella profondità del mare con parole semplici.", 'pharmanow' ),
	),
);

$pn_welfare = array(
	__( '165 sostenitori su Kickstarter', 'pharmanow' ),
	__( '€5.885 raccolti dalla community', 'pharmanow' ),
	__( '31 carte illustrate, 3 temi', 'pharmanow' ),
	__( '25 artisti e 2 biologi marini', 'pharmanow' ),
);

$pn_stats = array(
	array(
		'value' => '31',
		'label' => __( 'Carte illustrate', 'pharmanow' ),
	),
	array(
		'value' => '25',
		'label' => __( 'Illustratori', 'pharmanow' ),
	),
	array(
		'value' => '3',
		'label' => __( 'Temi da collezionare', 'pharmanow' ),
	),
	array(
		'value' => '165',
		'label' => __( 'Sostenitori Kickstarter', 'pharmanow' ),
	),
);
?>

<div class="bg-gray-50 min-h-screen">

	<?php
	// Hero — porting bg-gradient with logo card right side.
	?>
	<div class="bg-gradient-to-r from-[#008692] to-[#6AD6E3] py-12 sm:py-16 lg:py-20">
		<div class="max-w-[1280px] mx-auto px-4">
			<nav class="flex items-center gap-2 text-sm mb-4" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmanow' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-white/70 hover:text-white transition-colors">
					<?php esc_html_e( 'Home', 'pharmanow' ); ?>
				</a>
				<?php pn_icon( 'chevron-right', array( 'class' => 'w-4 h-4 text-white/50' ) ); ?>
				<span class="text-white"><?php esc_html_e( 'Chi Siamo', 'pharmanow' ); ?></span>
			</nav>
			<div class="grid lg:grid-cols-2 gap-8 items-center">
				<div>
					<h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4"><?php esc_html_e( 'Chi Siamo', 'pharmanow' ); ?></h1>
					<p class="text-xl sm:text-2xl text-white font-medium mb-4">
						<?php esc_html_e( 'The Sea In your Pocket: il mare in tasca, una carta alla volta.', 'pharmanow' ); ?>
					</p>
					<p class="text-white/90 text-base sm:text-lg leading-relaxed">
						<?php
						printf(
							/* translators: %s = nomi fondatori in grassetto */
							wp_kses_post( __( 'The SIP nasce a Napoli dall\'idea di %s, biologi marini e comunicatori scientifici. Un set di 31 flashcard illustrate da 25 artisti racconta la vita del mare tra interazioni, ambienti e adattamenti. Un progetto dove arte e scienza si incontrano per far innamorare grandi e piccoli della biologia marina.', 'pharmanow' ) ),
							'<strong class="font-semibold">Manuel e Giuliano</strong>'
						);
						?>
					</p>
				</div>
				<div class="hidden lg:flex justify-center">
					<div class="relative w-80 h-80">
						<div class="absolute inset-0 bg-white/20 rounded-full blur-3xl"></div>
						<div class="relative bg-white rounded-3xl p-4 shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-500">
							<img src="<?php echo esc_url( pn_asset( 'images/thesip/biologi.jpg' ) ); ?>" alt="<?php esc_attr_e( 'Manuel e Giuliano, i biologi marini di The SIP', 'pharmanow' ); ?>" width="320" height="320" class="w-full rounded-2xl object-cover aspect-square">
							<div class="mt-4 text-center">
								<p class="text-gray-600 font-medium"><?php esc_html_e( 'Nato a Napoli · finanziato su Kickstarter', 'pharmanow' ); ?></p>
								<div class="flex justify-center gap-1 mt-2">
									<?php for ( $i = 0; $i < 5; $i++ ) : ?>
										<?php pn_icon( 'star', array( 'class' => 'w-5 h-5 text-yellow-400 fill-yellow-400' ) ); ?>
									<?php endfor; ?>
								</div>
								<p class="text-sm text-gray-500 mt-1"><?php esc_html_e( '165 sostenitori · €5.885 raccolti', 'pharmanow' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php // Why choose us. ?>
	<section class="py-12 sm:py-16 lg:py-20">
		<div class="max-w-[1280px] mx-auto px-4">
			<div class="text-center mb-12">
				<h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4"><?php esc_html_e( 'Perché The SIP?', 'pharmanow' ); ?></h2>
				<p class="text-gray-600 max-w-2xl mx-auto">
					<?php esc_html_e( 'Un modo nuovo di raccontare il mare: bello da collezionare, giusto da studiare', 'pharmanow' ); ?>
				</p>
			</div>
			<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
				<?php foreach ( $pn_why_choose as $pn_card ) : ?>
					<div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow duration-300 border border-gray-100">
						<div class="w-14 h-14 bg-gradient-to-r from-[#0B8894] to-[#43CCB1] rounded-xl flex items-center justify-center text-white mb-4">
							<?php pn_icon( $pn_card['icon'], array( 'class' => 'w-8 h-8' ) ); ?>
						</div>
						<h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo esc_html( $pn_card['title'] ); ?></h3>
						<p class="text-gray-600 text-sm leading-relaxed"><?php echo esc_html( $pn_card['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php // Coverage Italia. ?>
	<section class="py-12 sm:py-16 lg:py-20 bg-white">
		<div class="max-w-[1280px] mx-auto px-4">
			<div class="grid lg:grid-cols-2 gap-12 items-center">
				<div>
					<h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">
						<?php esc_html_e( 'Tre temi per esplorare il mare', 'pharmanow' ); ?>
					</h2>
					<p class="text-gray-600 mb-6 leading-relaxed">
						<?php esc_html_e( 'Le carte sono organizzate in tre grandi temi, così collezionarle diventa anche un modo per capire come funziona la vita sott\'acqua: dalle relazioni tra le specie ai luoghi che abitano, fino alle strategie con cui sopravvivono.', 'pharmanow' ); ?>
					</p>
					<ul class="space-y-3 mb-6">
						<?php
						$pn_coverage = array(
							__( 'Interazioni: simbiosi, predazione e alleanze tra specie', 'pharmanow' ),
							__( 'Ambienti: dalla superficie agli abissi più profondi', 'pharmanow' ),
							__( 'Adattamenti: mimetismo, bioluminescenza e superpoteri del mare', 'pharmanow' ),
						);
						foreach ( $pn_coverage as $pn_line ) :
							?>
							<li class="flex items-center gap-3">
								<div class="w-8 h-8 bg-[#0B8894]/10 rounded-full flex items-center justify-center">
									<?php pn_icon( 'check', array( 'class' => 'w-5 h-5 text-[#0B8894]' ) ); ?>
								</div>
								<span class="text-gray-700"><?php echo esc_html( $pn_line ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
					<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/negozio' ) ); ?>" class="inline-flex items-center gap-2 text-[#0B8894] font-semibold hover:underline">
						<?php esc_html_e( 'Scopri le carte', 'pharmanow' ); ?>
						<?php pn_icon( 'arrow-right', array( 'class' => 'w-4 h-4' ) ); ?>
					</a>
				</div>
				<div class="relative">
					<div class="bg-gradient-to-br from-[#0B8894]/10 to-[#43CCB1]/10 rounded-3xl p-8 flex items-center justify-center min-h-[400px]">
						<div class="relative w-full max-w-[350px]">
							<img src="<?php echo esc_url( pn_asset( 'images/thesip/preview-carte.jpg' ) ); ?>" alt="<?php esc_attr_e( 'Anteprima delle carte The SIP', 'pharmanow' ); ?>" width="907" height="1020" class="w-full h-auto rounded-2xl shadow-lg">
						</div>
					</div>
					<div class="absolute -top-4 -right-4 bg-white rounded-2xl shadow-lg p-4 hidden sm:block">
						<div class="flex items-center gap-3">
							<div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
								<?php pn_icon( 'package', array( 'class' => 'w-6 h-6 text-green-600' ) ); ?>
							</div>
							<div>
								<p class="font-bold text-gray-900">31</p>
								<p class="text-xs text-gray-500"><?php esc_html_e( 'Carte', 'pharmanow' ); ?></p>
							</div>
						</div>
					</div>
					<div class="absolute -bottom-4 -left-4 bg-white rounded-2xl shadow-lg p-4 hidden sm:block">
						<div class="flex items-center gap-3">
							<div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
								<?php pn_icon( 'sparkles', array( 'class' => 'w-6 h-6 text-blue-600' ) ); ?>
							</div>
							<div>
								<p class="font-bold text-gray-900">3 <?php esc_html_e( 'temi', 'pharmanow' ); ?></p>
								<p class="text-xs text-gray-500"><?php esc_html_e( 'Da collezionare', 'pharmanow' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php // Welfare aziendale. ?>
	<section class="py-12 sm:py-16 lg:py-20 bg-gradient-to-br from-gray-900 to-gray-800">
		<div class="max-w-[1280px] mx-auto px-4">
			<div class="grid lg:grid-cols-2 gap-12 items-center">
				<div>
					<div class="inline-flex items-center gap-2 bg-white/10 rounded-full px-4 py-2 mb-6">
						<?php pn_icon( 'sparkles', array( 'class' => 'w-5 h-5 text-[#43CCB1]' ) ); ?>
						<span class="text-white font-medium text-sm"><?php esc_html_e( 'Il progetto', 'pharmanow' ); ?></span>
					</div>
					<h2 class="text-2xl sm:text-3xl font-bold text-white mb-4"><?php esc_html_e( 'Nata dalla community, su Kickstarter', 'pharmanow' ); ?></h2>
					<p class="text-gray-300 mb-6 leading-relaxed">
						<?php esc_html_e( "The SIP è nato dal basso: una campagna Kickstarter sostenuta da centinaia di persone che credono nella divulgazione fatta bene. Grazie a loro le 31 carte sono diventate realtà, inclusa la carta speciale numero 31, una creatura preistorica illustrata da Willy Guasti \"Zoosparkle\".", 'pharmanow' ); ?>
					</p>
					<ul class="space-y-3 mb-8">
						<?php foreach ( $pn_welfare as $pn_item ) : ?>
							<li class="flex items-center gap-3 text-white">
								<?php pn_icon( 'check', array( 'class' => 'w-5 h-5 text-[#43CCB1] flex-shrink-0' ) ); ?>
								<?php echo esc_html( $pn_item ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
					<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/negozio' ) ); ?>" class="inline-flex items-center gap-2 bg-gradient-to-r from-[#0B8894] to-[#43CCB1] text-white font-semibold py-3 px-6 rounded-xl hover:opacity-90 transition-opacity">
						<?php esc_html_e( 'Scopri le carte', 'pharmanow' ); ?>
						<?php pn_icon( 'arrow-right', array( 'class' => 'w-5 h-5' ) ); ?>
					</a>
				</div>
				<div class="bg-white/5 rounded-3xl p-6 sm:p-8 backdrop-blur-sm">
					<h3 class="text-white font-semibold mb-6 text-center"><?php esc_html_e( 'Ti guida Andrea', 'pharmanow' ); ?></h3>
					<div class="bg-white rounded-xl overflow-hidden">
						<img src="<?php echo esc_url( pn_asset( 'images/thesip/andrea.jpg' ) ); ?>" alt="<?php esc_attr_e( 'Andrea, la mascotte curiosa di The SIP', 'pharmanow' ); ?>" width="1200" height="800" class="w-full h-auto object-cover">
					</div>
					<p class="text-gray-400 text-sm text-center mt-6">
						<?php esc_html_e( 'Sul retro di ogni carta indica la profondità e accompagna i più piccoli alla scoperta del mare', 'pharmanow' ); ?>
					</p>
				</div>
			</div>
		</div>
	</section>

	<?php // Stats. ?>
	<section class="py-12 sm:py-16 bg-white">
		<div class="max-w-[1280px] mx-auto px-4">
			<div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
				<?php foreach ( $pn_stats as $pn_stat ) : ?>
					<div class="text-center p-6 bg-gray-50 rounded-2xl">
						<p class="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-[#0B8894] to-[#43CCB1] bg-clip-text text-transparent">
							<?php echo esc_html( $pn_stat['value'] ); ?>
						</p>
						<p class="text-gray-600 text-sm mt-2"><?php echo esc_html( $pn_stat['label'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php // CTA. ?>
	<section class="py-12 sm:py-16">
		<div class="max-w-[1280px] mx-auto px-4">
			<div class="bg-gradient-to-r from-[#0B8894] to-[#43CCB1] rounded-3xl p-8 sm:p-12 text-center">
				<h2 class="text-2xl sm:text-3xl font-bold text-white mb-4"><?php esc_html_e( 'Take a seat and take a SIP!', 'pharmanow' ); ?></h2>
				<p class="text-white/90 mb-8 max-w-xl mx-auto">
					<?php esc_html_e( 'Porta il mare in tasca: scegli il tuo set di flashcard illustrate e inizia a collezionare.', 'pharmanow' ); ?>
				</p>
				<div class="flex flex-wrap gap-4 justify-center">
					<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/negozio' ) ); ?>" class="bg-white text-[#0B8894] font-semibold py-3 px-8 rounded-xl hover:bg-gray-100 transition-colors">
						<?php esc_html_e( 'Esplora le carte', 'pharmanow' ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/contatti' ) ); ?>" class="bg-white/20 text-white font-semibold py-3 px-8 rounded-xl hover:bg-white/30 transition-colors border border-white/30">
						<?php esc_html_e( 'Contattaci', 'pharmanow' ); ?>
					</a>
				</div>
			</div>
		</div>
	</section>

	<?php // Company info. ?>
	<section class="py-8 bg-gray-100">
		<div class="max-w-[1280px] mx-auto px-4">
			<div class="flex flex-wrap items-center justify-center gap-6 text-center sm:text-left">
				<div class="flex items-center gap-3">
					<img src="<?php echo esc_url( pn_asset( 'images/logo-thesip.svg' ) ); ?>" alt="The SIP" width="120" height="40" class="h-10 w-auto object-contain">
					<div class="text-left">
						<p class="text-sm font-semibold text-gray-900"><?php esc_html_e( 'The Sea In your Pocket', 'pharmanow' ); ?></p>
						<p class="text-xs text-gray-500"><?php esc_html_e( 'Divulgazione di biologia marina', 'pharmanow' ); ?></p>
					</div>
				</div>
				<div class="hidden sm:block w-px h-8 bg-gray-300"></div>
				<div class="text-sm text-gray-600">
					<p><?php echo esc_html( 'The SIP · Napoli · info@thesip.it' ); ?></p>
					<p><?php esc_html_e( 'Un progetto di due biologi marini e 25 illustratori', 'pharmanow' ); ?></p>
				</div>
			</div>
		</div>
	</section>

</div>

<?php
get_footer();
