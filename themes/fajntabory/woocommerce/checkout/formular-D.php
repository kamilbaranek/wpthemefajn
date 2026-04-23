	<input type="hidden" name="objednavka" value="d">

	<div class="form-validation-error">Objednávkový formulář se nepodařilo odeslat, zkontrolujte prosím zvýrazněné části :)</div>

	<h2>Údaje táborníka</h2>

	<div class="col-3">
		<label for="">Jméno<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="jmeno" class="form-element required">
	</div>
	<div class="col-3">
		<label for="">Příjmení<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="prijmeni" class="form-element required">
	</div>
	<div class="col-3">
		<label for="">Datum narození<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="datum_narozeni" placeholder="dd.mm.rrrr" class="form-element required" data-mask="00.00.0000">
	</div>

	<h3>Kontaktní údaje</h3>

	<div class="col-3">
		<label for="">Telefon<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="telefon" class="form-element required" value="<?php echo esc_attr( fajntabory_checkout_field_value( 'telefon' ) ); ?>">
	</div>
	<div class="col-3">
		<label for="">E-mail<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="email" class="form-element required" value="<?php echo esc_attr( fajntabory_checkout_field_value( 'email' ) ); ?>">
		<p>Prosím zkontrolujte, zda jste svůj E-MAIL uvedli správně.</p>
	</div>
	<div class="col-3">
		<label for="">E-mail znovu<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="email-check" class="form-element required" value="<?php echo esc_attr( fajntabory_checkout_field_value( 'email-check' ) ); ?>">
		<p>Ověření správnosti e-mailu</p>
	</div>

	<h2>Poznámky</h2>

	<div class="col-2">
		<textarea name="poznamky" class="form-element form-textarea" placeholder="Poznámky"></textarea>
		<p>V případě potřeby, uveďte jakoukoliv poznámku.</p>
	</div>


	<div class="submit tocdiv">
		<h2>Všeobecné podmínky</h2>
		<p><em>Pro dokončení objednávky je nutné souhlasit se všeobecnými podmínkami</em></p>
		<input type="checkbox" id="toc" name="toc">
		<label for="toc"><?php printf( __( '<a href="%s" target="_blank">Všeobecné podmínky</a>', 'woocommerce' ), esc_url( wc_get_page_permalink( 'terms' ) ) ); ?> jsem si přečetl / přečetla a souhlasím s nimi</label>
	</div>

	<div class="submit">
		<input type="submit" class="form-submit" value="Odeslat objednávku">
	</div>
