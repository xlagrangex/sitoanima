<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="biz-header" id="biz-header">
	<div class="biz-container biz-header__inner">
		<!-- Logo -->
		<div class="biz-header__logo">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="biz-header__logo-text">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<!-- Navigation -->
		<nav class="biz-header__nav" id="biz-nav" aria-label="<?php esc_attr_e( 'Navigazione principale', 'bizstudio' ); ?>">
			<?php
			wp_nav_menu( [
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'biz-nav__list',
				'fallback_cb'    => false,
				'depth'          => 2,
			] );
			?>
		</nav>

		<!-- Actions -->
		<div class="biz-header__actions">
			<!-- Dark mode toggle -->
			<button class="biz-header__action biz-dark-toggle" id="biz-dark-toggle" aria-label="<?php esc_attr_e( 'Cambia tema', 'bizstudio' ); ?>">
				<span class="biz-dark-toggle__sun"><?php echo bizstudio_icon( 'sun' ); ?></span>
				<span class="biz-dark-toggle__moon"><?php echo bizstudio_icon( 'moon' ); ?></span>
			</button>

			<!-- Search -->
			<button class="biz-header__action" id="biz-search-toggle" aria-label="<?php esc_attr_e( 'Cerca', 'bizstudio' ); ?>">
				<?php echo bizstudio_icon( 'search' ); ?>
			</button>

			<!-- Wishlist -->
			<?php if ( function_exists( 'WC' ) ) : ?>
			<a href="<?php echo esc_url( home_url( '/wishlist/' ) ); ?>" class="biz-header__action biz-header__wishlist" aria-label="<?php esc_attr_e( 'Wishlist', 'bizstudio' ); ?>">
				<?php echo bizstudio_icon( 'heart' ); ?>
				<span class="biz-header__wishlist-count"><?php echo function_exists( 'bizstudio_wishlist_count' ) ? bizstudio_wishlist_count() : 0; ?></span>
			</a>
			<?php endif; ?>

			<!-- Account -->
			<a href="<?php echo esc_url( function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_page_permalink( 'myaccount' ) : '#' ); ?>" class="biz-header__action" aria-label="<?php esc_attr_e( 'Account', 'bizstudio' ); ?>">
				<?php echo bizstudio_icon( 'user' ); ?>
			</a>

			<?php if ( function_exists( 'WC' ) ) : ?>
			<!-- Cart -->
			<button class="biz-header__action biz-header__cart" id="biz-cart-toggle" aria-label="<?php esc_attr_e( 'Carrello', 'bizstudio' ); ?>">
				<?php echo bizstudio_icon( 'cart' ); ?>
				<span class="biz-cart-count"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>
			</button>
			<?php endif; ?>

			<!-- Mobile menu toggle -->
			<button class="biz-header__action biz-header__burger" id="biz-menu-toggle" aria-label="<?php esc_attr_e( 'Menu', 'bizstudio' ); ?>">
				<?php echo bizstudio_icon( 'menu' ); ?>
			</button>
		</div>
	</div>
</header>

<?php if ( function_exists( 'WC' ) ) : ?>
<!-- Mini Cart Drawer -->
<div class="biz-mini-cart-overlay" id="biz-mini-cart-overlay"></div>
<?php get_template_part( 'template-parts/global/mini-cart' ); ?>
<?php endif; ?>

<!-- Search Modal -->
<div class="biz-search-overlay" id="biz-search-overlay"></div>
<div class="biz-search-modal" id="biz-search-modal">
	<div class="biz-container">
		<div class="biz-search-modal__inner">
			<div class="biz-search-modal__input-wrap">
				<?php echo bizstudio_icon( 'search' ); ?>
				<input type="search" class="biz-search-modal__input" id="biz-search-input" placeholder="<?php esc_attr_e( 'Cerca prodotti...', 'bizstudio' ); ?>" autocomplete="off">
				<button class="biz-search-modal__close" id="biz-search-close">
					<?php echo bizstudio_icon( 'close' ); ?>
				</button>
			</div>
			<div class="biz-search-results" id="biz-search-results"></div>
		</div>
	</div>
</div>

<!-- Mobile Menu Drawer -->
<div class="biz-mobile-overlay" id="biz-mobile-overlay"></div>
<div class="biz-mobile-menu" id="biz-mobile-menu">
	<div class="biz-mobile-menu__header">
		<span class="biz-mobile-menu__title"><?php esc_html_e( 'Menu', 'bizstudio' ); ?></span>
		<button class="biz-mobile-menu__close" id="biz-mobile-close">
			<?php echo bizstudio_icon( 'close' ); ?>
		</button>
	</div>
	<?php
	wp_nav_menu( [
		'theme_location' => 'primary',
		'container'      => false,
		'menu_class'     => 'biz-mobile-menu__list',
		'fallback_cb'    => false,
		'depth'          => 2,
	] );
	?>
</div>
