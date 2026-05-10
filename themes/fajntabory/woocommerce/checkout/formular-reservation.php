<?php $form_type = get_query_var( 'fajntabory_checkout_form' ); ?>

<input type="hidden" name="objednavka" value="<?php echo esc_attr( $form_type ); ?>">
<input type="hidden" name="reservation_step" value="reserve">
<input type="hidden" name="_wpcf7_recaptcha_response" value="">
<?php wp_nonce_field( 'fajntabory_create_reservation', 'reservation_create_nonce' ); ?>

<div class="reservation-company-field" aria-hidden="true" style="position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden;">
	<label for="reservation-company">Společnost</label>
	<input id="reservation-company" type="text" name="reservation_company" value="" tabindex="-1" autocomplete="off">
</div>

<div class="form-validation-error">Objednávkový formulář se nepodařilo odeslat, zkontrolujte prosím zvýrazněné části :)</div>

<h2>Údaje zákonného zástupce</h2>

<div class="col-3">
	<label for="reservation-email">E-mail<em>Toto pole je vyžadováno</em></label>
	<input id="reservation-email" type="email" name="email" class="form-element required" value="<?php echo esc_attr( fajntabory_checkout_field_value( 'email' ) ); ?>">
</div>

<div class="col-3">
	<label for="reservation-phone">Telefon<em>Toto pole je vyžadováno</em></label>
	<input id="reservation-phone" type="tel" name="telefon" class="form-element required" value="<?php echo esc_attr( fajntabory_checkout_field_value( 'telefon' ) ); ?>">
</div>

<div class="col-3">
	<p>Tlačítkem níže vytvoříte předběžnou objednávku. V dalším kroku zvolíte, jestli údaje doplníte hned, nebo později přes e-mail.</p>
</div>

<div class="submit tocdiv">
	<h2>Všeobecné podmínky</h2>
	<p><em>Pro dokončení objednávky je nutné souhlasit se všeobecnými podmínkami</em></p>
	<input type="checkbox" id="toc" name="toc">
	<label for="toc"><?php printf( __( '<a href="%s" target="_blank">Všeobecné podmínky</a>', 'woocommerce' ), esc_url( wc_get_page_permalink( 'terms' ) ) ); ?> jsem si přečetl / přečetla a souhlasím s nimi</label>
</div>

<div class="submit">
	<input type="submit" class="form-submit" value="Pokračovat">
</div>
