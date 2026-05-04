<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<?php if ( false ) : // Hardcoded Google tags disabled; managed via GTM plugin. ?>
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-114677811-1"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', 'UA-114677811-1');
		  gtag('config', 'AW-809603562');
		</script>
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
		if ( function_exists( 'fajntabory_print_purchase_conversion' ) ) {
			fajntabory_print_purchase_conversion();
		}
	?>
	
	<?php if ( false ) : // Hardcoded Universal Analytics disabled; managed via GTM plugin. ?>
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
	<?php endif; ?>
</head>

<body <?php body_class(); ?>>

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
		<a class="mobile_nav" href="#"><i class="fa fa-bars"></i> Hlavní nabídka</a>
		<?php 
			if( has_nav_menu( 'main_menu' ) ) {
				wp_nav_menu( array( 'theme_location' => 'main_menu' ) );
			} 
		?>
	</header>
