<?php
	session_start();
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-114677811-1"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'UA-114677811-1');
	  gtag('config', 'AW-809603562');
	</script>
	<?php if(is_page_template( 'page-thankyou.php' ) && !empty($_GET['oid'])) : ?>
	<!-- Event snippet for Nákup conversion page --> 
	<script> gtag('event', 'conversion', { 'send_to': 'AW-809603562/3WcBCO6FmcQCEOqjhoID', '<?php echo $_GET['oid']; ?>': '' }); </script>
	<?php endif; ?>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="google-site-verification" content="ngt00vcEOaVFTxgkiHyF173vkpXgIBj6NsjDrcWXfUQ" />
	<meta name="facebook-domain-verification" content="gm8vdso14a8smh24m80lxtqbhessgw" />
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<!--[if lt IE 9]>
	<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/scripts/html5.js"></script>
	<![endif]-->
	<?php wp_head(); ?>

	<?php
		if ( is_page( 'cart' ) || is_cart() ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready( function($) {

					$('.checkout-button, .cart-mobile-order').on('click', function(e) {
						var checkoutUrl = $(this).data('checkout-url');

						if( $('.storno').length ) {
							e.preventDefault();
							$('.overlay').fadeIn();
							$('.storno').fadeIn();
							return false;
						}

						if( checkoutUrl ) {
							e.preventDefault();
							window.location.href = checkoutUrl;
							return false;
						}
					});

					$('.continue').on('click', function(e) {
						$('.overlay').fadeOut();
						$('.storno').fadeOut();
					});
				});
			</script>
			<?php
		}
	?>
	
	<script>
		(function(i,s,o,g,r,a,m){ i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		ga('create', "UA-48737306-1", "fajntabory.cz");
		ga('require', 'linkid', 'linkid.js');
		ga('require', 'ecommerce', 'ecommerce.js');
		ga('send', 'pageview');
	</script>
</head>

<body <?php body_class(); ?>>
<div class="overlay"></div>
<?php
	if( !isset($_COOKIE["accepted"] ) ) {
		echo '<div class="popup_window gdpr">';
		echo '<h2>Než budete pokračovat,</h2>';
		echo '<p>musíme Vás požádat o udělení souhlasu s užíváním <a href="https://cs.wikipedia.org/wiki/HTTP_cookie" target="_blank">cookies</a>, abychom vám zajistili co možná nejsnadnější použití našich webových stránek. Cookies používáme pro správnou funkci objednávkového systému a pro analytické služby, které nám poskytují informace, podle kterých se snažíme webové stránky dále rozvíjet.</p>';
		echo '<a href="?accepted=1" class="popup_button accepted">Souhlasím</a>';
		echo '</div>';
	}
?>

<?php
	if ( is_page( 'cart' ) || is_cart() ) {
		if( matched_cart_items(2853) == 0 ) {
			$storno_val = 0;
			$storno_val = get_storno_value(2853);
			if( $storno_val > 0 ) {
				echo '<div class="popup_window storno">';
				echo '<h2>Upozornění</h2>';
				echo '<p>Ve Vaší objednávce nemáte Storno pojištění. Díky Storno pojištění se vyhnete nepříjemnosti propadnutí až 100 % Vašich peněz v případě zrušení. Cena by jinak podlehla našim <a href="https://www.fajntabory.cz/produkt/storno-pojisteni/" target="_blank">storno podmínkám</a>.</p>';
				echo '<p>Cena storno pojištění je pouze ' . ceil($storno_val) . ' kč</p>';
				echo '<p>Opravdu si přejete objednat tábor bez Storno pojištění?</p>';
				echo '<p>';
				echo '<a href="' . esc_url( add_query_arg( 'add_to_cart', 2853, wc_get_cart_url() ) ) . '" class="popup_button">Přidat do košíku</a>';
				echo '<a href="'.esc_url( fajntabory_get_checkout_url() ).'" class="popup_button continue">Přesto pokračovat</a>';
				echo '</p>';
				echo '</div>';
			}
		}
	}
?>

	<header id="header">

		<?php 
			$website_logo 	= get_option('website_logo');
			if( !empty( $website_logo ) ) {
				echo '<a href="'.home_url().'" class="logo">';
				echo wp_get_attachment_image( $website_logo, 'logo' );
				echo '</a>';
			}
		?>

		<?php
			$facebook_uri 	= get_option('facebook_uri');
			$youtube_uri 	= get_option('youtube_uri');
			$twitter_uri 	= get_option('twitter_uri');
			$instagram_uri 	= get_option('instagram_uri');

			if( !empty( $facebook_uri ) || !empty( $youtube_uri ) || !empty( $twitter_uri ) || !empty( $instagram_uri ) ) :
		?>
			<div id="social">
				<ul>
					<?php 
						if( !empty( $facebook_uri ) ) {
							echo '<li class="facebook"><a href="'.$facebook_uri.'"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>';
						} if( !empty( $youtube_uri ) ) {
							echo '<li class="twitter"><a href="'.$youtube_uri.'"><i class="fa fa-youtube-play" aria-hidden="true"></i></a></li>';
						} if( !empty( $twitter_uri ) ) {
							echo '<li class="youtube"><a href="'.$twitter_uri.'"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>';
						} if( !empty( $instagram_uri ) ) {
							echo '<li class="instagram"><a href="'.$instagram_uri.'"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>';
						} 
					?>
				</ul>
			</div>
		<?php
			endif;
		?>
		<a class="mobile_nav" href="#" aria-label="Hlavní nabídka"><i class="fa fa-bars" aria-hidden="true"></i><span class="mobile_nav__label">Hlavní nabídka</span></a>
		<?php 
			if( has_nav_menu( 'main_menu' ) ) {
				wp_nav_menu( array( 'theme_location' => 'main_menu' ) );
			} 
		?>
	</header>		
