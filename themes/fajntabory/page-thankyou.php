<?php
/**
 * Template Name: Thank you page
 */
	get_header();

	if( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			$image = get_the_post_thumbnail_url( get_option( 'page_on_front' ), 'slider' );
			$pid = get_the_ID();
			if( !empty($_GET['oid']) ) { $oid = $_GET['oid']; } else { $oid = 0; }
			if( !empty($_GET['email']) ) { $email = $_GET['email']; } else { $email = 'svém e-mailu'; }

?>

<div id="promo" style="background-image: url(<?php echo $image; ?>);">
	<div class="flyer">Fajn Tábory<br/>jsou prostě boží!<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/fajn_tabory.png"></div>
</div>

<div class="confirm">
	<p>
		Vaše objednávka č. <?php echo $oid; ?> byla úspěšně odeslána :)</br>
		Potvrzení naleznete na <?php echo $email; ?></br>
	</p>
	<h2>DĚKUJEME</h2>
</div>

<?php
		}
	}
?>



<?php
	get_footer();
?>