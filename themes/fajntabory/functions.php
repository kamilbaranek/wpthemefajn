<?php



	add_filter( 'woocommerce_hide_invisible_variations', '__return_true' );



	add_filter( 'woocommerce_admin_meta_boxes_variations_per_page', 'handsome_bearded_guy_increase_variations_per_page' );



	function filter_woocommerce_can_reduce_order_stock( $true, $instance ) { 

		return false; 

	}; 

	add_filter( 'woocommerce_can_reduce_order_stock','filter_woocommerce_can_reduce_order_stock', 10, 2 );

	if( ! defined( 'FAJNTABORY_INCOMPLETE_ORDER_STATUS' ) ) {

		define( 'FAJNTABORY_INCOMPLETE_ORDER_STATUS', 'incomplete' );

	}

	if( ! defined( 'FAJNTABORY_INCOMPLETE_ORDER_POST_STATUS' ) ) {

		define( 'FAJNTABORY_INCOMPLETE_ORDER_POST_STATUS', 'wc-incomplete' );

	}

	if( ! defined( 'FAJNTABORY_RESERVATION_LINK_SENT_STATUS' ) ) {

		define( 'FAJNTABORY_RESERVATION_LINK_SENT_STATUS', 'res-link-sent' );

	}

	if( ! defined( 'FAJNTABORY_RESERVATION_MAX_REMINDERS' ) ) {

		define( 'FAJNTABORY_RESERVATION_MAX_REMINDERS', 5 );

	}

	if( ! defined( 'FAJNTABORY_RESERVATION_LINK_MAX_SENDS' ) ) {

		define( 'FAJNTABORY_RESERVATION_LINK_MAX_SENDS', 3 );

	}

	if( ! defined( 'FAJNTABORY_RESERVATION_LINK_RESEND_COOLDOWN' ) ) {

		define( 'FAJNTABORY_RESERVATION_LINK_RESEND_COOLDOWN', 15 * 60 );

	}

	function fajntabory_get_reservation_reminder_status( $reminder_number ) {

		$reminder_number = max( 1, min( FAJNTABORY_RESERVATION_MAX_REMINDERS, (int) $reminder_number ) );

		return 'res-remind-' . $reminder_number;

	}

	function fajntabory_get_reservation_tracking_statuses() {

		$statuses = array(
			FAJNTABORY_INCOMPLETE_ORDER_STATUS,
			FAJNTABORY_RESERVATION_LINK_SENT_STATUS,
		);

		for( $i = 1; $i <= FAJNTABORY_RESERVATION_MAX_REMINDERS; $i++ ) {

			$statuses[] = fajntabory_get_reservation_reminder_status( $i );

		}

		return $statuses;

	}

	add_action( 'init', 'fajntabory_register_incomplete_order_status' );

	function fajntabory_register_incomplete_order_status() {

		register_post_status( FAJNTABORY_INCOMPLETE_ORDER_POST_STATUS, array(
			'label'                     => _x( 'Nedokončeno', 'Order status', 'woocommerce' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Nedokončeno <span class="count">(%s)</span>', 'Nedokončeno <span class="count">(%s)</span>', 'woocommerce' )
		) );

		register_post_status( 'wc-' . FAJNTABORY_RESERVATION_LINK_SENT_STATUS, array(
			'label'                     => _x( 'Odkaz odeslán', 'Order status', 'woocommerce' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Odkaz odeslán <span class="count">(%s)</span>', 'Odkaz odeslán <span class="count">(%s)</span>', 'woocommerce' )
		) );

		for( $i = 1; $i <= FAJNTABORY_RESERVATION_MAX_REMINDERS; $i++ ) {

			register_post_status( 'wc-' . fajntabory_get_reservation_reminder_status( $i ), array(
				'label'                     => sprintf( _x( 'Připomenutí %d odesláno', 'Order status', 'woocommerce' ), $i ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Připomenutí odesláno <span class="count">(%s)</span>', 'Připomenutí odesláno <span class="count">(%s)</span>', 'woocommerce' )
			) );

		}

	}

	add_filter( 'wc_order_statuses', 'fajntabory_add_incomplete_order_status' );

	function fajntabory_add_incomplete_order_status( $order_statuses ) {

		$new_order_statuses = array();
		$status_added = false;

		foreach( $order_statuses as $status => $label ) {

			$new_order_statuses[ $status ] = $label;

			if( 'wc-pending' === $status ) {

				$new_order_statuses[ FAJNTABORY_INCOMPLETE_ORDER_POST_STATUS ] = _x( 'Nedokončeno', 'Order status', 'woocommerce' );
				$new_order_statuses[ 'wc-' . FAJNTABORY_RESERVATION_LINK_SENT_STATUS ] = _x( 'Odkaz odeslán', 'Order status', 'woocommerce' );

				for( $i = 1; $i <= FAJNTABORY_RESERVATION_MAX_REMINDERS; $i++ ) {

					$new_order_statuses[ 'wc-' . fajntabory_get_reservation_reminder_status( $i ) ] = sprintf( _x( 'Připomenutí %d odesláno', 'Order status', 'woocommerce' ), $i );

				}

				$status_added = true;

			}

		}

		if( ! $status_added ) {

			$new_order_statuses[ FAJNTABORY_INCOMPLETE_ORDER_POST_STATUS ] = _x( 'Nedokončeno', 'Order status', 'woocommerce' );
			$new_order_statuses[ 'wc-' . FAJNTABORY_RESERVATION_LINK_SENT_STATUS ] = _x( 'Odkaz odeslán', 'Order status', 'woocommerce' );

			for( $i = 1; $i <= FAJNTABORY_RESERVATION_MAX_REMINDERS; $i++ ) {

				$new_order_statuses[ 'wc-' . fajntabory_get_reservation_reminder_status( $i ) ] = sprintf( _x( 'Připomenutí %d odesláno', 'Order status', 'woocommerce' ), $i );

			}

		}

		return $new_order_statuses;

	}

	add_action( 'init', 'fajntabory_migrate_pending_reservations_to_incomplete', 20 );

	function fajntabory_migrate_pending_reservations_to_incomplete() {

		if( get_option( 'fajntabory_incomplete_reservation_status_migrated' ) || ! function_exists( 'wc_get_orders' ) ) {

			return;

		}

		$orders = wc_get_orders( array(
			'limit'      => -1,
			'status'     => 'pending',
			'meta_key'   => '_reservation_status',
			'meta_value' => 'pending_completion',
		) );

		foreach( $orders as $order ) {

			if( ! $order instanceof WC_Order ) {

				continue;

			}

			$order->update_status( FAJNTABORY_INCOMPLETE_ORDER_STATUS, 'Nedokončená rezervace byla převedena ze stavu Čeká na platbu.' );
			update_post_meta( fajntabory_get_order_id( $order ), '_fajntabory_incomplete_order', 'yes' );

		}

		update_option( 'fajntabory_incomplete_reservation_status_migrated', current_time( 'mysql' ), false );

	}

	if ( ! function_exists( 'fajntabory_get_checkout_url' ) ) {
		function fajntabory_get_checkout_url() {
			return home_url( '/objednavka/' );
		}
	}

	add_filter( 'body_class', 'fajntabory_reservation_choice_body_class' );

	function fajntabory_reservation_choice_body_class( $classes ) {
		$is_reservation_choice = ! empty( $_GET['reservation'] ) && 'choose' === sanitize_text_field( wp_unslash( $_GET['reservation'] ) );
		$is_reservation_entry = is_page( 'objednavka' ) && empty( $_GET['reservation'] ) && empty( $_GET['rezervace'] );

		if ( $is_reservation_choice ) {
			$classes[] = 'reservation-choice-page';
		}

		if ( $is_reservation_choice || $is_reservation_entry ) {
			$classes[] = 'reservation-compact-header';
		}

		return $classes;
	}

	if ( ! function_exists( 'fajntabory_get_order_id' ) ) {
		function fajntabory_get_order_id( $order ) {
			if ( is_object( $order ) && method_exists( $order, 'get_id' ) ) {
				return $order->get_id();
			}

			return is_object( $order ) && isset( $order->id ) ? $order->id : 0;
		}
	}

	if ( ! function_exists( 'fajntabory_get_conversion_order_from_request' ) ) {
		function fajntabory_get_conversion_order_from_request() {
			if ( empty( $_GET['oid'] ) || ! function_exists( 'wc_get_order' ) ) {
				return false;
			}

			$requested_order_id = wp_unslash( $_GET['oid'] );

			if ( is_array( $requested_order_id ) ) {
				$requested_order_id = reset( $requested_order_id );
			}

			$order_id = absint( $requested_order_id );

			if ( $order_id < 1 ) {
				return false;
			}

			$order = wc_get_order( $order_id );

			return $order instanceof WC_Order ? $order : false;
		}
	}

	if ( ! function_exists( 'fajntabory_get_purchase_event_data' ) ) {
		function fajntabory_get_purchase_event_data( $order ) {
			if ( ! $order instanceof WC_Order ) {
				return array();
			}

			$items = array();

			foreach ( $order->get_items() as $item ) {
				$quantity = max( 1, (int) $item->get_quantity() );
				$product_id = (int) $item->get_product_id();

				if ( method_exists( $item, 'get_variation_id' ) && $item->get_variation_id() ) {
					$product_id = (int) $item->get_variation_id();
				}

				$items[] = array(
					'item_id'   => (string) $product_id,
					'item_name' => wp_strip_all_tags( $item->get_name() ),
					'price'     => (float) wc_format_decimal( (float) $item->get_total() / $quantity, 2 ),
					'quantity'  => $quantity,
				);
			}

			return array(
				'transaction_id' => (string) fajntabory_get_order_id( $order ),
				'value'          => (float) wc_format_decimal( $order->get_total(), 2 ),
				'currency'       => $order->get_currency() ? $order->get_currency() : get_woocommerce_currency(),
				'items'          => $items,
			);
		}
	}

	if ( ! function_exists( 'fajntabory_print_purchase_conversion' ) ) {
		function fajntabory_print_purchase_conversion() {
			$order = fajntabory_get_conversion_order_from_request();

			if ( ! $order ) {
				return;
			}

			$purchase_data = fajntabory_get_purchase_event_data( $order );

			if ( empty( $purchase_data['transaction_id'] ) ) {
				return;
			}

			$ads_data = array(
				'send_to'        => 'AW-809603562/3WcBCO6FmcQCEOqjhoID',
				'transaction_id' => $purchase_data['transaction_id'],
				'value'          => $purchase_data['value'],
				'currency'       => $purchase_data['currency'],
			);

			$data_layer_purchase = array(
				'event'                 => 'purchase',
				'ecommerce'             => $purchase_data,
				'google_ads_conversion' => $ads_data,
			);
			?>
			<!-- Purchase conversion events -->
			<script>
				window.dataLayer = window.dataLayer || [];
				window.dataLayer.push({ ecommerce: null });
				window.dataLayer.push(<?php echo wp_json_encode( $data_layer_purchase ); ?>);

				if (typeof gtag === 'function' && typeof window.google_tag_manager === 'undefined') {
					gtag('event', 'conversion', <?php echo wp_json_encode( $ads_data ); ?>);
					gtag('event', 'purchase', <?php echo wp_json_encode( $purchase_data ); ?>);
				}
			</script>
			<?php
		}
	}

	if ( ! function_exists( 'fajntabory_get_checkout_form_type_from_product_ids' ) ) {
		function fajntabory_get_checkout_form_type_from_product_ids( $product_ids ) {
			$form = null;

			foreach ( $product_ids as $product_id ) {
				$typ_form = get_field( 'typ_form', $product_id );

				if ( 'A' === $typ_form ) {
					$form = 'A';
				} else if ( 'B' === $typ_form && 'A' !== $form ) {
					$form = 'B';
				} else if ( 'C' === $typ_form && 'A' !== $form && 'B' !== $form ) {
					$form = 'C';
				} else if ( 'D' === $typ_form && 'A' !== $form && 'B' !== $form && 'C' !== $form ) {
					$form = 'D';
				}
			}

			return $form;
		}
	}

	if ( ! function_exists( 'fajntabory_get_checkout_form_type' ) ) {
		function fajntabory_get_checkout_form_type( $order = null ) {
			$product_ids = array();

			if ( $order instanceof WC_Order ) {
				foreach ( $order->get_items() as $item ) {
					$product_ids[] = (int) $item->get_product_id();
				}
			} else if ( function_exists( 'WC' ) && WC()->cart ) {
				foreach ( WC()->cart->get_cart() as $values ) {
					$product_ids[] = (int) $values['product_id'];
				}
			}

			return fajntabory_get_checkout_form_type_from_product_ids( $product_ids );
		}
	}

	if ( ! function_exists( 'fajntabory_get_reservation_token' ) ) {
		function fajntabory_get_reservation_token() {
			if ( ! empty( $_GET['rezervace'] ) ) {
				return sanitize_text_field( wp_unslash( $_GET['rezervace'] ) );
			}

			if ( ! empty( $_POST['reservation_token'] ) ) {
				return sanitize_text_field( wp_unslash( $_POST['reservation_token'] ) );
			}

			return '';
		}
	}

	if ( ! function_exists( 'fajntabory_get_reservation_order' ) ) {
		function fajntabory_get_reservation_order( $token = '' ) {
			static $cached_orders = array();

			if ( empty( $token ) ) {
				$token = fajntabory_get_reservation_token();
			}

			if ( empty( $token ) ) {
				return false;
			}

			if ( array_key_exists( $token, $cached_orders ) ) {
				return $cached_orders[ $token ];
			}

			$orders = wc_get_orders(
				array(
					'limit'      => 1,
					'status'     => array_keys( wc_get_order_statuses() ),
					'meta_key'   => '_reservation_token',
					'meta_value' => $token,
				)
			);

			$cached_orders[ $token ] = ! empty( $orders ) ? $orders[0] : false;

			return $cached_orders[ $token ];
		}
	}

	if ( ! function_exists( 'fajntabory_reservation_is_completed' ) ) {
		function fajntabory_reservation_is_completed( $order ) {
			return $order instanceof WC_Order && 'completed' === $order->get_meta( '_reservation_status' );
		}
	}

	if ( ! function_exists( 'fajntabory_get_checkout_coupon_code' ) ) {
		function fajntabory_get_checkout_coupon_code( $order = null ) {
			if ( $order instanceof WC_Order ) {
				return (string) $order->get_meta( 'coupon_code' );
			}

			if ( function_exists( 'WC' ) && WC()->cart && ! empty( WC()->cart->applied_coupons[0] ) ) {
				return (string) WC()->cart->applied_coupons[0];
			}

			return '';
		}
	}

	if ( ! function_exists( 'fajntabory_checkout_field_value' ) ) {
		function fajntabory_checkout_field_value( $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				return is_array( $_POST[ $field ] ) ? '' : wp_unslash( $_POST[ $field ] );
			}

			$order = fajntabory_get_reservation_order();

			if ( ! $order ) {
				return '';
			}

			if ( 'telefon' === $field ) {
				return $order->get_billing_phone() ? $order->get_billing_phone() : $order->get_meta( 'telefon' );
			}

			if ( 'email' === $field || 'email-check' === $field ) {
				return $order->get_billing_email() ? $order->get_billing_email() : $order->get_meta( 'email' );
			}

			return $order->get_meta( $field );
		}
	}

	if ( ! function_exists( 'fajntabory_get_recaptcha_site_key' ) ) {
		function fajntabory_get_recaptcha_site_key() {
			if ( ! class_exists( 'WPCF7_RECAPTCHA' ) ) {
				return '';
			}

			$recaptcha = WPCF7_RECAPTCHA::get_instance();

			if ( ! $recaptcha->is_active() ) {
				return '';
			}

			return (string) $recaptcha->get_sitekey();
		}
	}

	if ( ! function_exists( 'fajntabory_verify_recaptcha_token' ) ) {
		function fajntabory_verify_recaptcha_token( $token ) {
			if ( empty( $token ) || ! class_exists( 'WPCF7_RECAPTCHA' ) ) {
				return false;
			}

			$recaptcha = WPCF7_RECAPTCHA::get_instance();

			if ( ! $recaptcha->is_active() ) {
				return false;
			}

			return (bool) $recaptcha->verify( $token );
		}
	}

	if ( ! function_exists( 'fajntabory_get_client_ip' ) ) {
		function fajntabory_get_client_ip() {
			$server_keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' );

			foreach ( $server_keys as $server_key ) {
				if ( empty( $_SERVER[ $server_key ] ) || is_array( $_SERVER[ $server_key ] ) ) {
					continue;
				}

				$ip = trim( explode( ',', wp_unslash( $_SERVER[ $server_key ] ) )[0] );

				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}

			return 'unknown';
		}
	}

	if ( ! function_exists( 'fajntabory_rate_limit_key' ) ) {
		function fajntabory_rate_limit_key( $scope, $identifier ) {
			$identifier_hash = hash_hmac( 'sha256', strtolower( trim( (string) $identifier ) ), wp_salt( 'auth' ) );

			return 'fajn_rl_' . md5( $scope . '|' . $identifier_hash );
		}
	}

	if ( ! function_exists( 'fajntabory_check_rate_limit' ) ) {
		function fajntabory_check_rate_limit( $scope, $identifier, $limit, $window ) {
			$limit = (int) $limit;
			$window = (int) $window;

			if ( $limit < 1 || $window < 1 || '' === (string) $identifier ) {
				return false;
			}

			$key = fajntabory_rate_limit_key( $scope, $identifier );
			$count = (int) get_transient( $key );

			if ( $count >= $limit ) {
				return false;
			}

			set_transient( $key, $count + 1, $window );

			return true;
		}
	}

	if ( ! function_exists( 'fajntabory_reservation_rate_limit_passed' ) ) {
		function fajntabory_reservation_rate_limit_passed( $email ) {
			$ip = fajntabory_get_client_ip();
			$checks = array(
				array( 'reservation_site_hour', 'site', (int) apply_filters( 'fajntabory_reservation_site_hour_limit', 60 ), HOUR_IN_SECONDS ),
				array( 'reservation_site_day', 'site', (int) apply_filters( 'fajntabory_reservation_site_day_limit', 200 ), DAY_IN_SECONDS ),
				array( 'reservation_ip_hour', $ip, (int) apply_filters( 'fajntabory_reservation_ip_hour_limit', 5 ), HOUR_IN_SECONDS ),
				array( 'reservation_ip_day', $ip, (int) apply_filters( 'fajntabory_reservation_ip_day_limit', 20 ), DAY_IN_SECONDS ),
				array( 'reservation_email_hour', $email, (int) apply_filters( 'fajntabory_reservation_email_hour_limit', 2 ), HOUR_IN_SECONDS ),
				array( 'reservation_email_day', $email, (int) apply_filters( 'fajntabory_reservation_email_day_limit', 4 ), DAY_IN_SECONDS ),
			);

			foreach ( $checks as $check ) {
				if ( ! fajntabory_check_rate_limit( $check[0], $check[1], $check[2], $check[3] ) ) {
					return false;
				}
			}

			return true;
		}
	}

	if ( ! function_exists( 'fajntabory_reservation_email_allowed' ) ) {
		function fajntabory_reservation_email_allowed( $order ) {
			if ( ! $order instanceof WC_Order || ! is_email( $order->get_billing_email() ) ) {
				return false;
			}

			$verified_at = (string) $order->get_meta( '_reservation_security_verified_at' );

			if ( empty( $verified_at ) && ! apply_filters( 'fajntabory_allow_unverified_reservation_email', false, $order ) ) {
				return false;
			}

			return true;
		}
	}

	if ( ! function_exists( 'fajntabory_clean_order_post_value' ) ) {
		function fajntabory_clean_order_post_value( $key, $value ) {
			if ( is_array( $value ) ) {
				return array_map( function( $item ) use ( $key ) {
					return fajntabory_clean_order_post_value( $key, $item );
				}, $value );
			}

			$value = wp_unslash( $value );

			if ( in_array( $key, array( 'email', 'email-check' ), true ) ) {
				return sanitize_email( $value );
			}

			if ( in_array( $key, array( 'zpusobilost', 'zamestnavatel', 'poznamky', 'dalsi_tabornici' ), true ) ) {
				return sanitize_textarea_field( $value );
			}

			return sanitize_text_field( $value );
		}
	}

	if ( ! function_exists( 'fajntabory_sanitize_order_post' ) ) {
		function fajntabory_sanitize_order_post( $post ) {
			$clean = array();

			foreach ( $post as $key => $value ) {
				$clean_key = sanitize_key( $key );

				if ( '' === $clean_key ) {
					continue;
				}

				$clean[ $clean_key ] = fajntabory_clean_order_post_value( $clean_key, $value );
			}

			return $clean;
		}
	}

	if ( ! function_exists( 'fajntabory_abort_checkout' ) ) {
		function fajntabory_abort_checkout( $message, $redirect_url = '' ) {
			wc_add_notice( $message, 'error' );
			wp_safe_redirect( $redirect_url ? $redirect_url : fajntabory_get_checkout_url() );
			exit;
		}
	}

	if ( ! function_exists( 'fajntabory_get_order_bank_account_number' ) ) {
		function fajntabory_get_order_bank_account_number( $order ) {
			$bank_account_number = get_option( 'bank_account' );

			if ( ! $order instanceof WC_Order ) {
				return $bank_account_number;
			}

			foreach ( $order->get_items() as $item ) {
				$variation_id = $item->get_variation_id();

				if ( empty( $variation_id ) ) {
					continue;
				}

				$variation = wc_get_product( $variation_id );

				if ( ! $variation || ! method_exists( $variation, 'get_attributes' ) ) {
					continue;
				}

				$variation_slug = $variation->get_attributes();
				$variation_slug = ! empty( $variation_slug['pa_terminy'] ) ? $variation_slug['pa_terminy'] : '';

				if ( empty( $variation_slug ) ) {
					continue;
				}

				$term = get_term_by( 'slug', $variation_slug, 'pa_terminy' );

				if ( empty( $term->term_id ) ) {
					continue;
				}

				$term_meta = get_option( 'taxonomy_' . $term->term_id );
				$term_meta = ! empty( $term_meta['ucet'] ) ? $term_meta['ucet'] : '';

				if ( ! empty( $term_meta ) ) {
					$bank_account_number = $term_meta;
				}
			}

			return $bank_account_number;
		}
	}

	if ( ! function_exists( 'fajntabory_get_reservation_complete_url' ) ) {
		function fajntabory_get_reservation_complete_url( $token ) {
			return add_query_arg( 'rezervace', rawurlencode( $token ), fajntabory_get_checkout_url() );
		}
	}

	if ( ! function_exists( 'fajntabory_get_reservation_choice_url' ) ) {
		function fajntabory_get_reservation_choice_url( $token ) {
			return add_query_arg(
				array(
					'reservation' => 'choose',
					'rezervace'   => rawurlencode( $token ),
				),
				fajntabory_get_checkout_url()
			);
		}
	}

	if ( ! function_exists( 'fajntabory_get_reservation_camp_summary' ) ) {
		function fajntabory_get_reservation_camp_summary( $order ) {
			if ( ! $order instanceof WC_Order ) {
				return '';
			}

			$summary = '<ul>';

			foreach ( $order->get_items() as $item ) {
				$item_meta = wc_display_item_meta( $item, array( 'echo' => false ) );
				$summary .= '<li>' . esc_html( $item->get_name() ) . ' - ' . wp_kses_post( $order->get_formatted_line_subtotal( $item ) );

				if ( ! empty( $item_meta ) ) {
					$summary .= '<div>' . wp_kses_post( $item_meta ) . '</div>';
				}

				$summary .= '</li>';
			}

			$summary .= '</ul>';

			return $summary;
		}
	}

	if ( ! function_exists( 'fajntabory_get_reservation_email_defaults' ) ) {
		function fajntabory_get_reservation_email_defaults() {
			return array(
				'link_subject'      => 'Dokončete přihlášku k táboru',
				'link_body'         => '<p>Dobrý den,</p><p>vaši rezervaci jsme přijali pod číslem {order_id}.</p><p>Pro dokončení přihlášky prosím otevřete tento odkaz:</p><p><a href="{complete_url}">{complete_url}</a></p><p>Rezervovaný tábor:</p>{camp_summary}<p>Po otevření odkazu doplníte zbývající údaje k táboru a teprve potom vám pošleme finální potvrzení a platební pokyny.</p><p>FajnTábory</p>',
				'reminder_subject'  => 'Připomenutí: dokončete přihlášku k táboru',
				'reminder_body'     => '<p>Dobrý den,</p><p>připomínáme dokončení vaší přihlášky k táboru pod číslem {order_id}.</p><p>Pokračovat můžete zde:</p><p><a href="{complete_url}">{complete_url}</a></p><p>Rezervovaný tábor:</p>{camp_summary}<p>FajnTábory</p>',
			);
		}
	}

	if ( ! function_exists( 'fajntabory_get_reservation_email_template' ) ) {
		function fajntabory_get_reservation_email_template( $key ) {
			$defaults = fajntabory_get_reservation_email_defaults();
			$value = get_option( 'fajntabory_reservation_' . $key );

			return '' !== (string) $value ? (string) $value : $defaults[ $key ];
		}
	}

	if ( ! function_exists( 'fajntabory_render_reservation_email_template' ) ) {
		function fajntabory_render_reservation_email_template( $template, $order, $token, $reminder_number = 0 ) {
			$order_id = fajntabory_get_order_id( $order );
			$complete_url = fajntabory_get_reservation_complete_url( $token );
			$replacements = array(
				'{order_id}'        => esc_html( $order_id ),
				'{complete_url}'    => esc_url( $complete_url ),
				'{customer_email}'  => esc_html( $order->get_billing_email() ),
				'{customer_phone}'  => esc_html( $order->get_billing_phone() ),
				'{camp_summary}'    => fajntabory_get_reservation_camp_summary( $order ),
				'{reminder_number}' => esc_html( $reminder_number ),
				'{site_name}'       => esc_html( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ),
			);

			return strtr( $template, $replacements );
		}
	}

	if ( ! function_exists( 'fajntabory_send_reservation_email' ) ) {
		function fajntabory_send_reservation_email( $order, $token, $email_type = 'link', $reminder_number = 0 ) {
			global $woocommerce;

			if ( ! $order instanceof WC_Order ) {
				return false;
			}

			$email = $order->get_billing_email();

			if ( empty( $email ) || ! fajntabory_reservation_email_allowed( $order ) ) {
				return false;
			}

			if ( (int) $order->get_meta( '_reservation_email_send_count' ) >= FAJNTABORY_RESERVATION_MAX_REMINDERS + FAJNTABORY_RESERVATION_LINK_MAX_SENDS ) {
				return false;
			}

			if (
				! fajntabory_check_rate_limit( 'reservation_mail_site_hour', 'site', (int) apply_filters( 'fajntabory_reservation_mail_site_hour_limit', 80 ), HOUR_IN_SECONDS )
				|| ! fajntabory_check_rate_limit( 'reservation_mail_recipient_day', $email, (int) apply_filters( 'fajntabory_reservation_mail_recipient_day_limit', 8 ), DAY_IN_SECONDS )
			) {
				return false;
			}

			$mailer = $woocommerce->mailer();
			$subject_key = 'reminder' === $email_type ? 'reminder_subject' : 'link_subject';
			$body_key = 'reminder' === $email_type ? 'reminder_body' : 'link_body';
			$subject = wp_strip_all_tags( fajntabory_render_reservation_email_template( fajntabory_get_reservation_email_template( $subject_key ), $order, $token, $reminder_number ) );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			$headers[] = 'From: Fajn Tábory <tereza@fajntabory.cz>';
			$message_body = wp_kses_post( fajntabory_render_reservation_email_template( fajntabory_get_reservation_email_template( $body_key ), $order, $token, $reminder_number ) );

			$message = $mailer->wrap_message( $subject, $message_body );

			return (bool) $mailer->send( $email, $subject, $message, $headers );
		}
	}

	if ( ! function_exists( 'fajntabory_mark_reservation_link_sent' ) ) {
		function fajntabory_mark_reservation_link_sent( $order, $source = 'manual' ) {
			if ( ! $order instanceof WC_Order ) {
				return;
			}

			$order_id = fajntabory_get_order_id( $order );
			$sent_at = current_time( 'mysql' );

			if ( '' === (string) $order->get_meta( '_reservation_email_sent_at' ) ) {
				update_post_meta( $order_id, '_reservation_email_sent_at', $sent_at );
			}

			update_post_meta( $order_id, '_reservation_email_last_sent_at', $sent_at );
			update_post_meta( $order_id, '_reservation_email_last_source', $source );
			update_post_meta( $order_id, '_reservation_email_send_count', (int) $order->get_meta( '_reservation_email_send_count' ) + 1 );
			update_post_meta( $order_id, '_reservation_link_email_send_count', (int) $order->get_meta( '_reservation_link_email_send_count' ) + 1 );

			if ( ! fajntabory_reservation_is_completed( $order ) && $order->has_status( array( FAJNTABORY_INCOMPLETE_ORDER_STATUS, 'pending' ) ) ) {
				$order->update_status( FAJNTABORY_RESERVATION_LINK_SENT_STATUS, 'Odkaz k dokončení objednávky byl odeslán e-mailem.' );
			} else {
				$order->add_order_note( 'Odkaz k dokončení objednávky byl odeslán e-mailem.' );
			}
		}
	}

	if ( ! function_exists( 'fajntabory_mark_reservation_reminder_sent' ) ) {
		function fajntabory_mark_reservation_reminder_sent( $order, $reminder_number ) {
			if ( ! $order instanceof WC_Order ) {
				return;
			}

			$reminder_number = max( 1, min( FAJNTABORY_RESERVATION_MAX_REMINDERS, (int) $reminder_number ) );
			$order_id = fajntabory_get_order_id( $order );
			$sent_at = current_time( 'mysql' );
			$note = sprintf( 'Připomenutí %d odesláno.', $reminder_number );

			update_post_meta( $order_id, '_reservation_reminder_count', $reminder_number );
			update_post_meta( $order_id, '_reservation_reminder_' . $reminder_number . '_sent_at', $sent_at );
			update_post_meta( $order_id, '_reservation_email_last_sent_at', $sent_at );
			update_post_meta( $order_id, '_reservation_email_last_source', 'reminder_' . $reminder_number );
			update_post_meta( $order_id, '_reservation_email_send_count', (int) $order->get_meta( '_reservation_email_send_count' ) + 1 );

			if ( ! fajntabory_reservation_is_completed( $order ) ) {
				$order->update_status( fajntabory_get_reservation_reminder_status( $reminder_number ), $note );
			} else {
				$order->add_order_note( $note );
			}
		}
	}

	if ( ! function_exists( 'fajntabory_get_reservation_reminder_intervals' ) ) {
		function fajntabory_get_reservation_reminder_intervals() {
			$raw_intervals = (string) get_option( 'fajntabory_reservation_reminder_intervals_hours', '24,72,168' );
			$intervals = array();

			foreach ( explode( ',', $raw_intervals ) as $interval ) {
				$interval = (int) trim( $interval );

				if ( $interval > 0 ) {
					$intervals[] = $interval;
				}
			}

			return array_slice( $intervals, 0, FAJNTABORY_RESERVATION_MAX_REMINDERS );
		}
	}

	if ( ! function_exists( 'fajntabory_reservation_time_to_timestamp' ) ) {
		function fajntabory_reservation_time_to_timestamp( $time ) {
			if ( empty( $time ) ) {
				return 0;
			}

			$timestamp = strtotime( $time );

			return false === $timestamp ? 0 : $timestamp;
		}
	}

	if ( ! function_exists( 'fajntabory_schedule_reservation_reminders' ) ) {
		add_action( 'init', 'fajntabory_schedule_reservation_reminders' );

		function fajntabory_schedule_reservation_reminders() {
			if ( ! wp_next_scheduled( 'fajntabory_process_reservation_reminders' ) ) {
				wp_schedule_event( time() + 300, 'hourly', 'fajntabory_process_reservation_reminders' );
			}
		}
	}

	if ( ! function_exists( 'fajntabory_process_reservation_reminders' ) ) {
		add_action( 'fajntabory_process_reservation_reminders', 'fajntabory_process_reservation_reminders' );

		function fajntabory_process_reservation_reminders() {
			if ( ! function_exists( 'wc_get_orders' ) ) {
				return;
			}

			$orders = wc_get_orders( array(
				'limit'      => 50,
				'status'     => array_merge( fajntabory_get_reservation_tracking_statuses(), array( 'pending' ) ),
				'meta_key'   => '_reservation_status',
				'meta_value' => 'pending_completion',
				'orderby'    => 'date',
				'order'      => 'ASC',
			) );

			foreach ( $orders as $order ) {
				if ( ! $order instanceof WC_Order || fajntabory_reservation_is_completed( $order ) ) {
					continue;
				}

				$token = (string) $order->get_meta( '_reservation_token' );

				if ( empty( $token ) || empty( $order->get_billing_email() ) ) {
					continue;
				}

				$order_id = fajntabory_get_order_id( $order );

				if ( ! fajntabory_reservation_email_allowed( $order ) ) {
					if ( '' === (string) $order->get_meta( '_reservation_email_blocked_at' ) ) {
						update_post_meta( $order_id, '_reservation_email_blocked_at', current_time( 'mysql' ) );
						$order->add_order_note( 'Automatické rezervační e-maily byly zablokovány, protože rezervace neprošla kontrolou proti spamu.' );
					}

					continue;
				}

				$email_sent_at = (string) $order->get_meta( '_reservation_email_sent_at' );

				if ( empty( $email_sent_at ) ) {
					if ( 'yes' !== get_option( 'fajntabory_reservation_auto_link_enabled', 'yes' ) ) {
						continue;
					}

					$opened_at = fajntabory_reservation_time_to_timestamp( $order->get_meta( '_reservation_completion_form_opened_at' ) );
					$reservation_created_at = fajntabory_reservation_time_to_timestamp( $order->get_meta( '_reservation_created_at' ) );
					$auto_link_base_time = $opened_at > 0 ? $opened_at : $reservation_created_at;
					$auto_link_source = $opened_at > 0 ? 'auto_after_open_form' : 'auto_after_abandoned_choice';
					$delay_minutes = max( 5, (int) get_option( 'fajntabory_reservation_auto_link_delay_minutes', 60 ) );

					if ( $auto_link_base_time > 0 && time() >= $auto_link_base_time + ( $delay_minutes * MINUTE_IN_SECONDS ) ) {
						if ( fajntabory_send_reservation_email( $order, $token, 'link' ) ) {
							fajntabory_mark_reservation_link_sent( $order, $auto_link_source );
						} else {
							update_post_meta( $order_id, '_reservation_email_last_error_at', current_time( 'mysql' ) );
							$order->add_order_note( 'Automatické odeslání odkazu k dokončení objednávky selhalo.' );
						}
					}

					continue;
				}

				if ( 'yes' !== get_option( 'fajntabory_reservation_reminders_enabled', 'yes' ) ) {
					continue;
				}

				$intervals = fajntabory_get_reservation_reminder_intervals();
				$reminder_count = (int) $order->get_meta( '_reservation_reminder_count' );
				$next_reminder = $reminder_count + 1;

				if ( empty( $intervals[ $next_reminder - 1 ] ) ) {
					continue;
				}

				$email_sent_timestamp = fajntabory_reservation_time_to_timestamp( $email_sent_at );

				if ( $email_sent_timestamp > 0 && time() >= $email_sent_timestamp + ( (int) $intervals[ $next_reminder - 1 ] * HOUR_IN_SECONDS ) ) {
					if ( fajntabory_send_reservation_email( $order, $token, 'reminder', $next_reminder ) ) {
						fajntabory_mark_reservation_reminder_sent( $order, $next_reminder );
					} else {
						update_post_meta( $order_id, '_reservation_email_last_error_at', current_time( 'mysql' ) );
						$order->add_order_note( sprintf( 'Odeslání připomenutí %d selhalo.', $next_reminder ) );
					}
				}
			}
		}
	}

	if ( ! function_exists( 'fajntabory_get_reservation_reminder_settings' ) ) {
		function fajntabory_get_reservation_reminder_settings() {
			$defaults = fajntabory_get_reservation_email_defaults();

			return array(
				array(
					'title' => 'Dokončování přihlášek',
					'type'  => 'title',
					'desc'  => 'Nastavení automatického odesílání odkazu a reminderů pro nedokončené předběžné objednávky.',
					'id'    => 'fajntabory_reservation_reminders_options',
				),
				array(
					'title'   => 'Automatický první odkaz',
					'id'      => 'fajntabory_reservation_auto_link_enabled',
					'default' => 'yes',
					'type'    => 'checkbox',
					'desc'    => 'Poslat odkaz automaticky, když zákazník nedokončí rozdělovník nebo otevře formulář „Doplnit údaje hned“ a nedokončí ho.',
				),
				array(
					'title'             => 'Zpoždění prvního odkazu',
					'id'                => 'fajntabory_reservation_auto_link_delay_minutes',
					'default'           => '60',
					'type'              => 'number',
					'desc'              => 'Počet minut od vytvoření předběžné objednávky nebo od otevření formuláře. Doporučeno: 60.',
					'custom_attributes' => array(
						'min'  => '5',
						'step' => '1',
					),
				),
				array(
					'title'   => 'Reminder e-maily',
					'id'      => 'fajntabory_reservation_reminders_enabled',
					'default' => 'yes',
					'type'    => 'checkbox',
					'desc'    => 'Posílat další připomenutí po odeslání prvního odkazu.',
				),
				array(
					'title'   => 'Intervaly reminderů',
					'id'      => 'fajntabory_reservation_reminder_intervals_hours',
					'default' => '24,72,168',
					'type'    => 'text',
					'desc'    => 'Časy v hodinách od odeslání prvního odkazu, oddělené čárkou. Např. 24,72,168. Eviduje se max. 5 reminderů.',
				),
				array(
					'title' => 'Proměnné v šablonách',
					'type'  => 'title',
					'desc'  => 'Použitelné proměnné: {order_id}, {complete_url}, {customer_email}, {customer_phone}, {camp_summary}, {reminder_number}, {site_name}.',
					'id'    => 'fajntabory_reservation_template_variables',
				),
				array(
					'title'   => 'Předmět prvního odkazu',
					'id'      => 'fajntabory_reservation_link_subject',
					'default' => $defaults['link_subject'],
					'type'    => 'text',
				),
				array(
					'title'   => 'Text prvního odkazu',
					'id'      => 'fajntabory_reservation_link_body',
					'default' => $defaults['link_body'],
					'type'    => 'textarea',
					'css'     => 'min-width: 520px; min-height: 190px;',
				),
				array(
					'title'   => 'Předmět reminderu',
					'id'      => 'fajntabory_reservation_reminder_subject',
					'default' => $defaults['reminder_subject'],
					'type'    => 'text',
				),
				array(
					'title'   => 'Text reminderu',
					'id'      => 'fajntabory_reservation_reminder_body',
					'default' => $defaults['reminder_body'],
					'type'    => 'textarea',
					'css'     => 'min-width: 520px; min-height: 190px;',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'fajntabory_reservation_reminders_options',
				),
			);
		}
	}

	add_filter( 'woocommerce_settings_tabs_array', 'fajntabory_add_reservation_reminder_settings_tab', 50 );

	function fajntabory_add_reservation_reminder_settings_tab( $settings_tabs ) {
		$settings_tabs['fajntabory_reservations'] = 'FajnTábory přihlášky';

		return $settings_tabs;
	}

	add_action( 'woocommerce_settings_tabs_fajntabory_reservations', 'fajntabory_render_reservation_reminder_settings' );

	function fajntabory_render_reservation_reminder_settings() {
		woocommerce_admin_fields( fajntabory_get_reservation_reminder_settings() );
	}

	add_action( 'woocommerce_update_options_fajntabory_reservations', 'fajntabory_save_reservation_reminder_settings' );

	function fajntabory_save_reservation_reminder_settings() {
		woocommerce_update_options( fajntabory_get_reservation_reminder_settings() );

		foreach ( array( 'link_body', 'reminder_body' ) as $body_key ) {
			$option_name = 'fajntabory_reservation_' . $body_key;

			if ( isset( $_POST[ $option_name ] ) ) {
				update_option( $option_name, wp_kses_post( wp_unslash( $_POST[ $option_name ] ) ) );
			}
		}
	}

	add_filter(
		'woocommerce_checkout_redirect_empty_cart',
		function( $redirect ) {
			if ( ! empty( $_GET['rezervace'] ) ) {
				return false;
			}

			if ( ! empty( $_GET['reservation'] ) && 'sent' === $_GET['reservation'] ) {
				return false;
			}

			return $redirect;
		}
	);



	function handsome_bearded_guy_increase_variations_per_page() {

		return 200;

	}



	add_shortcode( '.....', function() {

		return '<span class="space"></span>';

	} );



	add_filter('wp_get_attachment_link', 'rc_add_rel_attribute');

	function rc_add_rel_attribute($link) {

		global $post;

		return str_replace('<a href', '<a data-rel="prettyPhoto[gallery]" href', $link);

	}



	function change_quantity_input( $product_quantity, $cart_item_key, $cart_item ) {

	    $product_id = $cart_item['product_id'];

	    return '<strong>' . $cart_item['quantity'] . '</strong>';

	}

	add_filter( 'woocommerce_cart_item_quantity', 'change_quantity_input', 10, 3);



	/**

 	 * Setup Theme

 	 */

 	

 	add_action( 'after_setup_theme', function() {



 		require_once( dirname(__FILE__) . '/functions/breadcrumbs.php' );

 		require_once( dirname(__FILE__) . '/functions/vedouci.php' );

 		require_once( dirname(__FILE__) . '/functions/galerie.php' );

 		require_once( dirname(__FILE__) . '/functions/calendar.php' );

 		require_once( dirname(__FILE__) . '/functions/fakturace.php' );



 		add_theme_support( 'title-tag' );

 		add_theme_support( 'post-thumbnails' );

 		add_theme_support( 'woocommerce' );



 		register_nav_menus( array(

			'main_menu' => 'Hlavní nabídka'

		) );



		add_image_size( 'logo', 9999, 70 );

		add_image_size( 'gallery', 320, 240, true );

		add_image_size( 'listing', 320, 320, true );

		add_image_size( 'double-listing', 320, 640, true );

		add_image_size( 'slider', 1920, 1080, true );

		add_image_size( 'hp', 1920, 9999 );



 	} );



 	/**

 	 * Register Sidebar

 	 */

 	

 	add_action( 'widgets_init', function() {



 		register_sidebar( array(

	        'name' => __( 'Patička, první sloupec', 'fajntabory' ),

	        'id' => 'footer-1',

	        'description' => __( 'Prostor pro zobrazení např. kontaktů, odkazů na další informace apod.', 'fajntabory' ),

	        'before_widget' => '<div>',

			'after_widget'  => '</div>',

			'before_title'  => '<h2>',

			'after_title'   => '</h2>',

		) );

		register_sidebar( array(

	        'name' => __( 'Patička, druhý sloupec', 'fajntabory' ),

	        'id' => 'footer-2',

	        'description' => __( 'Prostor pro zobrazení např. kontaktů, odkazů na další informace apod.', 'fajntabory' ),

	        'before_widget' => '<div>',

			'after_widget'  => '</div>',

			'before_title'  => '<h2>',

			'after_title'   => '</h2>',

		) );

		register_sidebar( array(

	        'name' => __( 'Patička, třetí sloupec', 'fajntabory' ),

	        'id' => 'footer-3',

	        'description' => __( 'Prostor pro zobrazení např. kontaktů, odkazů na další informace apod.', 'fajntabory' ),

	        'before_widget' => '<div>',

			'after_widget'  => '</div>',

			'before_title'  => '<h2>',

			'after_title'   => '</h2>',

		) );

		register_sidebar( array(

	        'name' => __( 'Patička, čtvrtý sloupec', 'fajntabory' ),

	        'id' => 'footer-4',

	        'description' => __( 'Prostor pro zobrazení např. kontaktů, odkazů na další informace apod.', 'fajntabory' ),

	        'before_widget' => '<div>',

			'after_widget'  => '</div>',

			'before_title'  => '<h2>',

			'after_title'   => '</h2>',

		) );



 	} );



	/**

	 * Enqueue styles

	 */



	add_action('wp_enqueue_scripts', function() {

		wp_enqueue_style( 'font-dosis', 'https://fonts.googleapis.com/css?family=Dosis:400,600,700,800&amp;subset=latin-ext' );

		wp_enqueue_style( 'font-open-sans', 'https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700,700i&amp;subset=latin-ext' );

		wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );

		wp_enqueue_style( 'font-pacifico', 'https://fonts.googleapis.com/css?family=Pacifico&amp;subset=latin-ext' );

		wp_enqueue_style( 'bxslider', get_template_directory_uri() . '/assets/styles/jquery.bxslider.css' );

		wp_enqueue_style( 'general', get_stylesheet_uri() . '?' . time() );

		wp_enqueue_style( 'mobile', get_template_directory_uri() . '/assets/styles/mobile.css' . '?' . time() );



	} );



	/**

	 * Enqueue scripts

	 */



	add_action('wp_enqueue_scripts', function() {

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js' );

		wp_enqueue_script( 'bxslider', get_template_directory_uri() . '/assets/scripts/jquery.bxslider.js' );

		wp_enqueue_script( 'mask', get_template_directory_uri() . '/assets/scripts/jquery.mask.js' );

		wp_enqueue_script( 'vide', get_template_directory_uri() . '/assets/scripts/jquery.vide.js' );

		wp_enqueue_script( 'general', get_template_directory_uri() . '/assets/scripts/general.js?' . time() );

		wp_localize_script( 'general', 'ajax_object', array(
			'ajax_url'             => admin_url( 'admin-ajax.php' ),
			'product_choice_nonce' => wp_create_nonce( 'fajntabory_product_choice' ),
		) );

	} );



	/**

	 * Enqueue admin styles

	 */



	add_action('admin_enqueue_scripts', function() {

		wp_enqueue_style( 'theme-options', get_template_directory_uri() . '/assets/styles/admin.css' );

	} );





	/**

	 * Add Theme Options Panel

	 */

	

	add_action( 'admin_menu', function() { 

		add_theme_page( 

			'Možnosti',

			'Možnosti',

			'manage_options',

			'theme-options',

			'theme_options'

		);

	} );



	function theme_options() {



		echo '<div class="wrap">';

		echo '<h1>Nastavení šablony</h1>';



		if( isset( $_GET['updated'] ) && $_GET['updated'] == 'true' ) {

			echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">';

			echo '<p><strong>Nastavení bylo uloženo.</strong></p>';

			echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Skrýt toto upozornění.</span></button>';

			echo '</div>';

		}



		if( isset( $_GET['uploaded'] ) && $_GET['uploaded'] == 'false' ) {

			echo '<div id="setting-error-settings_updated" class="error settings-error notice is-dismissible">';

			echo '<p><strong>Import souboru se nepodařilo dokončit!</strong></p>';

			echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Skrýt toto upozornění.</span></button>';

			echo '</div>';

		}



		if( isset( $_GET['uploaded'] ) && $_GET['uploaded'] == 'true' ) {

			echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">';

			echo '<p><strong>Import souboru byl dokončen.</strong></p>';

			echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Skrýt toto upozornění.</span></button>';

			echo '</div>';

		}



		echo '<div class="themepanel">';



		echo '<div class="option_box">';



		echo '<h2>Export objednávek</h2>';

		echo '<p>';

		echo '<a href="'.admin_url( 'themes.php?page=theme-options&orders=true&donotcachepage=d3d3d224b5b6e41c3fab2822006f9ec5' ).'" target="_blank" class="button">Exportovat objednávky</a>';

		echo '</p>';

		echo '<h2>Export táborů</h2>';

		echo '<p>';

		echo '<a href="'.admin_url( 'themes.php?page=theme-options&export=true&donotcachepage=d3d3d224b5b6e41c3fab2822006f9ec5' ).'" target="_blank" class="button">Exportovat tábory</a>';

		echo '</p>';

		echo '<h2>Export dopravy</h2>';

		echo '<p>';


		echo '<a href="'.admin_url( 'themes.php?page=theme-options&export-transport=true' ).'" target="_blank" class="button">Exportovat společnou dopravu</a>';

		echo '</p>';

		echo '<h2>Import táborů</h2>';

		echo '<form action="'.admin_url( 'themes.php?page=theme-options' ).'" method="POST" enctype="multipart/form-data">';

		echo '<p>';

		echo 'Níže vyberte .csv soubor, který chcete importovat.';

		echo '</p>';

		echo '<p>';

		echo '<input type="hidden" name="csv" value="true">';
		wp_nonce_field( 'fajntabory_import_csv', 'fajntabory_csv_nonce' );

		echo '<input type="file" name="importcsv" accept=".csv">';

		echo '<input type="submit" class="button" value="Importovat!">';

		echo '</p>';

		echo '</form>';

		echo '<h2>Import dopravy</h2>';

		echo '<form action="'.admin_url( 'themes.php?page=theme-options' ).'" method="POST" enctype="multipart/form-data">';

		echo '<p>';

		echo 'Níže vyberte .csv soubor, který chcete importovat.';

		echo '</p>';

		echo '<p>';

		echo '<input type="hidden" name="tcsv" value="true">';
		wp_nonce_field( 'fajntabory_import_tcsv', 'fajntabory_tcsv_nonce' );

		echo '<input type="file" name="importcsv" accept=".csv">';

		echo '<input type="submit" class="button" value="Importovat!">';

		echo '</p>';

		echo '</form>';

		

		echo '</div>';



		echo '<form action="'.admin_url( 'themes.php?page=theme-options' ).'" method="POST">';



		echo '<div class="option_box">';

			

			echo '<h2>Logo webu</h2>';

			wp_enqueue_media();

			echo '<p>';

			$logo = get_option('website_logo');

			if( !empty( $logo ) ) {

				echo '<a href="#" class="logopicker">';

				echo '<img id="image-preview" src="'.esc_url( wp_get_attachment_image_src( get_option( 'website_logo' ), 'logo' )[0] ).'">';

				echo '</a>';

			} else {

				echo '<button class="button logopicker">Vybrat soubor</button>';

			}

			echo '<input type="hidden" id="website_logo" name="website_logo" value="'.esc_attr( get_option('website_logo') ).'">';

			echo '</p>';



		echo '</div>';



		echo '<div class="option_box">';

			echo '<h2>Bankovní účet</h2>';

			echo '<p>';

			echo '<label for="bank_account">Číslo bankovního účtu</label>';

			echo '<input type="text" id="bank_account" name="bank_account" value="'.esc_attr( get_option('bank_account') ).'">';

			echo '</p>';

			echo '<p>';

			echo '<label for="bank_iban">Mezinárodní číslo bankovního účtu (IBAN)</label>';

			echo '<input type="text" id="bank_iban" name="bank_iban" value="'.esc_attr( get_option('bank_iban') ).'">';

			echo '</p>';

			echo '<p>';

			echo '<label for="bank_swift">SWIFT / BIC kód banky</label>';

			echo '<input type="text" id="bank_swift" name="bank_swift" value="'.esc_attr( get_option('bank_swift') ).'">';

			echo '</p>';

			// IBAN

			// SWIFT

		echo '</div>';



		echo '<div class="option_box">';

			

			echo '<h2>Sociální sítě</h2>';



			echo '<p>';

			echo '<label for="facebook_uri">Odkaz na Facebook profil</label>';

			echo '<input type="text" id="facebook_uri" name="facebook_uri" value="'.esc_url( get_option('facebook_uri') ).'">';

			echo '</p>';



			echo '<p>';

			echo '<label for="youtube_uri">Odkaz na YouTube profil</label>';

			echo '<input type="text" id="youtube_uri" name="youtube_uri" value="'.esc_url( get_option('youtube_uri') ).'">';

			echo '</p>';



			echo '<p>';

			echo '<label for="twitter_uri">Odkaz na Twitter profil</label>';

			echo '<input type="text" id="twitter_uri" name="twitter_uri" value="'.esc_url( get_option('twitter_uri') ).'">';

			echo '</p>';



			echo '<p>';

			echo '<label for="instagram_uri">Odkaz na Instagram profil</label>';

			echo '<input type="text" id="instagram_uri" name="instagram_uri" value="'.esc_url( get_option('instagram_uri') ).'">';

			echo '</p>';



		echo '</div>';



		echo '<p class="submit">';

		echo '<input type="submit" value="Uložit změny" class="button button-primary">';

		echo '</p>';



		echo '</form>';

		echo '</div>';

		echo '</div>';



	}



	if( !empty($_GET['page']) && $_GET['page'] == 'theme-options' && !empty($_POST) && current_user_can( 'manage_options' ) ) {



		if( !empty( $_POST['website_logo'] ) ) {

			update_option( 'website_logo', $_POST['website_logo'] );

		}



		if( !empty( $_POST['bank_account'] ) ) {

			update_option( 'bank_account', $_POST['bank_account'] );

		}





		if( !empty( $_POST['bank_iban'] ) ) {

			update_option( 'bank_iban', $_POST['bank_iban'] );

		}





		if( !empty( $_POST['bank_swift'] ) ) {

			update_option( 'bank_swift', $_POST['bank_swift'] );

		}



		if( !empty( $_POST['facebook_uri'] ) ) {

			update_option( 'facebook_uri', $_POST['facebook_uri'] );

		}



		if( !empty( $_POST['youtube_uri'] ) ) {

			update_option( 'youtube_uri', $_POST['youtube_uri'] );

		}



		if( !empty( $_POST['twitter_uri'] ) ) {

			update_option( 'twitter_uri', $_POST['twitter_uri'] );

		}



		if( !empty( $_POST['instagram_uri'] ) ) {

			update_option( 'instagram_uri', $_POST['instagram_uri'] );

		}



		wp_redirect( admin_url( 'themes.php?page=theme-options&updated=true' ) );



	}



	add_action( 'admin_footer', function() {



		$website_logo = get_option( 'website_logo', 0 ); ?>

		<script type='text/javascript'>

			jQuery( document ).ready( function( $ ) {

				

				jQuery('.logopicker').on('click', function( event ){



					event.preventDefault();



					var file_frame;

					var wp_media_post_id = wp.media.model.settings.post.id;

					var set_to_post_id = <?php echo $website_logo; ?>;

					

					if ( file_frame ) {

						file_frame.uploader.uploader.param( 'post_id', set_to_post_id );

						file_frame.open();

						return;

					} else {

						wp.media.model.settings.post.id = set_to_post_id;

					}



					file_frame = wp.media.frames.file_frame = wp.media({

						title: 'Vyberte obrázek',

						button: {

							text: 'Použít obrázek',

						},

						multiple: false

					});



					file_frame.on( 'select', function() {

						attachment = file_frame.state().get('selection').first().toJSON();

						$( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );

						$( '#website_logo' ).val( attachment.id );

						wp.media.model.settings.post.id = wp_media_post_id;

					});



					file_frame.open();



				});



				jQuery( 'a.add_media' ).on( 'click', function() {

					wp.media.model.settings.post.id = wp_media_post_id;

				});



			});

		</script><?php



	} );



	function custom_toolbar_link($wp_admin_bar) {



	    $args = array(

	        'id' => 'importexport',

	        'title' => '<span class="ab-icon dashicons-update" style="padding-top: 6px;"></span> Export / Import', 

	        'href' => admin_url('themes.php?page=theme-options'), 

	        'meta' => array(

	            'class' => 'ordertable', 

	            'title' => 'Zobrazit nabídku exportu a importu dat'

	            )

	    );

	    $wp_admin_bar->add_node($args);



	}

	add_action('admin_bar_menu', 'custom_toolbar_link', 999);



	function fajntabory_is_valid_csv_upload( $field ) {
		if ( empty( $_FILES[ $field ] ) || ! isset( $_FILES[ $field ]['tmp_name'], $_FILES[ $field ]['error'] ) ) {
			return false;
		}
		$file = $_FILES[ $field ];
		if ( UPLOAD_ERR_OK !== (int) $file['error'] || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return false;
		}
		$filetype = wp_check_filetype( $file['name'] );
		return 'csv' === $filetype['ext'];
	}

	if( !empty( $_POST['tcsv'] ) ) {

		add_action( 'admin_init', function() {

			if( ! is_user_logged_in() || ! current_user_can( 'manage_options' )) {

				wp_redirect( wp_login_url() );

				exit;

			} else {

				if( ! isset( $_POST['fajntabory_tcsv_nonce'] )
					|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fajntabory_tcsv_nonce'] ) ), 'fajntabory_import_tcsv' ) ) {
					wp_die( 'Neplatný bezpečnostní token. Zkuste import provést znovu.' );
				}

				if( ! fajntabory_is_valid_csv_upload( 'importcsv' ) ) {
					wp_redirect( admin_url('themes.php?page=theme-options&uploaded=false') );
					exit;
				}

				$target = get_template_directory() . '/CSV/doprava.csv';

				if( move_uploaded_file($_FILES["importcsv"]["tmp_name"], $target) ) {

					$row = 1;

					$csv = array_map( 'str_getcsv', file( $target ) );

					$index = 0;

					foreach ($csv as $key => $value) {

						if( $index < 2 ) { 

							$index++; continue; 

						} else {

							$post_id = (int)trim($value[0], '#'); // A - ID

							// wp_die($post_id);

							update_post_meta( $post_id, '_sale_price', $value[8] ); // I - Cena po slevě

							update_post_meta( $post_id, '_regular_price', $value[9] ); // J - Aktuální cena

							update_post_meta( $post_id, '_stock', intval($value[12]) ); // M - Stav skladem

							update_post_meta( $post_id, 'variable_first_sale_price', $value[10] ); // K - Originální cena

							update_post_meta( $post_id, 'variable_raising_sale_price', $value[11] ); // L - Hodnota pro týdenní navyšování

							$index++;

						}

					}

					wp_redirect( admin_url('themes.php?page=theme-options&uploaded=true') );

					exit;

				} else {

					wp_redirect( admin_url('themes.php?page=theme-options&uploaded=false') );

					exit;

				}

			}

		});

	}



	if( !empty( $_POST['csv'] ) ) {

		add_action( 'init', function() {

			if( ! is_user_logged_in() || ! current_user_can( 'manage_options' )) {

				wp_redirect( wp_login_url() );

				exit;

			} else {

				if( ! isset( $_POST['fajntabory_csv_nonce'] )
					|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fajntabory_csv_nonce'] ) ), 'fajntabory_import_csv' ) ) {
					wp_die( 'Neplatný bezpečnostní token. Zkuste import provést znovu.' );
				}

				if( ! fajntabory_is_valid_csv_upload( 'importcsv' ) ) {
					wp_redirect( admin_url('themes.php?page=theme-options&uploaded=false') );
					exit;
				}

				$target = get_template_directory() . '/CSV/tabory.csv';

				if( move_uploaded_file($_FILES["importcsv"]["tmp_name"], $target) ) {

					$row = 1;

					$csv = array_map( 'str_getcsv', file( $target ) );

					$index = 0;



					foreach ($csv as $key => $value) {

						if( $index < 3 ) { 

							$index++; continue; 

						} else {

							// var_dump( $value );

							$post_id = (int)trim($value[1], '#');

							update_post_meta( $post_id, '_sale_price', $value[9] );

							update_post_meta( $post_id, '_regular_price', $value[10] );

							update_post_meta( $post_id, '_stock', intval($value[13]) );

							update_post_meta( $post_id, 'variable_first_sale_price', $value[11] );

							update_post_meta( $post_id, 'variable_raising_sale_price', $value[12] );

							$salePriceDatesTo = strtotime('next wednesday 23:59:59');
							update_post_meta( $post_id, '_sale_price_dates_to', $salePriceDatesTo );

						}

					}

					wp_redirect( admin_url('themes.php?page=theme-options&uploaded=true') );

					exit;

				} else {

					wp_redirect( admin_url('themes.php?page=theme-options&uploaded=false') );

					exit;

				}

			}

		});

	}



	if( !empty($_GET['orders'] ) ) {



		add_action( 'init', function() {



			if( ! is_user_logged_in() || ! current_user_can( 'manage_options' )) {

				wp_redirect( wp_login_url( admin_url('themes.php?page=theme-options') ) );

				exit;

			}



			global $woocommerce;



			$args = array(

				'posts_per_page' => -1

			);

			

			$orders = wc_get_orders($args);



			echo '<html>';

			echo '<head>';

			echo '<style>';

			echo 'body { font-family: sans-serif; } table { border-collapse: separate; } table tr td { padding: 5px 10px; white-space: nowrap; border-bottom: 1px solid #ddd; width: auto; }';

			echo '</style>';

			echo '<meta charset="UTF-8">';

			echo '</head>';

			echo '<body>';

			echo '<table>';

			

			foreach ( $orders as $order ) {



				$id = $order->id;



				// Získání meta informací



				$metas = get_post_meta( $id );

				// get_post_meta( $_GET['otest'], 'coupon_code', true )



				// Získání propagace



				$propagace = null;

				if( !empty($metas['v_minulosti_byl']) ) { $propagace = 'Už jsem v minulosti na Fajn Táborech byl / byla'; }

				if( !empty($metas['od_kamarada']) ) { $propagace = 'Od kamaráda'; }

				if( !empty($metas['letaky']) ) { $propagace = 'Letáky'; }

				if( !empty($metas['noviny']) ) { $propagace = 'Noviny, časopisy'; }

				if( !empty($metas['billboard']) ) { $propagace = 'Billboard'; }

				if( !empty($metas['na_aute']) ) { $propagace = 'Upoutávka na autě'; }

				if( !empty($metas['facebook']) ) { $propagace = 'Facebook'; }

				if( !empty($metas['instagram']) ) { $propagace = 'Instagram'; }

				if( !empty($metas['ve_skole']) ) { $propagace = 'Ve škole z prezentace'; }

				if( !empty($metas['youtube']) ) { $propagace = 'Youtube'; }

				if( !empty($metas['twitter']) ) { $propagace = 'Twitter'; }

				if( !empty($metas['jinde']) ) { $propagace = 'Jinde na internetu'; }



				// Získání data splatnosti



				$splatnost =  null;

				$splatnost = $order->date_created;

				$splatnost = $splatnost->getTimestamp();

				$splatnost = strtotime("+7 day", $splatnost);

				$splatnost = date( 'j.n.Y', $splatnost );

				

				// Získání informací o táboře



				$items = $order->get_items();



				$tabor = array();



				foreach ( $items as $item ) {

					

					$item_id = $item->get_product_id();

					$terms = get_the_terms( $item_id, 'product_cat' );

					$category = $terms[0]->term_id;

					

					if( $category == 20 ) {

						$tabor['id'] = $item_id;

						$tabor['name'] = $item->get_name();

						$tabor['variation'] = 0;

						if( $item->get_variation_id() != 0 ) {

							$tabor['variation'] = $item->get_variation_id();

						}

						$product = get_product( $tabor['variation'] );

						$product_attributes = $product->attributes;



						if( is_array( $product_attributes ) ) {



							$product_attribute_term = get_term_by('slug', $product_attributes['pa_typ-tabora'], 'pa_typ-tabora'); 

							$product_attribute_name = $product_attribute_term->name;

							$tabor['typ'] = $product_attribute_name;



							$product_attribute_term = get_term_by('slug', $product_attributes['pa_lokalita'], 'pa_lokalita'); 

							$product_attribute_name = $product_attribute_term->name;

							$tabor['lokalita'] = $product_attribute_name;



							$product_attribute_term = get_term_by('slug', $product_attributes['pa_terminy'], 'pa_terminy'); 

							$product_attribute_name = $product_attribute_term->name;

							$tabor['termin'] = $product_attribute_name;



						} else {



							$tabor['typ'] = null;

							$tabor['lokalita'] = null;

							$tabor['termin'] = null;



						}

					}



				}

		 

		 		echo '<tr>';

				echo '<td>'.esc_html( $metas['jmeno'][0] ).'</td>';

				echo '<td>'.esc_html( $metas['prijmeni'][0] ).'</td>';

				echo '<td>'.esc_html( $metas['datum_narozeni'][0] ).'</td>';

				echo '<td>'.esc_html( $metas['ulice'][0] ).', '.esc_html( $metas['mesto'][0] ).' '.esc_html( $metas['psc'][0] ).'</td>';

				echo '<td>'.esc_html( $metas['narodnost'][0] ).'</td>';

				echo '<td>'.esc_html( $metas['skola'][0] ).'</td>';

				echo '<td>'.esc_html( $metas['triko'][0] ).'</td>';



				echo '<td>'.esc_html( $tabor['typ'] ).'</td>';

				echo '<td>'.esc_html( $tabor['lokalita'] ).'</td>';

				echo '<td>'.esc_html( $tabor['name'] ).'</td>';

				echo '<td>'.esc_html( $tabor['termin'] ).'</td>';



				echo '<td>'.$order->total.'</td>';

				echo '<td></td>';

				echo '<td>'.$splatnost.'</td>';

				echo '<td>'.$id.'</td>';

				$zamestnavatel = null;
				
				if( !empty($metas['fakturace_odberatel_nazev'][0]) ) {
					$zamestnavatel .= 'Zaměstnavatel:' . $metas['fakturace_odberatel_nazev'][0];
				}

				if( !empty($metas['fakturace_odberatel_ulice'][0]) ) {
					$zamestnavatel .= ', ulice č.p.: ' . $metas['fakturace_odberatel_ulice'][0];
				}

				if( !empty($metas['fakturace_odberatel_mesto'][0]) ) {
					$zamestnavatel .= ', PSČ, mesto: ' . $metas['fakturace_odberatel_mesto'][0];
				}

				if( !empty($metas['fakturace_odberatel_ico'][0]) ) {
					$zamestnavatel .= ', IČ: ' . $metas['fakturace_odberatel_ico'][0];
				}

				if( !empty($metas['fakturace_odberatel_dic'][0]) ) {
					$zamestnavatel .= ', DIČ: ' . $metas['fakturace_odberatel_dic'][0];
				}

				if( !empty($metas['zamestnavatel'][0]) ) {
					$zamestnavatel .= ', Poznámky k proplacení: ' . $metas['zamestnavatel'][0];
				}

				echo '<td>'.esc_html( $zamestnavatel ).'</td>';

				echo '<td>'.esc_html( get_post_meta( $id, 'coupon_code', true ) ).'</td>';



				echo '<td>'.esc_html( $metas['zpusobilost'][0] ).'</td>';

				echo '<td>'.esc_html( $metas['Z_prijmeni'][0] ).' '.esc_html( $metas['Z_jmeno'][0] ).'</td>';

				echo '<td>'.esc_html( $metas['telefon'][0] ).'</td>';

				echo '<td>'.esc_html( $metas['email'][0] ).'</td>';

				echo '<td>'.esc_html( $propagace ).'</td>';

				echo '<td></td>';

				echo '<td></td>';

				echo '<td></td>';

				echo '<td></td>';

				echo '<td></td>';

				echo '<td></td>';

				echo '<td></td>';

				echo '<td></td>';

				echo '<td>'.$tabor['variation'].'</td>';

				echo '</tr>';

			}



			echo '</table>';

			echo '</body>';



 			exit;



		} );



	}







	if( !empty($_GET['export-transport'] ) ) {



		add_action( 'init', function() {



			if( ! is_user_logged_in() || ! current_user_can( 'manage_options' )) {

				wp_redirect( wp_login_url( admin_url('themes.php?page=theme-options') ) );

				exit;

			}



			global $woocommerce;



			echo '<html>';

			echo '<head>';

			echo '<style>';

			echo 'body { font-family: sans-serif; } table { border-collapse: separate; } table tr td { padding: 5px 10px; white-space: nowrap; border-bottom: 1px solid #ddd; width: auto; }';

			echo '</style>';

			echo '<meta charset="UTF-8">';

			echo '</head>';

			echo '<body>';

			echo '<table>';



			$args = array(

				'post_type'			=> 'product',

				'post_status'		=> 'any',

				'posts_per_page' 	=> -1, 

				'tax_query' => array(

					array(

						'taxonomy' => 'product_cat',

						'field'    => 'slug',

						'terms'    => 'doprava',

					),

				)

			);



			$query = new WP_Query( $args );

			if( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					

					$query->the_post();

					

					global $product;

					if ( $product->is_type( 'variable' ) ) {

						$available_variations = $product->get_available_variations();

						foreach( $available_variations as $variation ) {



							$metas = get_post_meta( $variation['variation_id'] );

							

							$taxonomy = 'pa_doprava';

							$doprava = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );

							$doprava = get_term_by('slug', $doprava, $taxonomy);

							$doprava = $doprava->name;

							

							$taxonomy = 'pa_smer';

							$smer = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );

							$smer = get_term_by('slug', $smer, $taxonomy);

							$smer = $smer->name;

							

							$taxonomy = 'pa_terminy';

							$terminy = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );

							$terminy = get_term_by('slug', $terminy, $taxonomy);

							$terminy = $terminy->name;



							$taxonomy = 'pa_lokalita';

							$lokalita = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );

							$lokalita = get_term_by('slug', $lokalita, $taxonomy);

							$lokalita = $lokalita->name;



							$status = get_post_status( $product->ID );

							if( $status == 'publish' ) {

								$status = 'aktivní';

							} else {

								$status = get_post_status( $product->ID );

							}



							echo '<tr>';

								echo '<td>#'.$variation['variation_id'].'</td>'; // A

								echo '<td>'.esc_html( get_the_title( $variation['variation_id'] ) ).'</td>'; // B

								echo '<td>'.$doprava.'</td>'; // C

								echo '<td>'.$smer.'</td>'; // D

								echo '<td>'.$terminy.'</td>'; // E

								echo '<td>'.$lokalita.'</td>'; // F

								$qty = (int)$metas['_stock'][0] - (int)count( retrieve_orders_ids_from_a_product_id($variation['variation_id']) ); // 

								echo '<td>'.$qty.'</td>'; // G

								echo '<td></td>';

								echo '<td>'.(int)$metas['_sale_price'][0].'</td>'; // I

								echo '<td>'.(int)$metas['_regular_price'][0].'</td>'; // J

								echo '<td>'.(int)$metas['variable_first_sale_price'][0].'</td>'; // K

								echo '<td>'.(int)$metas['variable_raising_sale_price'][0].'</td>'; // L

								echo '<td>'.intval($metas['_stock'][0]).'</td>'; // M

							echo '</tr>';



					}

				}

			}

		}



			echo '</table>';

			echo '</body>';



 			exit;



		} );

	}





	if( !empty($_GET['export'] ) ) {



		add_action( 'init', function() {



			if( ! is_user_logged_in() || ! current_user_can( 'manage_options' )) {

				wp_redirect( wp_login_url( admin_url('themes.php?page=theme-options') ) );

				exit;

			}



			global $woocommerce;



			echo '<html>';

			echo '<head>';

			echo '<style>';

			echo 'body { font-family: sans-serif; } table { border-collapse: separate; } table tr td { padding: 5px 10px; white-space: nowrap; border-bottom: 1px solid #ddd; width: auto; }';

			echo '</style>';

			echo '<meta charset="UTF-8">';

			echo '</head>';

			echo '<body>';

			echo '<table>';



			$args = array(

				'post_type'			=> 'product',

				'post_status'		=> 'any',

				'posts_per_page' 	=> -1, 

				'tax_query' => array(

					array(

						'taxonomy' => 'product_cat',

						'field'    => 'slug',

						'terms'    => 'tabory',

					),

				)

			);



			$query = new WP_Query( $args );

			if( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					

					$query->the_post();

					

					global $product;

					if ( $product->is_type( 'variable' ) ) {

						$available_variations = $product->get_available_variations();

						foreach( $available_variations as $variation ) {



							$metas = get_post_meta( $variation['variation_id'] );

							

							$taxonomy = 'pa_lokalita';

							$lokalita = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );

							$lokalita = get_term_by('slug', $lokalita, $taxonomy);

							$lokalita = $lokalita->name;

							

							$taxonomy = 'pa_typ-tabora';

							$typ_tabora = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );

							$typ_tabora = get_term_by('slug', $typ_tabora, $taxonomy);

							$typ_tabora_slug = $typ_tabora->slug;

							$typ_tabora = $typ_tabora->name;

							

							$taxonomy = 'pa_terminy';

							$terminy = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );

							$terminy = get_term_by('slug', $terminy, $taxonomy);

							$terminy = $terminy->name;



							$status = get_post_status( $product->ID );

							if( $status == 'publish' ) {

								$status = 'aktivní';

							} else {

								$status = get_post_status( $product->ID );

							}



							echo '<tr>';

								echo '<td>#'.$variation['variation_id'].'</td>';

								echo '<td>'.esc_html( get_the_title( $variation['variation_id'] ) ).'</td>';

								echo '<td>'.esc_html( $lokalita ).'</td>';

								echo '<td>'.$typ_tabora.'</td>';

								echo '<td>'.$terminy.'</td>';

								echo '<td>'.$status.'</td>';

								$qty = (int)$metas['_stock'][0] - (int)count( retrieve_orders_ids_from_a_product_id($variation['variation_id']) );

								echo '<td>'.$qty.'</td>';

								echo '<td></td>';

								echo '<td>'.$metas['_sale_price'][0].'</td>';// _sale_price

								echo '<td>'.$metas['_regular_price'][0].'</td>';

								echo '<td>'.$metas['variable_first_sale_price'][0].'</td>';

								echo '<td>'.$metas['variable_raising_sale_price'][0].'</td>';

								echo '<td>'.intval($metas['_stock'][0]).'</td>';

							echo '</tr>';



					}

				}

			}

		}



			echo '</table>';

			echo '</body>';



 			exit;



		} );

	}



	/**

	 * Woocommerce custom taxonomies

	 */

	

	add_action( 'init', function() {



		$labels = array(

		    'name'                       => 'Typ tábora',

		    'singular_name'              => 'Typ tábora',

		    'menu_name'                  => 'Typ tábora',

		    'all_items'                  => 'Všechny typy',

		    'parent_item'                => 'Nadřazený typ tábora',

		    'parent_item_colon'          => 'Nadřazený typ tábora:',

		    'new_item_name'              => 'Název nového typu tábora',

		    'add_new_item'               => 'Přidat typ tábora',

		    'edit_item'                  => 'Upravit typ tábora',

		    'update_item'                => 'Aktualizovat typ tábora',

		    'separate_items_with_commas' => 'Oddělte typy tábora čárkou',

		    'search_items'               => 'Hledat typ tábora',

		    'add_or_remove_items'        => 'Přidat nebo odebrat typ tábora',

		    'choose_from_most_used'      => 'Vyberte z nejčastěji používaných typů tábora',

		);



		$args = array(

		    'labels'                     => $labels,

		    'hierarchical'               => true,

		    'public'                     => true,

		    'show_ui'                    => true,

		    'show_admin_column'          => true,

		    'show_in_nav_menus'          => true,

		    'show_tagcloud'              => true,

		);



		register_taxonomy( 'typ-tabora', 'product', $args );

		register_taxonomy_for_object_type( 'typ-tabora', 'product' );



		$labels = array(

		    'name'                       => 'Pozice vedoucího',

		    'singular_name'              => 'Pozice vedoucího',

		    'menu_name'                  => 'Pozice vedoucího',

		    'all_items'                  => 'Všechny pozice',

		    'parent_item'                => 'Nadřazená pozice vedoucího',

		    'parent_item_colon'          => 'Nadřazená pozice vedoucího:',

		    'new_item_name'              => 'Název nové pozice vedoucího',

		    'add_new_item'               => 'Přidat pozici vedoucího',

		    'edit_item'                  => 'Upravit pozici vedoucího',

		    'update_item'                => 'Aktualizovat pozici vedoucího',

		    'separate_items_with_commas' => 'Oddělte pozice vedoucího čárkou',

		    'search_items'               => 'Hledat pozici vedoucího',

		    'add_or_remove_items'        => 'Přidat nebo odebrat pozici vedoucího',

		    'choose_from_most_used'      => 'Vyberte z nejčastěji používaných pozicí vedoucího',

		);



		$args = array(

		    'labels'                     => $labels,

		    'hierarchical'               => true,

		    'public'                     => true,

		    'show_ui'                    => true,

		    'show_admin_column'          => true,

		    'show_in_nav_menus'          => true,

		    'show_tagcloud'              => true,

		);



		register_taxonomy( 'pozice-vedoucich', 'vedouci', $args );

		register_taxonomy_for_object_type( 'pozice-vedoucich', 'vedouci' );



		// Galerie - taxonomie



		$labels = array(

		    'name'                       => 'Lokalita',

		    'singular_name'              => 'Lokalita',

		    'menu_name'                  => 'Lokalita',

		    'all_items'                  => 'Všechny lokality',

		    'parent_item'                => 'Nadřazená lokalita',

		    'parent_item_colon'          => 'Nadřazená lokalita:',

		    'new_item_name'              => 'Název nové lokality',

		    'add_new_item'               => 'Přidat lokalitu',

		    'edit_item'                  => 'Upravit lokalitu',

		    'update_item'                => 'Aktualizovat lokalitu',

		    'separate_items_with_commas' => 'Oddělte lokality čárkou',

		    'search_items'               => 'Hledat lokality',

		    'add_or_remove_items'        => 'Přidat nebo odebrat lokalitu',

		    'choose_from_most_used'      => 'Vyberte z nejčastěji používaných lokalit',

		);



		$args = array(

		    'labels'                     => $labels,

		    'hierarchical'               => true,

		    'public'                     => true,

		    'show_ui'                    => true,

		    'show_admin_column'          => true,

		    'show_in_nav_menus'          => true,

		    'show_tagcloud'              => true,

		);



		register_taxonomy( 'galerie-lokalita', 'galerie', $args );

		register_taxonomy_for_object_type( 'galerie-lokalita', 'galerie' );



		$labels = array(

		    'name'                       => 'Termín',

		    'singular_name'              => 'Termín',

		    'menu_name'                  => 'Termín',

		    'all_items'                  => 'Všechny termíny',

		    'parent_item'                => 'Nadřazený termín',

		    'parent_item_colon'          => 'Nadřazený termín:',

		    'new_item_name'              => 'Název nového termíni',

		    'add_new_item'               => 'Přidat termín',

		    'edit_item'                  => 'Upravit termín',

		    'update_item'                => 'Aktualizovat termín',

		    'separate_items_with_commas' => 'Oddělte termíny čárkou',

		    'search_items'               => 'Hledat termíny',

		    'add_or_remove_items'        => 'Přidat nebo odebrat termín',

		    'choose_from_most_used'      => 'Vyberte z nejčastěji používaných termínů',

		);



		$args = array(

		    'labels'                     => $labels,

		    'hierarchical'               => true,

		    'public'                     => true,

		    'show_ui'                    => true,

		    'show_admin_column'          => true,

		    'show_in_nav_menus'          => true,

		    'show_tagcloud'              => true,

		);



		register_taxonomy( 'galerie-terminy', 'galerie', $args );

		register_taxonomy_for_object_type( 'galerie-terminy', 'galerie' );



		$labels = array(

		    'name'                       => 'Rok',

		    'singular_name'              => 'Rok',

		    'menu_name'                  => 'Rok',

		    'all_items'                  => 'Všechna roky',

		    'parent_item'                => 'Nadřazené roky',

		    'parent_item_colon'          => 'Nadřazené roky:',

		    'new_item_name'              => 'Název nového roku',

		    'add_new_item'               => 'Přidat rok',

		    'edit_item'                  => 'Upravit rok',

		    'update_item'                => 'Aktualizovat rok',

		    'separate_items_with_commas' => 'Oddělte rok čárkou',

		    'search_items'               => 'Hledat rok',

		    'add_or_remove_items'        => 'Přidat nebo odebrat rok',

		    'choose_from_most_used'      => 'Vyberte z nejčastěji používaných roků',

		);



		$args = array(

		    'labels'                     => $labels,

		    'hierarchical'               => true,

		    'public'                     => true,

		    'show_ui'                    => true,

		    'show_admin_column'          => true,

		    'show_in_nav_menus'          => true,

		    'show_tagcloud'              => true,

		);



		register_taxonomy( 'galerie-roky', 'galerie', $args );

		register_taxonomy_for_object_type( 'galerie-roky', 'galerie' );



	} );



	add_action( 'plugins_loaded', function() {



		class WC_Product_Tabor extends WC_Product_Variable {



			public function __construct( $product ) {



				$this->product_type = 'tabor';

				parent::__construct( $product );



			}



		}



	});



	

	/**

	 * Přidání slideru

	 */

	

	function bxslider() {



		$labels = array(

			'name'                  => _x( 'Slider', 'Post Type General Name', 'fajntabory' ),

			'singular_name'         => _x( 'Slider', 'Post Type Singular Name', 'fajntabory' ),

			'menu_name'             => __( 'Slider', 'fajntabory' ),

			'name_admin_bar'        => __( 'Slider', 'fajntabory' ),

			'archives'              => __( 'Slider archiv', 'fajntabory' ),

			'attributes'            => __( 'Atributy slideru', 'fajntabory' ),

			'parent_item_colon'     => __( 'Nadřazený slider', 'fajntabory' ),

			'all_items'             => __( 'Všechny slidery', 'fajntabory' ),

			'add_new_item'          => __( 'Přidat nový slider', 'fajntabory' ),

			'add_new'               => __( 'Přidat nový', 'fajntabory' ),

			'new_item'              => __( 'Nový slider', 'fajntabory' ),

			'edit_item'             => __( 'Upravit slider', 'fajntabory' ),

			'update_item'           => __( 'Aktualizovat slider', 'fajntabory' ),

			'view_item'             => __( 'Zobrazit slider', 'fajntabory' ),

			'view_items'            => __( 'Zobrazit slidery', 'fajntabory' ),

			'search_items'          => __( 'Hledat slider', 'fajntabory' ),

			'not_found'             => __( 'Nenalezeno', 'fajntabory' ),

			'not_found_in_trash'    => __( 'Nenalezeno', 'fajntabory' ),

			'featured_image'        => __( 'Náhledový obrázek', 'fajntabory' ),

			'set_featured_image'    => __( 'Nastavit náhledový obrázek', 'fajntabory' ),

			'remove_featured_image' => __( 'Odstranit náhledový obrázek', 'fajntabory' ),

			'use_featured_image'    => __( 'Použít jako náhledový obrázek', 'fajntabory' ),

			'insert_into_item'      => __( 'Vložit do slideru', 'fajntabory' ),

			'uploaded_to_this_item' => __( 'Nahrát do toho slideru', 'fajntabory' ),

			'items_list'            => __( 'Seznam sliderů', 'fajntabory' ),

			'items_list_navigation' => __( 'Navigace seznamu sliderů', 'fajntabory' ),

			'filter_items_list'     => __( 'Filtr seznamu sliderů', 'fajntabory' ),

		);

		$args = array(

			'label'                 => __( 'Slider', 'fajntabory' ),

			'description'           => __( 'Slider', 'fajntabory' ),

			'labels'                => $labels,

			'supports'              => array( 'title', 'editor', 'thumbnail' ),

			'hierarchical'          => false,

			'public'                => true,

			'show_ui'               => true,

			'show_in_menu'          => true,

			'menu_position'         => 5,

			'menu_icon'             => 'dashicons-format-gallery',

			'show_in_admin_bar'     => false,

			'show_in_nav_menus'     => false,

			'can_export'            => true,

			'has_archive'           => false,		

			'exclude_from_search'   => true,

			'publicly_queryable'    => true,

			'capability_type'       => 'page',

		);

		register_post_type( 'bxslider', $args );



	}

	add_action( 'init', 'bxslider', 0 );



	add_action( 'wp_enqueue_scripts', 'frontend_scripts_include_lightbox' );



	function frontend_scripts_include_lightbox() {

	  

	  	global $woocommerce;

	 	$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	  	$lightbox_en = get_option( 'woocommerce_enable_lightbox' ) == 'yes' ? true : false;



	  	if ( $lightbox_en ) {

	    	wp_enqueue_script( 'prettyPhoto', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), $woocommerce->version, true );

	    	wp_enqueue_script( 'prettyPhoto-init', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto.init' . $suffix . '.js', array( 'jquery' ), $woocommerce->version, true );

	    	wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce->plugin_url() . '/assets/css/prettyPhoto.css' );

		}



	}





	if( !empty( $_GET['add_to_cart'] ) ) {

		add_action( 'template_redirect', function() {

			global $woocommerce;

	

			$id = $_GET['add_to_cart'];



			$product = wc_get_product( $id );

			if( $product->post_type == 'product_variation') {

				$product = wc_get_product( $product->parent_id );

			}

			$tid = $product->id;

			$terms = get_the_terms( $tid, 'product_cat' );

			$category = $terms[0]->term_id;

			$clear = true;



			foreach( $woocommerce->cart->get_cart() as $cart_item ) {

				$item_id = $cart_item['product_id'];

				$key = $cart_item['key'];

				$item_terms = get_the_terms( $item_id, 'product_cat' );

				$item_category = $item_terms[0]->term_id;

				if( $item_category == 20 && $category == 20 ) {

					$woocommerce->cart->remove_cart_item($key);

					// var_dump($cart_item); exit;

				}

			}



			if( $clear == true ) {

				$woocommerce->cart->add_to_cart($id);

				wp_redirect( home_url() . '/kosik/' );

				exit;

			} else {

				wp_redirect( home_url() . '/kosik/' );

				// wp_redirect( get_the_permalink( $id ) );

				exit;

			}



		} );

	}



	add_filter( 'nav_menu_css_class', 'add_custom_class', 10, 2 );



	function add_custom_class( $classes = array(), $menu_item = false ) {

		

		if( is_single(  ) ) {



			if (! in_array( 'current-menu-item', $classes ) ) {



				if( is_singular('product') && $menu_item->title == 'Tábory' ) {

					$classes[] = 'current-menu-item';

				}



				if( is_singular('galerie') && $menu_item->title == 'Galerie' ) {

					$classes[] = 'current-menu-item';

				}



				if( is_singular('vedouci') && $menu_item->title == 'Vedoucí' ) {

					$classes[] = 'current-menu-item';

				}



				return array_unique( $classes );



			}

		} else {

			return array_unique( $classes );

		}



	}

	/*** Nastavení účtů

	if(!empty($_GET['testuctu'])) {

		add_action('init', 'testuctu');

		function testuctu() {
			global $woocommerce;
			$product_id = 15204;
			$variation = new WC_Product_Variation( $product_id );
			$variation_slug = $variation->get_attributes();
			$variation_slug = $variation_slug['pa_terminy'];
			$term = get_term_by('slug', $variation_slug, 'pa_terminy');
			$term_id = $term->term_id;
			$term_meta = get_option( "taxonomy_$term_id" );
			$term_meta = $term_meta['ucet'];
			var_dump($term_meta);
			wp_die();
		};

	}

	 ***/


	if( !empty( $_POST['reservation_step'] ) || !empty( $_POST['objednavka']) ) {

		$reservation_step = ! empty( $_POST['reservation_step'] ) ? sanitize_text_field( wp_unslash( $_POST['reservation_step'] ) ) : '';

		if ( 'reserve' === $reservation_step ) {
			add_action( 'init', 'fajntabory_create_reservation' );
		} elseif ( 'send_link' === $reservation_step ) {
			add_action( 'init', 'fajntabory_send_reservation_link_request' );
		} elseif ( 'complete' === $reservation_step ) {
			add_action( 'init', 'complete_order' );
		} elseif ( ! empty( $_POST['objednavka'] ) ) {
			add_action( 'init', 'complete_order' );
		}

	}



	function fajntabory_create_reservation() {



		global $woocommerce;



		if( WC()->cart->get_cart_contents_count() < 1 ) {

			wp_redirect( wc_get_cart_url() );

			exit;

		}



		$email = ! empty( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$phone = ! empty( $_POST['telefon'] ) ? sanitize_text_field( wp_unslash( $_POST['telefon'] ) ) : '';
		$form_type = ! empty( $_POST['objednavka'] ) ? sanitize_text_field( wp_unslash( $_POST['objednavka'] ) ) : fajntabory_get_checkout_form_type();
		$form_type = strtolower( (string) $form_type );
		$coupon_code = ! empty( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
		$recaptcha_token = ! empty( $_POST['_wpcf7_recaptcha_response'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpcf7_recaptcha_response'] ) ) : '';
		$honeypot = ! empty( $_POST['reservation_company'] ) ? sanitize_text_field( wp_unslash( $_POST['reservation_company'] ) ) : '';



		if ( empty( $form_type ) ) {

			wc_add_notice( 'Nepodařilo se určit typ přihlášky. Vraťte se prosím do košíku a zkuste to znovu.', 'error' );
			wp_safe_redirect( wc_get_cart_url() );
			exit;

		}

		if ( ! in_array( $form_type, array( 'a', 'b', 'c', 'd' ), true ) ) {

			wc_add_notice( 'Nepodařilo se ověřit typ přihlášky. Vraťte se prosím do košíku a zkuste to znovu.', 'error' );
			wp_safe_redirect( wc_get_cart_url() );
			exit;

		}

		if (
			! empty( $honeypot )
			|| empty( $_POST['reservation_create_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['reservation_create_nonce'] ) ), 'fajntabory_create_reservation' )
		) {

			wc_add_notice( 'Objednávkový formulář se nepodařilo ověřit. Zkuste to prosím znovu.', 'error' );
			wp_safe_redirect( fajntabory_get_checkout_url() );
			exit;

		}



		if ( empty( $email ) || ! is_email( $email ) ) {

			wc_add_notice( 'Zadejte prosím platný e-mail.', 'error' );
			wp_safe_redirect( fajntabory_get_checkout_url() );
			exit;

		}



		if ( empty( $phone ) ) {

			wc_add_notice( 'Zadejte prosím telefon.', 'error' );
			wp_safe_redirect( fajntabory_get_checkout_url() );
			exit;

		}



		if ( empty( $_POST['toc'] ) ) {

			wc_add_notice( 'Pro pokračování je nutné souhlasit se všeobecnými podmínkami.', 'error' );
			wp_safe_redirect( fajntabory_get_checkout_url() );
			exit;

		}



		if ( ! fajntabory_verify_recaptcha_token( $recaptcha_token ) ) {

			wc_add_notice( 'Nepodařilo se ověřit ochranu proti spamu. Zkuste to prosím znovu.', 'error' );
			wp_safe_redirect( fajntabory_get_checkout_url() );
			exit;

		}



		if ( ! fajntabory_reservation_rate_limit_passed( $email ) ) {

			wc_add_notice( 'Formulář byl z této adresy odeslán příliš často. Zkuste to prosím později.', 'error' );
			wp_safe_redirect( fajntabory_get_checkout_url() );
			exit;

		}



		$address = array(

		    'email'      => $email,

		    'phone'      => $phone,

		    'country'    => 'CZ'

		);



		$order_data = array(

	        'status' => FAJNTABORY_INCOMPLETE_ORDER_STATUS,

	        'customer_id' => get_current_user_id()

	    );



	    $new_order = wc_create_order( $order_data );

        $new_order->set_address($address, 'billing');



        if( !empty( $coupon_code ) ) {

        	update_post_meta( fajntabory_get_order_id( $new_order ), 'coupon_code', $coupon_code );

    	}



		foreach ( $woocommerce->cart->get_cart() as $values ) {

		    $new_order->add_product(

               	$values['data'],

               	$values['quantity'],

               	array(

                	'totals' => array(

                    	'subtotal' => $values['line_subtotal'],

                    	'subtotal_tax' => $values['line_subtotal_tax'],

                    	'total' => $values['line_total'],

                    	'tax' => $values['line_tax'],

                    	'tax_data' => $values['line_tax_data']

                	)

                )

            );

        }



        $new_order->calculate_totals();

        $order_id = fajntabory_get_order_id( $new_order );



        update_post_meta( $order_id, 'email', $email );
        update_post_meta( $order_id, 'telefon', $phone );
        update_post_meta( $order_id, 'objednavka', $form_type );
        update_post_meta( $order_id, '_reservation_status', 'pending_completion' );
        update_post_meta( $order_id, '_reservation_created_at', current_time( 'mysql' ) );
        update_post_meta( $order_id, '_fajntabory_incomplete_order', 'yes' );
        update_post_meta( $order_id, '_reservation_security_verified_at', current_time( 'mysql' ) );
        update_post_meta( $order_id, '_reservation_client_ip_hash', hash_hmac( 'sha256', fajntabory_get_client_ip(), wp_salt( 'auth' ) ) );
        $new_order->add_order_note( 'Objednávka byla vytvořena jako nedokončená rezervace. Na stav Čeká na platbu se přepne až po doplnění přihlášky.' );



        try {

        	$reservation_token = bin2hex( random_bytes( 16 ) );

        } catch ( Exception $e ) {

        	$reservation_token = wp_generate_password( 32, false, false );

        }



        update_post_meta( $order_id, '_reservation_token', $reservation_token );



		WC()->cart->empty_cart( true );



		wp_safe_redirect( fajntabory_get_reservation_choice_url( $reservation_token ) );
		exit;

	}



	function fajntabory_send_reservation_link_request() {

		$reservation_token = ! empty( $_POST['reservation_token'] ) ? sanitize_text_field( wp_unslash( $_POST['reservation_token'] ) ) : '';

		if (
			empty( $reservation_token )
			|| empty( $_POST['reservation_action_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['reservation_action_nonce'] ) ), 'fajntabory_send_reservation_link' )
		) {
			wc_add_notice( 'Odkaz pro dokončení přihlášky se nepodařilo ověřit. Zkuste to prosím znovu.', 'error' );
			wp_safe_redirect( fajntabory_get_checkout_url() );
			exit;
		}

		$reservation_order = fajntabory_get_reservation_order( $reservation_token );

		if ( ! $reservation_order ) {
			wc_add_notice( 'Rezervaci se nepodařilo najít. Zkuste prosím vytvořit objednávku znovu.', 'error' );
			wp_safe_redirect( fajntabory_get_checkout_url() );
			exit;
		}

		if ( fajntabory_reservation_is_completed( $reservation_order ) ) {
			wc_add_notice( 'Tato přihláška už byla dokončena.', 'notice' );
			wp_safe_redirect( fajntabory_get_reservation_complete_url( $reservation_token ) );
			exit;
		}

		if ( ! fajntabory_reservation_email_allowed( $reservation_order ) ) {
			wc_add_notice( 'Odkaz se nepodařilo odeslat, protože rezervace neprošla kontrolou proti spamu. Zkuste prosím vytvořit objednávku znovu.', 'error' );
			wp_safe_redirect( fajntabory_get_checkout_url() );
			exit;
		}

		$order_id = fajntabory_get_order_id( $reservation_order );
		$last_sent_at = fajntabory_reservation_time_to_timestamp( $reservation_order->get_meta( '_reservation_email_last_sent_at' ) );

		if (
			(int) $reservation_order->get_meta( '_reservation_link_email_send_count' ) >= FAJNTABORY_RESERVATION_LINK_MAX_SENDS
			|| ( $last_sent_at > 0 && time() < $last_sent_at + FAJNTABORY_RESERVATION_LINK_RESEND_COOLDOWN )
			|| ! fajntabory_check_rate_limit( 'reservation_link_ip_hour', fajntabory_get_client_ip(), (int) apply_filters( 'fajntabory_reservation_link_ip_hour_limit', 10 ), HOUR_IN_SECONDS )
			|| ! fajntabory_check_rate_limit( 'reservation_link_order_hour', $order_id, (int) apply_filters( 'fajntabory_reservation_link_order_hour_limit', 3 ), HOUR_IN_SECONDS )
		) {
			wc_add_notice( 'Odkaz byl odeslán nedávno. Zkontrolujte prosím e-mail, případně to zkuste později.', 'error' );
			wp_safe_redirect( fajntabory_get_reservation_choice_url( $reservation_token ) );
			exit;
		}

		if ( ! fajntabory_send_reservation_email( $reservation_order, $reservation_token, 'link' ) ) {
			wc_add_notice( 'Odkaz se nepodařilo odeslat. Zkuste to prosím znovu.', 'error' );
			wp_safe_redirect( fajntabory_get_reservation_choice_url( $reservation_token ) );
			exit;
		}

		fajntabory_mark_reservation_link_sent( $reservation_order, 'customer_choice' );

		wp_safe_redirect( add_query_arg(
			array(
				'reservation' => 'sent',
				'oid'         => $order_id,
			),
			fajntabory_get_checkout_url()
		) );
		exit;

	}



	function complete_order() {



		global $woocommerce;

		$_POST = fajntabory_sanitize_order_post( $_POST );

		$reservation_token = ! empty( $_POST['reservation_token'] ) ? $_POST['reservation_token'] : '';
		$reservation_order = ! empty( $reservation_token ) ? fajntabory_get_reservation_order( $reservation_token ) : false;
		$bank_account_number = get_option('bank_account');



		if ( empty( $reservation_token ) || ! $reservation_order ) {

			fajntabory_abort_checkout( 'Přihlášku se nepodařilo ověřit. Začněte prosím znovu od košíku.', wc_get_cart_url() );
			exit;

		}



		if (
			empty( $_POST['reservation_complete_nonce'] )
			|| ! wp_verify_nonce( $_POST['reservation_complete_nonce'], 'fajntabory_complete_reservation' )
		) {

			fajntabory_abort_checkout( 'Objednávkový formulář se nepodařilo ověřit. Zkuste to prosím znovu.', fajntabory_get_reservation_complete_url( $reservation_token ) );
			exit;

		}



		if ( $reservation_order && fajntabory_reservation_is_completed( $reservation_order ) ) {

			wc_add_notice( 'Tato přihláška už byla dokončena.', 'notice' );
			wp_safe_redirect( fajntabory_get_reservation_complete_url( $reservation_token ) );
			exit;

		}



		if ( empty( $_POST['objednavka'] ) || ! in_array( $_POST['objednavka'], array( 'a', 'b', 'c', 'd' ), true ) ) {

			fajntabory_abort_checkout( 'Nepodařilo se ověřit typ přihlášky. Zkuste to prosím znovu.', fajntabory_get_reservation_complete_url( $reservation_token ) );
			exit;

		}



		if ( empty( $_POST['email'] ) || ! is_email( $_POST['email'] ) ) {

			fajntabory_abort_checkout( 'Zadejte prosím platný e-mail.', fajntabory_get_reservation_complete_url( $reservation_token ) );
			exit;

		}



		if ( ! empty( $_POST['email-check'] ) && $_POST['email-check'] !== $_POST['email'] ) {

			fajntabory_abort_checkout( 'Kontrolní e-mail se neshoduje.', fajntabory_get_reservation_complete_url( $reservation_token ) );
			exit;

		}



		if ( empty( $_POST['toc'] ) ) {

			fajntabory_abort_checkout( 'Pro pokračování je nutné souhlasit se všeobecnými podmínkami.', fajntabory_get_reservation_complete_url( $reservation_token ) );
			exit;

		}

	

		$address = array(

		    'first_name' => ! empty( $_POST['jmeno'] ) ? wp_unslash( $_POST['jmeno'] ) : '',

		    'last_name'  => ! empty( $_POST['prijmeni'] ) ? wp_unslash( $_POST['prijmeni'] ) : '',

		    'email'      => ! empty( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',

		    'phone'      => ! empty( $_POST['telefon'] ) ? sanitize_text_field( wp_unslash( $_POST['telefon'] ) ) : '',

		    'address_1'  => ! empty( $_POST['ulice'] ) ? wp_unslash( $_POST['ulice'] ) : '',

		    'city'       => ! empty( $_POST['mesto'] ) ? wp_unslash( $_POST['mesto'] ) : '',

		    'postcode'   => ! empty( $_POST['psc'] ) ? wp_unslash( $_POST['psc'] ) : '',

		    'country'    => 'CZ'

		);



		if ( $reservation_order ) {

	        $new_order = $reservation_order;
	        $new_order->set_address( $address, 'billing' );
	        $newid = fajntabory_get_order_id( $new_order );

	    } else {

			$order_data = array(

		        'status' => apply_filters('woocommerce_default_order_status', 'pending'),

		        'customer_id' => get_current_user_id()

		    );



		    $new_order = wc_create_order( $order_data );

	        $new_order->set_address($address, 'billing');



	        if( !empty( $_POST['coupon_code'] ) ) {

	        	update_post_meta( fajntabory_get_order_id( $new_order ), 'coupon_code', sanitize_text_field($_POST['coupon_code']) );

	    	}



			foreach ($woocommerce->cart->get_cart() as $cart_item_key => $values) {

			    $new_order->add_product(

               		$values['data'], 

               		$values['quantity'], 

               		array(

                			'totals' => array(

                    			'subtotal' => $values['line_subtotal'],

                    			'subtotal_tax' => $values['line_subtotal_tax'],

                    			'total' => $values['line_total'],

                    			'tax' => $values['line_tax'],

                    			'tax_data' => $values['line_tax_data']

                			)

                		)

            		);



	        }



	        $new_order->calculate_totals();

	        $newid = fajntabory_get_order_id( $new_order );

		}



		$bank_account_number = fajntabory_get_order_bank_account_number( $new_order );



        foreach ($_POST as $key => $value) {

        	update_post_meta( $newid, $key, $value );

        }



        if ( $reservation_order ) {

        	update_post_meta( $newid, '_reservation_status', 'completed' );
        	update_post_meta( $newid, '_reservation_completed_at', current_time( 'mysql' ) );
            update_post_meta( $newid, '_fajntabory_incomplete_order', 'no' );

            if ( $new_order->has_status( fajntabory_get_reservation_tracking_statuses() ) ) {

                $new_order->set_status( apply_filters('woocommerce_default_order_status', 'pending') );
                $new_order->add_order_note( 'Přihláška byla doplněna. Objednávka byla přepnuta na stav Čeká na platbu.' );

            }

        }



        if ( method_exists( $new_order, 'save' ) ) {

        	$new_order->save();

        }


        // Vytvoření faktury, pokud je zatrhnuta platba zaměstnavatelem


		/////////
		/////////
		/////////
		/////////
		/////////
		/////////
		/////////
		/////////
		

        if( !empty( $_POST['proplaceni_tabora_zamestnavatelem'] ) ) {

        	$metas = array();

	        $metas['fakturace_odberatel_nazev'] = $_POST['fakturace_odberatel_nazev'];
			$metas['fakturace_odberatel_ulice'] = $_POST['fakturace_odberatel_ulice'];
			$metas['fakturace_odberatel_mesto'] = $_POST['fakturace_odberatel_mesto'];
			$metas['fakturace_odberatel_ico'] = $_POST['fakturace_odberatel_ico'];
			$metas['fakturace_odberatel_dic'] = $_POST['fakturace_odberatel_dic'];
			$metas['fakturace_variabilni_symbol'] = $newid;
			$metas['objednavka_cislo'] = $newid;
			$metas['fakturace_vystaveno'] = date('Y-m-d', time());
			$metas['fakturace_splatnost'] = date('Y-m-d', strtotime('+30 days',time()));
			$metas['zprava_pro_prijemce'] = $_POST['jmeno'] .' '. $_POST['prijmeni'];

			$metas['faktura_cislo_polozky'] = array();
			$metas['faktura_polozka'] = array();
			$metas['faktura_castka'] = array();

			$count = 0;
			$polozky = array();

			$object_order = new WC_Order( $newid );
				$items = $object_order->get_items();

				foreach ( $items as $item ) {
					
					$item_id = $item->get_product_id();
					$terms = get_the_terms( $item_id, 'product_cat' );
					$category = $terms[0]->term_id;
					$count++;

					$metas['faktura_cislo_polozky'][] = $count;
					$metas['faktura_polozka'][] = $item->get_name();
					$metas['faktura_castka'][] = $item->get_total();

					if( $category == 20 ) {
						if( !empty( $item->get_variation_id() ) ) {
							$product = get_product( $item->get_variation_id() );
							$product_attributes = $product->attributes;

							foreach( $product_attributes as $key => $value ) {
								if( $key == 'pa_terminy' ) {
									$termin = $value;
								}
								$product_attribute_term = get_term_by('slug', $product_attributes[$key], $key);
								if( !empty( $product_attribute_term->name ) ) {
									// $polozky[] = array(null, get_taxonomy( $key )->labels->singular_name.': '.$product_attribute_term->name, null);
									$metas['faktura_cislo_polozky'][] = null;
									$metas['faktura_polozka'][] = get_taxonomy( $key )->labels->singular_name.': '.$product_attribute_term->name;
									$metas['faktura_castka'][] = null;
								}
							}

							// $polozky[] = array( null, 'Dítě: ' . get_post_meta( $objednavka_cislo, 'jmeno' )[0] . ' ' . get_post_meta( $objednavka_cislo, 'prijmeni' )[0], null);
							$metas['faktura_cislo_polozky'][] = null;
							$metas['faktura_polozka'][] = 'Dítě: ' . $_POST['jmeno'] .' '. $_POST['prijmeni'];
							$metas['faktura_castka'][] = null;

							// $polozky[] = array( null, 'Zaměstnanec: ' . get_post_meta( $objednavka_cislo, 'Z_jmeno' )[0] . ' ' . get_post_meta( $objednavka_cislo, 'Z_prijmeni' )[0], null);

							$metas['faktura_cislo_polozky'][] = null;
							$metas['faktura_polozka'][] = 'Zaměstnanec: ' . $_POST['Z_jmeno'] .' '. $_POST['Z_prijmeni'];
							$metas['faktura_castka'][] = null;

						}
					}
				}

				$termin = get_term_by( 'slug', $termin, 'pa_terminy' );
				$termin = $termin->term_id;

				$posts = get_posts(array(
				    'numberposts'   => 1,
				    'post_type'     => 'faktury-spolecnosti',
				    'meta_key'      => 'terminy',
				    'meta_value'    => $termin
				));

				$metas['fakturace_dodavatel'] = $posts[0]->ID;

				$zvolena_spolecnost = $metas['fakturace_dodavatel'];
				$zvolena_spolecnost = (int)$zvolena_spolecnost;
				$cislo_dokladu = get_post_meta($zvolena_spolecnost, 'fakturace_spolecnost_cislovani', true);
				$cislo_dokladu = (int)$cislo_dokladu;
				$cislo_dokladu++;
				update_post_meta($zvolena_spolecnost, 'fakturace_spolecnost_cislovani', $cislo_dokladu);

				$metas['fakturace_cislo'] = $cislo_dokladu;

				$post_id = wp_insert_post( array (
					'post_type' => 'faktury',
					'post_title' => $cislo_dokladu,
					'post_status' => 'publish',
					'comment_status' => 'closed',
					'ping_status' => 'closed'
				) );

				if ( !empty( $post_id ) ) {

					foreach( $metas as $key => $value ) {
						if( $value != array() ) {
							update_post_meta( $post_id, $key, $value );
						} else {
							$value = serialize( $value );
							update_post_meta( $post_id, $key, $value );
						}
					}
				}

		}

		
		/////////
		/////////
		/////////
		/////////
		/////////
		/////////
		/////////
		/////////
		/////////



        $mailer = $woocommerce->mailer();



        $to = $_POST['email'];

		$subject = sprintf( __( 'Potvrzení o přijetí objednávky!' ), $newid );

		$headers = array('Content-Type: text/html; charset=UTF-8');

		$headers[] = 'From: Fajn Tábory <tereza@fajntabory.cz>';

		$headers[] = 'Cc: Fajn Tábory <tereza@fajntabory.cz>';

		

		$message_body  = null;

		$message_body .= '<p>Dobrý den,<br><br>potvrzujeme přijetí objednávky č. '.$newid.'. Pro dokončení objednávky proveďte platbu dle níže uvedených pokynů. Jakmile platbu zaevidujeme, zašleme Vám potvrzení o jejím přijetí. V případě možnosti proplacení Vaším zaměstnavatelem Vám zašleme fakturu na základě vyplněných fakturačních údajů.<br><br></p>';

		$message_body .= '<h2>Objednávka č. '.$newid.'</h2>';

		$message_body .= '<table>';

		switch( $_POST['objednavka'] ) {

			

			case 'a': 

				$message_body .= '<tr><td colspan="2"><h3>Údaje táborníka</h3></td></tr>';

				$message_body .= '<tr><td><strong>Jméno</strong>:</td><td>' . $_POST['jmeno'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Příjmení</strong>:</td><td>' . $_POST['prijmeni'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Datum narození</strong>:</td><td>' . $_POST['datum_narozeni'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Ulice č.p.</strong>:</td><td>' . $_POST['ulice'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Město, PSČ</strong>:</td><td>' . $_POST['mesto'] . ', ' . $_POST['psc'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Název školy</strong>:</td><td>' . $_POST['skola'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Velikost trička</strong>:</td><td>' . $_POST['triko'] . '</td></tr>';

				

				$message_body .= '<tr><td colspan="2"><h3>Údaje zákonného zástupce</h3></td></tr>';

				$message_body .= '<tr><td><strong>Jméno</strong>:</td><td>' . $_POST['Z_jmeno'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Příjmení</strong>:</td><td>' . $_POST['Z_prijmeni'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Telefon</strong>:</td><td>' . $_POST['telefon'] . '</td></tr>';

				$message_body .= '<tr><td><strong>E-mail</strong>:</td><td>' . $_POST['email'] . '</td></tr>';

				$message_body .= '<tr><td colspan="2"><h3>Poznámky</h3></td></tr>';

				$message_body .= '<tr><td><strong>Zdravotní stav, diety a jiné poznámky</strong>:</td><td>' . $_POST['zpusobilost'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Proplacení tábora zaměstnavatelem</strong></td><td></td></tr>';

				$message_body .= '<tr><td>Název zaměstnavatele: </td><td>'.$_POST['fakturace_odberatel_nazev'].'</td></tr>';
				$message_body .= '<tr><td>Ulice a č.p. </td><td>'.$_POST['fakturace_odberatel_ulice'].'</td></tr>';
				$message_body .= '<tr><td>PSČ, město: </td><td>'.$_POST['fakturace_odberatel_mesto'].'</td></tr>';
				$message_body .= '<tr><td>IČ zaměstnavatele: </td><td>'.$_POST['fakturace_odberatel_ico'].'</td></tr>';
				$message_body .= '<tr><td>DIČ zaměstnavatele: </td><td>'.$_POST['fakturace_odberatel_dic'].'</td></tr>';
				$message_body .= '<tr><td>Poznámky: </td><td>'.$_POST['zamestnavatel'].'</td></tr>';

				

				break;

			

			case 'b': 

				break;

			

			case 'c': 

				break;

			

			case 'd': 

				$message_body .= '<tr><td colspan="2"><h3>Údaje táborníka</h3></td></tr>';

				$message_body .= '<tr><td><strong>Jméno</strong>:</td><td>' . $_POST['jmeno'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Příjmení</strong>:</td><td>' . $_POST['prijmeni'] . '</td></tr>';

				$message_body .= '<tr><td><strong>Datum narození</strong>:</td><td>' . $_POST['datum_narozeni'] . '</td></tr>';

				$message_body .= '<tr><td colspan="2"><h3>Kontaktní údaje</h3></td></tr>';

				$message_body .= '<tr><td><strong>Telefon</strong>:</td><td>' . $_POST['telefon'] . '</td></tr>';

				$message_body .= '<tr><td><strong>E-mail</strong>:</td><td>' . $_POST['email'] . '</td></tr>';

				$message_body .= '<tr><td colspan="2"><h3>Poznámky</h3></td></tr>';

				$message_body .= '<tr><td colspan="2">' . $_POST['poznamky'] . '</td></tr>';

				break;



		}

		$message_body .= '</table><br><br>';



		$message_body .= '<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif;" border="1">';

		$message_body .= '<thead>';

		$message_body .= '<tr>';

		$message_body .= '<th class="td" scope="col" style="text-align:'.$text_align.';">Předmět</th>';

		$message_body .= '<th class="td" scope="col" style="text-align:'.$text_align.';">'.__( 'Price', 'woocommerce' ).'</th>';

		$message_body .= '</tr>';

		$message_body .= '</thead>';

		$message_body .= '<tbody>';

		$message_body .= wc_get_email_order_items( $new_order, array(

					'show_sku'      => $sent_to_admin,

					'show_image'    => false,

					'image_size'    => array( 32, 32 ),

					'plain_text'    => $plain_text,

					'sent_to_admin' => $sent_to_admin,

				) );

		$message_body .= '</tbody>';

		$message_body .= '<tfoot>';

		if ( $totals = $new_order->get_order_item_totals() ) {

			$i = 0;

			foreach ( $totals as $total ) {

				$i++;

				if( $total['label'] != 'Mezisoučet:' ) {

					if( $total['label'] == 'Sleva:' ) {

						$total['label'] = 'Slevový kupón:';

					}

					$message_body .= '<tr>';

					$message_body .= '<th class="td" scope="row">'.$total['label'].'</th>';

					$message_body .= '<td class="td">'.$total['value'].'</td>';

					$message_body .= '</tr>';

					$celkem = $total['value'];

				}

			}

		}

		$message_body .= '</tfoot>';

		$message_body .= '</table>';





		$message_body .= '<h2 style="margin-top: 15px;">Platební pokyny</h2>';

		

		if(!empty(get_option('bank_account'))) {

			$message_body .= 'Číslo účtu: '.$bank_account_number.'<br/>';

		}	


		/*

		if(!empty(get_option('bank_iban'))) {

			$message_body .= 'IBAN: '.get_option('bank_iban').'<br/>';

		}

		

		if(!empty(get_option('bank_swift'))) {

			$message_body .= 'SWIFT: '.get_option('bank_swift').'<br/>';

		}
		*/

		

		$message_body .= 'Variabilní symbol: '.$newid.'<br/>';

		$message_body .= 'Zpráva pro příjemce: '.$_POST['jmeno'] . ' ' . $_POST['prijmeni'].'<br/>';

		$date = strtotime("+7 day");

		$message_body .= '<strong>Částka: '.$celkem.'</strong><br/>';

		$message_body .= 'Datum splatnosti:	'.date( 'j.n.Y', $date ).'</p>';

		$message_body .= '<p>Po neobdržení platby do výše uvedeného data splatnosti, bude Vaše objednávka stornována v domnění, že jste již o ni ztratili zájem.<br/>';

		$message_body .= 'V případě opakovaného zájmu bude zapotřebí vyplnit objednávku znovu. Upozorňujeme, že cena již nemusí být stejná.</p>';

		$message_body .= '<h2>Těšíme se na Vaši účast! :)</h2>';

		$message_body .= '<p>FajnTábory</p>';

		

		$message = $mailer->wrap_message(

        	sprintf( __( 'Potvrzení o přijetí objednávky!' ), $newid ), $message_body 

        );

 

		$mailer->send( $to, $subject, $message, $headers );

		if ( ! $reservation_order ) {
			WC()->cart->empty_cart( true );
		}

		wp_safe_redirect( add_query_arg(
			array(
				'oid'   => $newid,
				'email' => $_POST['email'],
			),
			home_url( '/' )
		) );

		exit;



	}



	function accordion_shortcode( $atts, $content = null ) {

		$a = shortcode_atts( array(

			'title' => 'accordion',

		), $atts );



		$return =  '<div class="accordion">';

		$return .= '<h3><a href="#">' . esc_attr($a['title']) . '<em>+</em></a></h3>';

		$return .= '<div>' . $content . '</div>';

		$return .= '</div>';



		return $return;

	}



	add_shortcode( 'accordion', 'accordion_shortcode' );



	add_action( 'add_meta_boxes', function() {

		add_meta_box("order-meta-box", "Detaily objednávky", "order_meta_box_markup", "shop_order", "normal", "default", null);

	});



	function order_meta_box_markup() {

		?>

		<div id="order_data" class="panel">

			<div class="order_data_column_container">

				<div class="order_data_column">

					<h3>Údaje táborníka</h3>

				</div>

				<div class="order_data_column">

					<h3>Údaje z. zástupce</h3>

				</div>

				<div class="order_data_column">

					<h3>Poznámky</h3>

				</div>

			</div>

			<div class="clear"></div>

		</div>

		<?php

	}





	function terminy_datum_add_field() {

		// this will add the custom meta field to the add new term page

		?>

		<div class="form-field">

			<label for="term_meta[datum_od]"><?php _e( 'Datum od:', 'pippin' ); ?></label>

			<input type="date" name="term_meta[datum_od]" id="term_meta[datum_od]" value="">

		</div>

		<div class="form-field">

			<label for="term_meta[datum_do]"><?php _e( 'Datum do:', 'pippin' ); ?></label>

			<input type="date" name="term_meta[datum_do]" id="term_meta[datum_do]" value="">

		</div>

		<div class="form-field">

			<label for="term_meta[ucet]"><?php _e( 'Číslo účtu:', 'pippin' ); ?></label>

			<input type="text" name="term_meta[ucet]" id="term_meta[ucet]" value="">

		</div>

	<?php

	}

	add_action( 'pa_terminy_add_form_fields', 'terminy_datum_add_field', 10, 2 );



	function terminy_datum_edit_field($term) {

 

		$t_id = $term->term_id;

	 

		$term_meta = get_option( "taxonomy_$t_id" ); ?>

		<tr class="form-field">

			<th scope="row" valign="top">

				<label for="term_meta[datum_od]"><?php _e( 'Datum od:', 'pippin' ); ?></label>

			</th>

			<td>

				<input type="date" name="term_meta[datum_od]" id="term_meta[datum_od]" value="<?php echo esc_attr( $term_meta['datum_od'] ) ? esc_attr( $term_meta['datum_od'] ) : ''; ?>">

			</td>

		</tr>





		<tr class="form-field">

			<th scope="row" valign="top">

				<label for="term_meta[datum_do]"><?php _e( 'Datum do:', 'pippin' ); ?></label>

			</th>

			<td>

				<input type="date" name="term_meta[datum_do]" id="term_meta[datum_do]" value="<?php echo esc_attr( $term_meta['datum_do'] ) ? esc_attr( $term_meta['datum_do'] ) : ''; ?>">

			</td>

		</tr>


		<tr class="form-field">

			<th scope="row" valign="top">

				<label for="term_meta[ucet]"><?php _e( 'Číslo účtu:', 'pippin' ); ?></label>

			</th>

			<td>

				<input type="text" name="term_meta[ucet]" id="term_meta[ucet]" value="<?php echo esc_attr( $term_meta['ucet'] ) ? esc_attr( $term_meta['ucet'] ) : ''; ?>">

			</td>

		</tr>



	<?php

	}

	add_action( 'pa_terminy_edit_form_fields', 'terminy_datum_edit_field', 10, 2 );



	function save_taxonomy_custom_meta( $term_id ) {

		if ( isset( $_POST['term_meta'] ) ) {

			$t_id = $term_id;

			$term_meta = get_option( "taxonomy_$t_id" );

			$cat_keys = array_keys( $_POST['term_meta'] );

			foreach ( $cat_keys as $key ) {

				if ( isset ( $_POST['term_meta'][$key] ) ) {

					$term_meta[$key] = $_POST['term_meta'][$key];

				}

			}

			update_option( "taxonomy_$t_id", $term_meta );

		}

	}  

	add_action( 'edited_pa_terminy', 'save_taxonomy_custom_meta', 10, 2 );  

	add_action( 'create_pa_terminy', 'save_taxonomy_custom_meta', 10, 2 );



	add_theme_support( 'post-formats', array( 'gallery', 'image', 'video', 'status' ) );



	if(isset($_GET['gotest'])) {

		add_action('init', function() {

			var_dump(wp_get_post_parent_id(9401));

			exit;

		});

	}



	add_action( 'woocommerce_before_calculate_totals', function() {



		global $woocommerce;

    	$items = $woocommerce->cart->get_cart();

    	$total = 0;

    	$calls = 0;

    	$percent = 0;

    	$variation = false;



        foreach($items as $item => $values) { 



        	$variation = false;

        	

        	if( $values['variation_id'] != 0 ) {

        		$id = $values['variation_id'];

        		$variation = true;

        	} else {

        		$id = $values['product_id'];

        	}



        	if( !get_field('percent_boolean', $id) ) {

				$total = $total + abs($values['line_total']);

				if($variation) {

					$parent = wp_get_post_parent_id($id);

					if( has_term( 'tabory', 'product_cat', $parent ) ) {

						$calls = $calls + abs($values['line_total']);

					}

				}

        	}

        }



		foreach($items as $item => $values) {



			if( $values['variation_id'] != 0 ) {

        		$id = $values['variation_id'];

        	} else {

        		$id = $values['product_id'];

        	}



         	if( get_field('percent_boolean', $id) ) {

         		$calls = (int)$calls;

         		$percent = abs(get_field('percent_value', $id));

         		$diff = ( $calls / 100 ) * $percent;

         		$values['data']->set_price( $diff );

         	}

        }

        



	});



	function action_woocommerce_variation_options( $loop, $variation_data, $variation ) { 



    	echo '<div>';

    	echo '<p class="form-field form-row form-row-first">';

    	echo '<label for="variable_first_sale_price'.$loop.'">Počáteční cena po slevě (Kč)</label>';

    	echo '<input type="text" class="short wc_input_price" style="" name="variable_first_sale_price['.esc_attr( $loop ).']" id="variable_first_sale_price_'.esc_attr( $loop ).'" value="'.esc_attr( get_post_meta( $variation->ID, 'variable_first_sale_price', true ) ).'" placeholder="Počáteční cena po slevě">';

    	echo '</p>';

    	echo '<p class="form-field form-row form-row-last">';

    	echo '<label for="variable_raising_sale_price'.$loop.'">Částka pro týdenní navyšování (Kč)</label>';

    	echo '<input type="text" class="short wc_input_price" style="" name="variable_raising_sale_price['.esc_attr( $loop ).']" id="variable_raising_sale_price_'.esc_attr( $loop ).'" value="'.esc_attr( get_post_meta( $variation->ID, 'variable_raising_sale_price', true ) ).'" placeholder="Navyšovací cena">';

    	echo '</p>';

    	echo '</div>';

    }; 

         

	add_action( 'woocommerce_variation_options', 'action_woocommerce_variation_options', 10, 3 ); 



	function action_woocommerce_save_addititonal_data( $post_id, $i ) {



		if( !empty( $_POST['variable_first_sale_price'][$i] ) ) {

			$variable_first_sale_price = $_POST['variable_first_sale_price'][$i];

			update_post_meta( $post_id, 'variable_first_sale_price', $variable_first_sale_price );

		}

		

		if( !empty( $_POST['variable_raising_sale_price'][$i] ) ) {

			$variable_raising_sale_price = $_POST['variable_raising_sale_price'][$i];

			update_post_meta( $post_id, 'variable_raising_sale_price', $variable_raising_sale_price );

		}



	}



	add_action( 'woocommerce_save_product_variation',  'action_woocommerce_save_addititonal_data', 10, 2 );



	if( !empty( $_GET['updating'] )  && $_GET['updating'] == true ) {

		

		add_action( 'init', function() {

			

			$args = array(

				'post_type'			=> 'product',

				'post_status'		=> 'any',

				'posts_per_page' 	=> -1, 

				'tax_query' => array(

					array(

						'taxonomy' => 'product_cat',

						'field'    => 'slug',

						'terms'    => 'tabory',

					),

				)

			);



			$query = new WP_Query( $args );

			

			if( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					

					$query->the_post();

					

					global $product;

					if ( $product->is_type( 'variable' ) ) {

						$available_variations = $product->get_available_variations();

						foreach( $available_variations as $variation ) {



							$vid = $variation['variation_id'];

							$metas = get_post_meta( $vid );



							// Kontrola data

							$date = strtotime('today 23:59:59');

							$control = get_post_meta( $vid, '_sale_price_dates_to', $date );



							if( $control <= $date ) {



								$date = strtotime("+7 day", $date);

								update_post_meta( $vid, '_sale_price_dates_to', $date );



								$before = (int)get_post_meta( $vid, '_sale_price', true );

								$add = (int)get_post_meta( $vid, 'variable_raising_sale_price', true );

								$after = $before + $add;

								// $regular = (int)get_post_meta( $vid, '_regular_price', true );



								update_post_meta( $vid, '_sale_price', $after );

							}

						}

					}

				}

				exit;

			}

		});

	}



	add_action( 'wp_ajax_pchose', 'pchose' );

	add_action( 'wp_ajax_nopriv_pchose', 'pchose' );



	function pchose() {



		global $woocommerce;

		$response = array();

		if (
			empty( $_POST['nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fajntabory_product_choice' )
		) {
			wp_send_json( array( 'error' => 'invalid_nonce' ) );
		}

		$id = ! empty( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		$p_attributes = ! empty( $_POST['post'] ) && is_array( $_POST['post'] ) ? wp_unslash( $_POST['post'] ) : array();

		$p_attributes = ! empty( $p_attributes[0] ) && is_array( $p_attributes[0] ) ? array_map( 'sanitize_text_field', $p_attributes[0] ) : array();

		if ( $id < 1 || empty( $p_attributes ) ) {
			wp_send_json( array( 'error' => 'invalid_request' ) );
		}

		ksort($p_attributes);

		

		$product = new WC_Product_Variable( $id );

		$variations = $product->get_available_variations();



		foreach ($variations as $variation) {



			// var_dump( $variation ); exit;



		 	$d_attributes = $variation['attributes'];

		 	ksort($d_attributes);

		 	if( $p_attributes == $d_attributes ) {



		 		$response['id'] = $variation['variation_id'];

		 		$response['variation_price'] = $variation['display_regular_price'];

		 		$response['variation_discount'] = $variation['display_price'];

		 		$response['variation_qty'] = $variation['max_qty'];

		 		

				wp_send_json( $response );



		 	}

		}

		wp_send_json( array( 'error' => 'not_found' ) );



	}





	add_action( 'wp_ajax_dchose', 'dchose' );

	add_action( 'wp_ajax_nopriv_dchose', 'dchose' );



	if ( ! function_exists( 'fajntabory_decode_choice_ids' ) ) {
		function fajntabory_decode_choice_ids( $value ) {
			if ( is_array( $value ) ) {
				$decoded = $value;
			} else {
				$raw = is_scalar( $value ) ? trim( wp_unslash( $value ) ) : '';
				$decoded = json_decode( $raw, true );

				if ( ! is_array( $decoded ) && defined( 'PHP_VERSION_ID' ) && PHP_VERSION_ID >= 70000 ) {
					$decoded = @unserialize( $raw, array( 'allowed_classes' => false ) );
				}
			}

			if ( ! is_array( $decoded ) ) {
				return array();
			}

			return array_values( array_filter( array_map( 'absint', $decoded ) ) );
		}
	}



	function dchose() {



		global $woocommerce;



		if (
			empty( $_POST['nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fajntabory_product_choice' )
		) {
			wp_send_json( array( 'error' => 'invalid_nonce' ) );
		}


		$n_chose = ! empty( $_POST['n_chose'] ) ? fajntabory_decode_choice_ids( $_POST['n_chose'] ) : array();

		$v_chose = ! empty( $_POST['v_chose'] ) ? fajntabory_decode_choice_ids( $_POST['v_chose'] ) : array();

		$t_chose = ! empty( $_POST['t_chose'] ) ? fajntabory_decode_choice_ids( $_POST['t_chose'] ) : array();

		$z_chose = ! empty( $_POST['z_chose'] ) ? fajntabory_decode_choice_ids( $_POST['z_chose'] ) : array();

		if ( empty( $n_chose ) || empty( $v_chose ) || empty( $t_chose ) || empty( $z_chose ) ) {
			wp_send_json( array( 'error' => 'invalid_request' ) );
		}



		$result = array_intersect( $n_chose, $v_chose, $t_chose, $z_chose );

		$response = array();



		if( count($result) == 1 ) {

			$product = wc_get_product( reset($result) );

			$response['id'] = reset($result);



			$response['capacity'] = (int)get_post_meta( reset($result), '_stock', true) - (int)count( retrieve_orders_ids_from_a_product_id(reset($result)) );



			// $response['capacity'] = $product->get_stock_quantity();

			$response['price'] = $product->get_regular_price();

			$response['sale_bool'] = $product->is_on_sale();

			$response['sale_to'] = '<br><small>platí do '. date('j.n.Y', get_post_meta( reset($result), '_sale_price_dates_to', true ) ) .'</small>';

			$response['sale_price'] = $product->get_sale_price();

		} else {

			$response['error'] = $n_chose;

		}



		wp_send_json( $response );

	}





	function retrieve_orders_ids_from_a_product_id( $product_id ) {

	    global $wpdb;



	    $orders_statuses = "'wc-incomplete', 'wc-pending', 'wc-completed', 'wc-processing', 'wc-on-hold'";



	    $orders_ids = $wpdb->get_col( $wpdb->prepare( "

	        SELECT DISTINCT woi.order_id

	        FROM {$wpdb->prefix}woocommerce_order_itemmeta as woim, 

	             {$wpdb->prefix}woocommerce_order_items as woi, 

	             {$wpdb->prefix}posts as p

	        WHERE  woi.order_item_id = woim.order_item_id

	        AND woi.order_id = p.ID

	        AND p.post_status IN ( $orders_statuses )

	        AND woim.meta_key LIKE '_variation_id'

	        AND woim.meta_value = %s

	        ORDER BY woi.order_item_id DESC

	    ", $product_id ) );

	    // Return an array of Orders IDs for the given product ID

	    return $orders_ids;

	}



	add_filter('embed_oembed_html', 'wrap_embed_with_div', 10, 3);



	function wrap_embed_with_div($html, $url, $attr) {

		return "<div class=\"responsive-container\">".$html."</div>";

	}

	function matched_cart_items( $search_products ) {
    
	    $count = 0;
	    if ( ! WC()->cart->is_empty() ) {
	        foreach( WC()->cart->get_cart() as $cart_item ) {
	            $cart_item_ids = array( $cart_item['product_id'], $cart_item['variation_id'] );
	            if( ( is_array($search_products) && array_intersect($search_products, $cart_item_ids) ) || ( !is_array($search_products) && in_array($search_products, $cart_item_ids) ) ) {
	                $count++;
	            }
	        }
	    }

	    return $count;
	}	

	function get_storno_value( $id ) {

		global $woocommerce;
    	$items = $woocommerce->cart->get_cart();
    	$total = 0;
    	$calls = 0;
    	$percent = 0;
    	$variation = false;

        foreach($items as $item => $values) {

        	$variation = false;
        	if( $values['variation_id'] != 0 ) {
        		$sid = $values['variation_id'];
        		$variation = true;
        	} else {
        		$sid = $values['product_id'];
        	}

        	if( !get_field('percent_boolean', $sid) ) {
				$total = $total + abs($values['line_total']);
				if($variation) {
					$parent = wp_get_post_parent_id($sid);
					if( has_term( 'tabory', 'product_cat', $parent ) ) {
						$calls = $calls + abs($values['line_total']);
					}
				}
        	}

        }

        if( get_field('percent_boolean', $id) ) {
         	$calls = (int)$calls;
         	$percent = abs(get_field('percent_value', $id));
         	$diff = ( $calls / 100 ) * $percent;
         	return $diff;
        }

	}

	if( isset($_GET['accepted']) && $_GET['accepted'] == 1 ) {
		add_action('init', function() {
			setcookie('accepted', 1, 2147483647);
		});
	}


?>
