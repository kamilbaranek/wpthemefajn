
	<input type="hidden" name="objednavka" value="a">

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
	<div class="col-3">
		<label for="">Ulice, č.p.<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="ulice" class="form-element required">
	</div>
	<div class="col-3">
		<label for="">Město<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="mesto" placeholder="Město" class="form-element required">
	</div>
	<div class="col-3">
		<label for="">PSČ<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="psc" class="form-element required"  placeholder="000 00" data-mask="000 00">
	</div>
	<div class="col-3">
		<label for="">Název školy, město<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="skola" class="form-element required">
		<p>Jméno školy táborníka a město, kde se škola nachází</p>
	</div>
	<div class="col-3">
		<label for="">Národnost<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="narodnost" class="form-element required">
		<p>Národnost táborníka</p>
	</div>
	<div class="col-3">
		<label for="">Velikost trička<em>Toto pole je vyžadováno</em></label>
		<select name="triko" class="form-element required">
			<option value="0">Prosím vyberte..</option>
			<option value="xs">XS</option>
			<option value="s">S</option>
			<option value="m">M</option>
			<option value="l">L</option>
			<option value="xl">XL</option>
			<option value="xxl">XXL</option>
		</select>
		<p>Táborové tričko není v ceně tábora. I v případě, že neobjednáváte tričko společně s táborem, uveďte jeho velikost pro případný zájem budoucí.</p>
	</div>

	<h2>Údaje zákonného zástupce</h2>

	<div class="col-3">
		<label for="">Jméno<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="Z_jmeno" class="form-element required">
	</div>
	<div class="col-3">
		<label for="">Příjmení<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="Z_prijmeni" class="form-element required">
	</div>
	<div class="col-3">
		<label for="">Telefon<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="telefon" class="form-element required">
	</div>
	<div class="col-3">
		<label for="">E-mail<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="email" class="form-element required">
		<p>Prosím zkontrolujte, zda jste svůj E-MAIL uvedli správně.</p>
	</div>
	<div class="col-3">
		<label for="">E-mail znovu<em>Toto pole je vyžadováno</em></label>
		<input type="text" name="email-check" class="form-element required">
		<p>Ověření správnosti e-mailu</p>
	</div>

	<h2>Poznámky</h2>

	<div class="col-2">
		<textarea name="zpusobilost" class="form-element form-textarea" placeholder="Zdravotní stav, diety a jiné poznámky"></textarea>
		<p>Způsobilost táborníka pro pobyt na táboře, diety, alergie, nebo jiná zdravotní omezení</p>
	</div>

	<h2>Proplacení tábora zaměstnavatelem</h2>

	<div class="col-2 clearfix">
		<span>
			<input class="checkit" type="checkbox" id="proplaceni_tabora_zamestnavatelem" name="proplaceni_tabora_zamestnavatelem">
			<label for="proplaceni_tabora_zamestnavatelem">Tábor úhradí zaměstnavatel, potřebuji vystavit fakturu pro zaměstnavatele.</label>
		</span>
	</div>

	<br class="clear" />

	<div class="zamestnavatel_hidden">

		<div class="col-3">
			<label for="fakturace_odberatel_nazev">Název zaměstnavatele</label>
			<input type="text" name="fakturace_odberatel_nazev" class="form-element fakturace_odberatel_nazev">
		</div>

		<div class="col-3">
			<label for="fakturace_odberatel_ulice">Ulice č.p.</label>
			<input type="text" name="fakturace_odberatel_ulice" class="form-element fakturace_odberatel_ulice">
		</div>

		<div class="col-3">
			<label for="fakturace_odberatel_mesto">PSČ, město</label>
			<input type="text" name="fakturace_odberatel_mesto" class="form-element fakturace_odberatel_mesto">
		</div>

		<div class="col-3">
			<label for="fakturace_odberatel_ico">IČ zaměstnavatele</label>
			<input type="number" name="fakturace_odberatel_ico" class="form-element fakturace_odberatel_ico">
		</div>

		<div class="col-3 clearfix">
			<label for="fakturace_odberatel_dic">DIČ zaměstnavatele</label>
			<input type="text" name="fakturace_odberatel_dic" class="form-element fakturace_odberatel_dic">
		</div>

		<br class="clear" />

		<div class="col-2">
			<label for="zamestnavatel">Poznámky k proplacení tábora zaměstnavatelem</label>
			<textarea name="zamestnavatel" class="form-element form-textarea" placeholder="Další specifikace vyžadované Vaším zaměstnavatelem"></textarea>
		</div>

	</div>

	<h2>Kde jste se o nás dozvěděli?</h2>
	<div class="col-2 checkitdiv">
		<p><em>Prosím vyberte jednu možnost</em></p>
		<span>
			<input class="checkit" type="checkbox" id="v_minulosti_byl" name="v_minulosti_byl">
			<label for="v_minulosti_byl">Už jsem v minulosti na Fajn Táborech byl / byla</label>
		</span>
		<span>
			<input class="checkit" type="checkbox" id="od_kamarada" name="od_kamarada">
			<label for="od_kamarada">Od kamaráda</label>
		</span>
		<span>
			<input class="checkit" type="checkbox" id="letaky" name="letaky">
			<label for="letaky">Letáky / plakáty</label>
		</span>
		<span>
			<input class="checkit" type="checkbox" id="noviny" name="noviny">
			<label for="noviny">Noviny, městské listy nebo časopis</label>
		</span>
		<span>
			<input class="checkit" type="checkbox" id="facebook" name="facebook">
			<label for="facebook">Facebook</label>
		</span>
		<span>
			<input class="checkit" type="checkbox" id="instagram" name="instagram">
			<label for="instagram">Instagram</label>
		</span>
		<span>
			<input class="checkit" type="checkbox" id="ve_skole" name="ve_skole">
			<label for="tiktok">TikTok</label>
		</span>
		<span>
			<input class="checkit" type="checkbox" id="youtube" name="youtube">
			<label for="youtube">Youtube</label>
		</span>
		<span>
			<input class="checkit" type="checkbox" id="jinde" name="jinde">
			<label for="jinde">Jinde na internetu</label>
		</span>
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