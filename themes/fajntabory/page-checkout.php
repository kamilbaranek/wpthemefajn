<?php
/**
 * Template Name: Objednávka tábora
 */

	if( empty( $_GET['id'] ) ) {
		$_GET['id'] = 0;
	}

	$id = $_GET['id'];

	get_header(); ?>
	<div id="content">
	<h2 class="section-title" style="background: #14b5e1; border-bottom: 3px solid #0082AE;">Objednávka tábora<span>Vyplňte prosím objednávkový formulář</span></h2>
	<div id="main">
		<form>
			<h2>Údaje táborníka</h2>
			<p>
				<label for="tabornik_jmeno">Jméno</label>
				<input type="text" id="tabornik_jmeno" name="tabornik_jmeno">
			</p>
			<p>
				<label for="tabornik_prijmeni">Příjmení</label>
				<input type="text" id="tabornik_prijmeni" name="tabornik_prijmeni">
			</p>
			<p>
				<label for="tabornik_narozeni">Datum narození</label>
				<input type="text" id="tabornik_narozeni" name="tabornik_narozeni">
			</p>
			<p>
				<label for="tabornik_ulice">Ulice, č.p.</label>
				<input type="text" id="tabornik_ulice" name="tabornik_ulice">
			</p>
			<p>
				<label for="tabornik_mesto">Město</label>
				<input type="text" id="tabornik_mesto" name="tabornik_mesto">
			</p>
			<p>
				<label for="tabornik_psc">PSČ</label>
				<input type="text" id="tabornik_psc" name="tabornik_psc">
			</p>
			<p>
				<label for="tabornik_skola">Název školy, město</label>
				<input type="text" id="tabornik_skola" name="tabornik_skola">
			</p>
			<p>
				<label for="tabornik_triko">Velikost trička</label>
				<input type="text" id="tabornik_triko" name="tabornik_triko">
			</p>

			<h2 class="clearfix">Údaje zákonného zástupce</h2>
			<h2>Poznámky</h2>
			<h2>Kde jste se o nás dozvěděli?</h2>
		</form>
	</div>
	</div>
	<?php
		get_footer();
?>