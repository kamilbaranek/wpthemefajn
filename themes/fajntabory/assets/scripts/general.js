jQuery(document).ready(function($) {

	$("select#koureni option:first").attr('disabled', 'disabled');// Disable the first value/label ---

	$('.mobile_nav').on('click', function() {
		$('header#header ul.menu').slideToggle();
	});

	// Výběr dopravy :o

	$(document).on('click', '.disabled', function(e){
		e.preventDefault();
	});

	$('.p_chose').on('change', function() {

		var canDo = true;
		var tr = $(this).closest('tr');
		var id = $('input[name=id]').val();
		var post = [];
		var item = {};
		tr.find('select.p_chose').each(function() {
			if( ! $(this).val() ) { canDo = false; }
			var name = $(this).attr('name');
			var value = ($(this).val());
				item[name] = value;
		});

		if( canDo ) {

			$('.heywait').fadeIn( 'slow' );
			post.push(item);
			var data = { 'action' : 'pchose', 'id' : id, 'post' : post };

			jQuery.post(ajax_object.ajax_url, data, function(response) {
				console.log(response);
				var t = JSON.parse(response);
				if( t.variation_price != 0 && t.variation_qty != 0 ) {
					
					if( t.variation_discount < t.variation_price ) {
						tr.find('.variation_price').html( t.variation_price + ' Kč' );
						tr.find('.variation_discount').html( t.variation_discount  + ' Kč' );
					} else {
						tr.find('.variation_price').html( null );
						tr.find('.variation_discount').html( t.variation_price  + ' Kč' );
					}
					// tr.find('.variation_qty').html( 'Zbývá ' + t.variation_qty + ' ks' );
					tr.find('a').attr('href', '?add_to_cart=' + t.id);
					tr.find('a').removeClass('disabled');
					$('.heywait').fadeOut( 'slow' );

				} else {

					tr.find('.variation_price').html( null );
					tr.find('.variation_discount').html( null  );
					// tr.find('.variation_qty').html('Není k dispozici');
					tr.find('a').attr('href', '?add_to_cart=0');	
					tr.find('a').addClass('disabled');
					$('.heywait').fadeOut( 'slow' );

				}

				$('.heywait').fadeOut( 'slow' );
			});

		}

	});
	
	$('.d_chose').on('change', function() {
		if( $(this).closest('tr').find('.n_chose').val() &&
			$(this).closest('tr').find('.v_chose').val() &&
			$(this).closest('tr').find('.t_chose').val() &&
			$(this).closest('tr').find('.z_chose').val() ) {

			$('.heywait').fadeIn( 'slow' );
		
			var tr = $(this).closest('tr');
			var data = {
				'action': 'dchose',
				'n_chose': tr.find('.n_chose').val(),
				'v_chose': tr.find('.v_chose').val(),
				't_chose': tr.find('.t_chose').val(),
				'z_chose': tr.find('.z_chose').val()
			};

			jQuery.post(ajax_object.ajax_url, data, function(response) {
				var t = JSON.parse(response);
				// console.log( t );
				if( t.price != 0 && t.capacity != 0 ) {
					if( t.sale_bool == true ) {
						tr.find('.variation_price').html( t.price + ' Kč' );
						tr.find('.variation_discount').html( t.sale_price  + ' Kč' + t.sale_to );
					} else {
						tr.find('.variation_price').html( null );
						tr.find('.variation_discount').html( t.price  + ' Kč'  );
					}

					tr.find('.variation_qty').html( 'Zbývá ' + t.capacity + ' míst' );
					tr.find('a').attr('href', '?add_to_cart=' + t.id);
					tr.find('a').removeClass('disabled');
					$('.heywait').fadeOut( 'slow' );
				} else {
					tr.find('.variation_price').html( null );
					tr.find('.variation_discount').html( null  );
					tr.find('.variation_qty').html('Není k dispozici');
					tr.find('a').attr('href', '?add_to_cart=0');	
					tr.find('a').addClass('disabled');
					$('.heywait').fadeOut( 'slow' );
				}
			});

		}
	});

	if(window.location.hash) {
		
		var hashtag = window.location.hash;

		if( $(hashtag).length != 0 ) {

			var top = $('#tabory-vypis').offset().top;
				top = top - 110;
			$('.tabory_cat').each( function () {
				$(this).stop().fadeOut();
			});
			$('#tabory-vypis ' + hashtag).stop().fadeIn();
			// $(window).animate({scrollTop: top}, 500 );
			$("html, body").animate({ scrollTop: top }, "slow");

		}
	}

	$('.slidelink a').on('click', function(e) {
		e.preventDefault();
		var elem = $(this).attr('href');
		var top = $(elem).offset().top;
		$("html, body").animate({ scrollTop: top }, "slow");
	});

	$('.show_cat').on('click', function(e) {
		e.preventDefault();
		var hashtag = $(this).attr('href');
		location.hash = hashtag;
		var top = $('#tabory-vypis').offset().top;
			top = top - 110;
		$('.tabory_cat').each( function () {
			$(this).stop().fadeOut();
		});
		var $href = $(this).attr('href');
		if( $($href).css("display") == 'none' ) {
			$($href).stop().fadeIn();
		} else {
			$($href).stop().fadeOut();
		}
		// $(window).animate({scrollTop: top}, 500 );
		$("html, body").animate({ scrollTop: top }, "slow");
	});

	/**$('#promo').vide(
		{
 			mp4: 'http://www.fajntabory.cz/wp-content/themes/fajntabory/assets/demo/video.mp4',
  			webm: 'http://www.fajntabory.cz/wp-content/themes/fajntabory/assets/demo/video.webm',
  			ogv: 'http://www.fajntabory.cz/wp-content/themes/fajntabory/assets/demo/video.ogv',
  			poster: 'http://www.fajntabory.cz/wp-content/themes/fajntabory/assets/demo/ocean.jpg'
		} );
	**/

	if ($(window).width() < 960) {

		var width = $(window).width() / 3;
		$('#gallery').bxSlider({
			minSlides: 3,
	  		maxSlides: 3,
	  		slideWidth: width,
	  		slideMargin: 0,
	  		moveSlides: 1,
	  		pager: false,
	  		touchEnabled: false
		});

	} else {

		var width = $(window).width() / 6;
		$('#gallery').bxSlider({
			minSlides: 6,
	  		maxSlides: 6,
	  		slideWidth: width,
	  		slideMargin: 0,
	  		moveSlides: 1,
	  		pager: false,
	  		touchEnabled: false
		});
	}

	$('#slider ul').bxSlider({
  		pager: false,
  		infiniteLoop: false,
  		touchEnabled: false
	});

	function initCampBookingPicker() {
		$('[data-camp-picker]').each(function() {
			var $picker = $(this);
			var $panel = $picker.closest('.camp-booking__panel');
			var $select = $picker.find('[data-camp-select]');
			var $location = $picker.find('[data-camp-location]');
			var $term = $picker.find('[data-camp-term]');
			var $priceOld = $picker.find('[data-camp-price-old]');
			var $priceCurrent = $picker.find('[data-camp-price-current]');
			var $discountNote = $picker.find('[data-camp-discount-note]');
			var $availability = $picker.find('[data-camp-availability]');
			var $cta = $picker.find('[data-camp-cta]');
			var $summaryPrice = $panel.find('[data-camp-summary-price]');
			var $summaryAvailability = $panel.find('[data-camp-summary-availability]');
			var $countdown = $picker.find('[data-camp-countdown]');
			var $countdownValue = $picker.find('[data-camp-countdown-value]');
			var countdownTimer = null;

			if ( ! $select.length ) {
				return;
			}

			function padCountdownNumber(number) {
				return number < 10 ? '0' + number : number;
			}

			function formatSaleCountdown(milliseconds) {
				var secondsTotal = Math.max(0, Math.floor(milliseconds / 1000));
				var days = Math.floor(secondsTotal / 86400);
				var hours = Math.floor((secondsTotal % 86400) / 3600);
				var minutes = Math.floor((secondsTotal % 3600) / 60);
				var seconds = secondsTotal % 60;
				var time = padCountdownNumber(hours) + ':' + padCountdownNumber(minutes) + ':' + padCountdownNumber(seconds);

				if ( days > 0 ) {
					return days + ' d ' + time;
				}

				return time;
			}

			function stopSaleCountdown() {
				if ( countdownTimer ) {
					clearInterval(countdownTimer);
					countdownTimer = null;
				}
			}

			function syncSaleCountdown(saleEndsAt) {
				var endsAt = parseInt(saleEndsAt, 10);

				stopSaleCountdown();

				if ( ! $countdown.length || ! endsAt ) {
					$countdown.addClass('is-hidden');
					$countdownValue.text('');
					return;
				}

				function renderCountdown() {
					var remaining = (endsAt * 1000) - Date.now();

					if ( remaining <= 0 ) {
						stopSaleCountdown();
						$countdown.addClass('is-hidden');
						$countdownValue.text('');
						return;
					}

					$countdown
						.attr('data-sale-ends-at', endsAt)
						.removeClass('is-hidden');
					$countdownValue.text(formatSaleCountdown(remaining));
				}

				renderCountdown();
				countdownTimer = setInterval(renderCountdown, 1000);
			}

			function syncCampBookingSelection() {
				var $option = $select.find('option:selected');
				var location = $option.attr('data-location') || '';
				var term = $option.attr('data-term') || $option.text();
				var price = $option.attr('data-price') || '';
				var regularPrice = $option.attr('data-regular-price') || price;
				var priceOld = $option.attr('data-price-old') || '';
				var discountNote = $option.attr('data-discount-note') || '';
				var saleEndsAt = $option.attr('data-sale-ends-at') || '';
				var availabilityLabel = $option.attr('data-availability-label') || '';
				var availabilityClass = $option.attr('data-availability-class') || '';
				var manageStock = $option.attr('data-manage-stock') === '1';
				var canOrder = $option.attr('data-can-order') === '1';
				var orderLink = $option.attr('data-order-link') || '#';
				var buttonLabel = $option.attr('data-button-label') || (canOrder ? 'Rezervovat' : 'Obsazeno');

				$term.text(term);
				$priceCurrent.text(price);
				$summaryPrice.html(regularPrice.replace(/ /g, '&nbsp;'));
				syncSaleCountdown(saleEndsAt);

				if ( $location.length ) {
					$location.text(location);
					$location.toggleClass('is-hidden', ! location);
				}

				$priceOld.text(priceOld);
				$priceOld.toggleClass('is-hidden', ! priceOld);

				$discountNote.text(discountNote);
				$discountNote.toggleClass('is-hidden', ! discountNote);

				$availability
					.removeClass('is-open is-low is-full')
					.toggleClass('is-hidden', ! manageStock || ! availabilityLabel)
					.text(availabilityLabel);

				if ( manageStock && availabilityClass ) {
					$availability.addClass(availabilityClass);
				}

				$summaryAvailability
					.removeClass('is-open is-low is-full')
					.toggleClass('is-hidden', ! manageStock || ! availabilityLabel)
					.text(availabilityLabel);

				if ( manageStock && availabilityClass ) {
					$summaryAvailability.addClass(availabilityClass);
				}

				$cta
					.attr('href', orderLink)
					.toggleClass('disabled', ! canOrder)
					.text(buttonLabel);
			}

			$select.on('change', syncCampBookingSelection);
			syncCampBookingSelection();
		});
	}


	if ($("body").hasClass("single-product")) {
		initCampBookingPicker();

		if($(".camp-tab-panel").length != 0) {
			if( $('[data-tab-panel="pobytovy"]').length > 0 ) {
				openCard('js', 'pobytovy');
			} else if( $('[data-tab-panel="primestsky"]').length > 0 ) {
				openCard('js', 'primestsky');
			}
		}
	}

	$(window).on('scroll', function() {
		if ($("body").hasClass("home")) {
			
		}
	});

	$('input[data-mask]').each(function () {
		$(this).mask($(this).attr('data-mask'), { });
	});

	$('form.objednavka').on( 'submit', function(e) {
		var $form = $(this);
		var $valid = true;

		// Validace jestli bylo zatrhnuto TOC

		if( $form.find('#toc').prop('checked') == true) {
			$form.parent('div').removeClass('not-valid');
			$form.find('.tocdiv').removeClass('not-valid');
		} else {
			$valid = false;
			$form.parent('div').addClass('not-valid');
			$form.find('.tocdiv').addClass('not-valid');
		}

		// Validace povinných polí

		$form.find('.required').each( function() {
			if( $(this).val().length === 0 || $(this).val() == 0 ) {
				$(this).parent('div').addClass('not-valid');
				$valid = false;
			} else {
				$(this).parent('div').removeClass('not-valid');
			}
		});

		// Validace, jestli emaily odpovídají

		if( $form.find("input[name='email-check']").length ) {
			if( $form.find("input[name='email']").val() != $form.find("input[name='email-check']").val() ) {
				$form.find("input[name='email']").parent('div').addClass('not-valid');
				$form.find("input[name='email-check']").parent('div').addClass('not-valid');
				$valid = false;
			} else {
				$form.find("input[name='email']").parent('div').removeClass('not-valid');
				$form.find("input[name='email-check']").parent('div').removeClass('not-valid');
			}
		}

		if ( $form.find('.checkit').length ) {

			var checkit = false;

			$form.find('.checkit').each( function() {

				if( $(this).prop('checked') == true ) {
					checkit = true;
				}

			});

			if( checkit == false ) {
				$form.find('.checkitdiv').addClass('not-valid');
				$valid = false;
			} else {
				$form.find('.checkitdiv').removeClass('not-valid');
			}

		}

		if( $valid == false ) {
			$('html,body').animate({ scrollTop: 0 }, 'slow');
			$form.find('.form-validation-error').show();
			console.log( 'invalid' );
			return false;
		} else {
			$form.find('.form-validation-error').hide();
			console.log( 'valid' );
			if (
				$form.attr('data-checkout-step') == 'reserve' &&
				$form.find("input[name='_wpcf7_recaptcha_response']").length &&
				$form.attr('data-recaptcha-sitekey')
			) {
				e.preventDefault();

				if (typeof grecaptcha === 'undefined') {
					$('html,body').animate({ scrollTop: 0 }, 'slow');
					$form.find('.form-validation-error').show();
					return false;
				}

				grecaptcha.ready(function() {
					grecaptcha.execute($form.attr('data-recaptcha-sitekey'), { action: 'contactform' }).then(function(token) {
						$form.find("input[name='_wpcf7_recaptcha_response']").val(token);
						$form[0].submit();
					}).catch(function() {
						$('html,body').animate({ scrollTop: 0 }, 'slow');
						$form.find('.form-validation-error').show();
					});
				});

				return false;
			}
			return true;
		}

	});

	$('.accordion h3 a').on( 'click', function(e) {

		e.preventDefault();
		var $this = $(this).closest('div.accordion');

		if( ! $this.hasClass('active') ) {

			$('.accordion').each( function() {
				$(this).removeClass('active');
				$(this).find('h3 a em').text('+');
			});

			$this.addClass( 'active' );
			$this.find('h3 a em').text('-');

		} else {

			$('.accordion').each( function() {
				$(this).removeClass('active');
				$(this).find('h3 a em').text('+');
			});

		}

	} );

	$('.event').each( function() {
		var start_position = $(this).data('start-position');
			start_position = (start_position * 60) - 25;
		var end_position = $(this).data('end-position');
			end_position = (end_position * 60) - 35;
		var width = end_position - start_position;
		var top_position = $(this).data('top-position');
		$(this).css({width: width, left: start_position, right: end_position, top: top_position, position:'absolute'});
	});

	$('document .single-product #defaultOpen').on('create', function(){
		var href= $(this).attr('href');
		alert('href');
	});

	$('input#proplaceni_tabora_zamestnavatelem').on('click', function() {
		if($(this).prop('checked')) {
			$(".zamestnavatel_hidden").show();
		} else {
			$(".zamestnavatel_hidden").hide();
		}
	} );

});

function openCard(evt, cardName) {
    // Declare all variables
    var i, tabcontent, tablinks, targetPanels, defaultButton;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("camp-tab-panel");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    targetPanels = document.querySelectorAll('[data-tab-panel="' + cardName + '"]');
    for (i = 0; i < targetPanels.length; i++) {
    	targetPanels[i].style.display = "block";
    }

    if( evt == 'js' ) {
    	defaultButton = document.querySelector('.tablinks[data-tab-target="' + cardName + '"]');
    	if (defaultButton) {
    		defaultButton.className += " active";
    	}
    } else if (evt && evt.currentTarget) {
    	evt.currentTarget.className += " active";
    }
}
