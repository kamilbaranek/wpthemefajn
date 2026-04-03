<?php

	function get_paged_link( $paged ) {

		$get_parameters = null;
		$and = '?';

		$_GET['paged'] = $paged;

		foreach( $_GET as $key => $value ) {
			$get_parameters .= $and . $key . '=' . $value;
			$and = '&';
		}

		return $get_parameters;
	}

	function sklonovani($pocet, $slova) {
		$pocet = abs($pocet);
		if ($pocet == 1) return $slova[0];
		if ($pocet < 5 && $pocet > 0) return $slova[1];
		return $slova[2];
	}


	$args = array(
		'public' => false,
		'show_ui' => false,
		'show_in_menu' => false
	);

	register_post_type( 'faktury', $args );
	register_post_type( 'faktury-spolecnosti', $args );

	add_action( 'admin_menu', function() { 

		add_menu_page( 
			'Fakturace',
			'Fakturace',
			'manage_options',
			'fakturace',
			'fakturace_content',
			'dashicons-money',
			58
		);

		add_submenu_page( 
			'fakturace',
			'Společnosti',
			'Společnosti',
			'manage_options',
			'fakturace-spolecnosti',
			'fakturace_spolecnosti_content',
			5
		);

		add_submenu_page( 
			'fakturace',
			'Nastavení',
			'Nastavení',
			'manage_options',
			'fakturace-nastaveni',
			'fakturace_nastaveni_content',
			10
		);

	} );



	function fakturace_content() {

		echo '<div class="wrap">';

// Zobrazit seznam vydaných faktur

		if( empty( $_GET['action'] ) ) {

			$paged = $_GET['paged'] ? $_GET['paged'] : 1;
			$meta_query = array();
			$search_string = null;
			$search_filter = null;
			$search_dodavatel = null;

			$filtry = array(
				'fakturace_cislo'			=> 'Číslo dokladu',
				'objednavka_cislo'			=> 'Číslo objednávky',
				'fakturace_odberatel_nazev'	=> 'Název odběratele',
				'zprava_pro_prijemce'		=> 'Jméno dítěte'
			);

			$args =  array(
				'post_type' => 'faktury',
				'orderby' => 'date',
				'order' => 'DESC',
				'posts_per_page' => '20',
				'paged' => $paged,
			);

			if( !empty( $_GET['search']) ) {
				$search_string = $_GET['search'];
			}

			if( !empty( $_GET['filter'] ) ) {
				$search_filter = $_GET['filter'];
			}

			if( !empty( $_GET['dodavatel'] ) ) {
				$search_dodavatel = $_GET['dodavatel'];
			}

			if( !empty($search_string) ) {
				$meta_query[] = array(
					'key' => $search_filter,
					'value' => $search_string,
					'compare' => 'LIKE'
				);
			}

			if( !empty($search_dodavatel) && $search_dodavatel > 0 ) {
				$meta_query[] = array(
					'key' => 'fakturace_dodavatel',
					'value' => $search_dodavatel,
					'compare' => 'LIKE'
				);
			}

			if(count($meta_query) > 1) {
				$meta_query['relation'] = 'AND';
			}

			if( !empty( $meta_query ) ) {
				$args['meta_query'] = $meta_query;
			}

			$query = new WP_Query($args);
			$max_num_pages = $query->max_num_pages;
			
			echo '<h1 class="wp-heading-inline">Fakturace</h1>';
			echo '<a href="' . admin_url('admin.php?page=fakturace&action=new') . '" class="page-title-action">Vytvořit fakturu</a>';
			echo '<hr class="wp-header-end">';
			if( !empty($_GET['message']) && $_GET['message'] == 'deleted' ) {
				echo '<div id="message" class="error notice notice-success"><p>Faktura byla smazána.</p></div>';
			}

			// echo '<form id="posts-filter" method="get">';

			echo '<div class="wp-filter">';

			echo '<div class="search-form invoice_search">';
			echo '<form method="get">';
			echo '<input type="hidden" name="page" value="fakturace" />';
			echo '<label class="screen-reader-text" for="post-search-input">Hledat produkty:</label>';
			echo '<input autocomplete="off" type="search" placeholder="Vyhledávání..." id="post-search-input" name="search" value="'.$search_string.'"> ';
			echo '<select name="filter" id="postform">';
			echo '<option value="-1">Vyberte filtr</option>';
			foreach( $filtry as $key => $value ) {
				if( $search_filter == $key ) {
					echo '<option selected value="'.$key.'">'.$value.'</option>';
				} else {
					echo '<option value="'.$key.'">'.$value.'</option>';
				}
			}
			echo '</select> ';
			echo '<select name="dodavatel" id="postform">';
			echo '<option value="-1">Vyberte dodavatele</option>';

			$args = array(
				'numberposts' => 20,
				'post_type'   => 'faktury-spolecnosti',
				'order'       => 'ASC',
				'orderby'     => 'date'
			);

			$spolecnosti = get_posts( $args );
			foreach($spolecnosti as $spolecnost) {
				if( $search_dodavatel == $spolecnost->ID ) {
					echo '<option selected value="'.$spolecnost->ID.'">'.$spolecnost->post_title.'</option>';
				} else {
					echo '<option value="'.$spolecnost->ID.'">'.$spolecnost->post_title.'</option>';
				}
			}

			echo '</select> ';

			echo '<input type="submit" id="search-submit" class="button" value="Hledat faktury"> ';
			echo '<a href="'.admin_url('admin.php?page=fakturace').'" class="button">Reset</a>';
			echo '</form>';
			echo '</div>';
			echo '</div>';

			$args_count = array(
				'post_type' => 'faktury'
			);
			
			if( !empty( $meta_query ) ) {
				$args_count['meta_query'] = $meta_query;
			}

			$count_query = new WP_Query( $args_count );
			$count_invoices = $count_query->found_posts;

			echo '<div class="notice notice-error"><p><strong>Důležité upozornění</strong>, číslo faktury je součástí nastavení společností. Pokud pro číslování na začátku používáte rok, je nutné na začátku nového roku toto číslo upravit např. na '.date('Y', time()).'0001. Nesprávné nastavení číslování může mít fatální vliv na fakturace.</p></div>';

			echo '<div class="tablenav top">';
			echo '<div class="alignleft">';
			echo sklonovani( $count_invoices, array('Nalezen', 'Nalezeny', 'Nalezeno') ) .' <strong>'. $count_invoices .'</strong> '. sklonovani( $count_invoices, array('doklad', 'doklady', 'dokladů') );
			echo '</div>';
			echo '<div class="tablenav-pages">';
			
			if( $paged > 1 ) {
				echo '<a class="first-page" href="'.admin_url('admin.php' . get_paged_link( 1 ) ).'"><span class="screen-reader-text">První stránka</span><span aria-hidden="true">«</span></a> ';
				echo '<a class="prev-page" href="'.admin_url('admin.php' . get_paged_link( ($paged - 1 ) ) ).'"><span class="screen-reader-text">Předchozí stránka</span><span aria-hidden="true">‹</span></a> ';
			} else {
				echo '<span class="tablenav-pages-navspan" aria-hidden="true">«</span> ';
				echo '<span class="tablenav-pages-navspan" aria-hidden="true">‹</span> ';
			}
			echo '<span class="displaying-num">Stránka '.$paged.' z celkem '.$max_num_pages.'</span>';
			if( $paged < $max_num_pages ) {
				echo '<a class="next-page" href="'.admin_url('admin.php' . get_paged_link( ( $paged + 1 ) ) ).'"><span class="screen-reader-text">Následující stránka</span><span aria-hidden="true">›</span></a> ';
				echo '<a class="last-page" href="'.admin_url('admin.php' . get_paged_link( $max_num_pages ) ).'"><span class="screen-reader-text">Poslední stránka</span><span aria-hidden="true">»</span></a> ';
			} else {
				echo '<span class="tablenav-pages-navspan" aria-hidden="true">›</span> ';
				echo '<span class="tablenav-pages-navspan" aria-hidden="true">»</span> ';
			}
			echo '</div>';
			echo '</div>';

			echo '</form>';

			echo '<br>';
			echo '<table class="wp-list-table widefat fixed striped posts">';

			echo '<thead>';
			echo '<tr>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo dokladu</th>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo objednávky</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Dodavatel</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Odběratel</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Jméno dítěte</th>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Částka celkem</th>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Akce</th>';
			echo '</tr>';
			echo '</thead>';

			echo '<tbody id="the-list">';

			if( $count_invoices == 0 ) {

				echo '<td colspan="7">Nic jsme tu nenašli :( </td>';

			} else {

				if ( $query->have_posts() ) :
					while ( $query->have_posts() ) : $query->the_post(); 
						$post_id = get_the_ID();

						echo '<tr>';
						$dodavatel_id = get_post_meta( $post_id, 'fakturace_dodavatel', true );
						$castka = get_post_meta( $post_id, 'faktura_castka', true );
						$j = 0;
						foreach( $castka as $key => $value) {
							$j = $j + (int)$value;
						}

						$j = number_format($j, 2, ',', ' ');

						echo '<td><a href="https://www.fajntabory.cz/wp-admin/admin.php?page=fakturace&action=update&id='.$post_id.'">' . get_post_meta( $post_id, 'fakturace_cislo', true ) . '</a></td>';
						echo '<td><a href="'.admin_url('post.php?action=edit&post=').get_post_meta( $post_id, 'objednavka_cislo', true ).'">' . get_post_meta( $post_id, 'objednavka_cislo', true ) . '</a></td>';
						echo '<td>' . get_post_meta( $dodavatel_id, 'fakturace_spolecnost_nazev', true ) . '</td>';
						echo '<td>' . get_post_meta( $post_id, 'fakturace_odberatel_nazev', true ) . '</td>';
						echo '<td>' . get_post_meta( $post_id, 'zprava_pro_prijemce', true ) . '</td>';
						echo '<td>' . $j . ' kč</td>';
						echo '<td class="iactions"><a class="button wc-action-button dashicons dashicons-edit" href="'.admin_url('admin.php?page=fakturace&action=update&id=').$post_id.'"></a> ';
						echo '<a class="button wc-action-button dashicons dashicons-download" target="_blank" href="'.admin_url('admin.php?invoice=' . $post_id) .'"></a> ';
						echo '<a class="button wc-action-button dashicons dashicons-trash" onclick="return confirm(\'Opravdu chcete fakturu smazat?\')" href="'.admin_url('admin.php?page=fakturace&action=delete&id=' . $post_id) .'"></a></td>';

						echo '</tr>';

					endwhile;
				endif;
				wp_reset_postdata();

			}

			echo '</tbody>';

			echo '<tfoot>';
			echo '<tr>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo dokladu</th>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo objednávky</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Dodavatel</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Odběratel</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Jméno dítěte</th>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Částka celkem</th>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Akce</th>';
			echo '</tr>';
			echo '</tfoot>';

			echo '</table>';

			echo '<form id="posts-filter" method="get">';

			echo '<div class="tablenav bottom">';
			echo '<div class="tablenav-pages">';
			if( $paged > 1 ) {
				echo '<a class="first-page" href="'.admin_url('admin.php?page=fakturace&paged=1').'"><span class="screen-reader-text">První stránka</span><span aria-hidden="true">«</span></a> ';
				echo '<a class="prev-page" href="'.admin_url('admin.php?page=fakturace&paged=' . ($paged - 1 ) ).'"><span class="screen-reader-text">Předchozí stránka</span><span aria-hidden="true">‹</span></a> ';
			} else {
				echo '<span class="tablenav-pages-navspan" aria-hidden="true">«</span> ';
				echo '<span class="tablenav-pages-navspan" aria-hidden="true">‹</span> ';
			}
			echo '<span class="displaying-num">Stránka '.$paged.' z celkem '.$max_num_pages.'</span>';
			if( $paged < $max_num_pages ) {
				echo '<a class="next-page" href="'.admin_url('admin.php?page=fakturace&paged=' . ($paged + 1 ) ).'"><span class="screen-reader-text">Následující stránka</span><span aria-hidden="true">›</span></a> ';
				echo '<a class="last-page" href="'.admin_url('admin.php?page=fakturace&paged='.$max_num_pages).'"><span class="screen-reader-text">Poslední stránka</span><span aria-hidden="true">»</span></a> ';
			} else {
				echo '<span class="tablenav-pages-navspan" aria-hidden="true">›</span> ';
				echo '<span class="tablenav-pages-navspan" aria-hidden="true">»</span> ';
			}
			echo '</div>';
			echo '</div>';

			// echo '</form>';

// Odstranění faktury

		} else if( !empty( $_GET['action'] ) && !empty( $_GET['id'] ) && $_GET['action'] == 'delete' ) {

			wp_delete_post($_GET['id']);
			wp_redirect( admin_url('admin.php?page=fakturace&message=deleted') );
			wp_die();


// Vytvoření nové faktury

		} else if( !empty( $_GET['action']) && $_GET['action'] == 'new' ) {


			if(!empty($_POST)) {

				// Najdeme spolecnost, poslední použité číslo, zvýšíme ho a propíšeme
				$zvolena_spolecnost = $_POST['fakturace_dodavatel'];
				$zvolena_spolecnost = (int)$zvolena_spolecnost;
				$cislo_dokladu = get_post_meta($zvolena_spolecnost, 'fakturace_spolecnost_cislovani', true);
				$cislo_dokladu = (int)$cislo_dokladu;
				$cislo_dokladu++;
				update_post_meta($zvolena_spolecnost, 'fakturace_spolecnost_cislovani', $cislo_dokladu);
				$_POST['fakturace_cislo'] = $cislo_dokladu;

				// Vytvoříme záznam

				$post_id = wp_insert_post( array (
					'post_type' => 'faktury',
					'post_title' => $_POST['fakturace_cislo'],
					'post_status' => 'publish',
					'comment_status' => 'closed',
					'ping_status' => 'closed'
				) );

				if ( !empty( $post_id ) ) {
					foreach ($_POST as $key => $value) {
						if( $value != array() ) {
							update_post_meta( $post_id, $key, $value );
						} else {
							$value = serialize( $value );
							update_post_meta( $post_id, $key, $value );
						}
					}
					wp_redirect( admin_url('admin.php?page=fakturace&message=created&action=update&id=' . $post_id) );
				}
				
				wp_die();
			}

// Vytvoření faktury z objednávky

			if( !empty($_GET['order']) ) {

				$count = 0;
				$polozky = array();
				$objednavka_cislo = $_GET['order'];
				$lokalita = null;
				$termin = null;
				$dite = null;
				$zamestnanec = null;
				$fakturace_dodavatel = null;
				$fakturace_odberatel_nazev = get_post_meta( $objednavka_cislo, 'fakturace_odberatel_nazev' )[0];
				$fakturace_odberatel_ulice = get_post_meta( $objednavka_cislo, 'fakturace_odberatel_ulice' )[0];
				$fakturace_odberatel_mesto = get_post_meta( $objednavka_cislo, 'fakturace_odberatel_mesto' )[0];
				$fakturace_odberatel_ico = get_post_meta( $objednavka_cislo, 'fakturace_odberatel_ico' )[0];
				$fakturace_odberatel_dic = get_post_meta( $objednavka_cislo, 'fakturace_odberatel_dic' )[0];
				$fakturace_variabilni_symbol = null;
				$fakturace_vystaveno = null;
				$fakturace_splatnost = null;

				$object_order = new WC_Order( $objednavka_cislo );
				$items = $object_order->get_items();

				foreach ( $items as $item ) {
					
					$item_id = $item->get_product_id();
					$terms = get_the_terms( $item_id, 'product_cat' );
					$category = $terms[0]->term_id;
					$count++;

					$polozky[] = array( $count, $item->get_name(), $item->get_total());

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
									$polozky[] = array(null, get_taxonomy( $key )->labels->singular_name.': '.$product_attribute_term->name, null);
								}
							}

							$polozky[] = array( null, 'Dítě: ' . get_post_meta( $objednavka_cislo, 'jmeno' )[0] . ' ' . get_post_meta( $objednavka_cislo, 'prijmeni' )[0], null);
							$polozky[] = array( null, 'Zaměstnanec: ' . get_post_meta( $objednavka_cislo, 'Z_jmeno' )[0] . ' ' . get_post_meta( $objednavka_cislo, 'Z_prijmeni' )[0], null);

						}
					}
				}

				$termin = get_term_by( 'slug', $termin, 'pa_terminy' );
				$termin = $termin->term_id;

				$args = array(
					'post_type'  => 'faktury-spolecnosti',
					'meta_key' => 'terminy',
					'meta_query' => array(
						array(
					    	'key' => 'terminy',
							'value' => $termin,
					    	'compare' => '=',
					       )
				   )
				);
				$query = new WP_Query($args);

				$posts = get_posts(array(
				    'numberposts'   => 1,
				    'post_type'     => 'faktury-spolecnosti',
				    'meta_key'      => 'terminy',
				    'meta_value'    => $termin
				));

				

				echo '<h1 class="wp-heading-inline">Vytvořit novou fakturu</h1>';
				echo '<hr class="wp-header-end">';
				echo '<br>';

				echo '<form method="POST" class="validate invoice">';
				echo '<div id="col-container" class="wp-clearfix">';
				echo '<div id="col-left">';
				echo '<div class="col-wrap">';

				echo '<div class="form-wrap">';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_cislo">Číslo faktury</label>';
				echo '<input type="number" size="10" aria-required="true" name="fakturace_cislo" id="fakturace_cislo" readonly><br>';
				echo '<p>Číslo faktury ponechte prázdné, bude automaticky přiděleno po vytvoření faktury.</p>';
				echo '</div>';


				echo '<div class="form-field form-required">';
				echo '<label for="objednavka_cislo">Číslo objednávky</label>';
				echo '<input type="number" size="10" aria-required="true" value="'.$objednavka_cislo.'" name="objednavka_cislo" id="objednavka_cislo"><br>';
				echo '<p>Zde zadejte číslo objednávky tábora. Systém pomocí tohoto čísla páruje objednávky s fakturou.</p>';
				echo '</div>';

				echo '<h2>Dodavatel</h2>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_dodavatel">Dodavatel</label>';
				$args = array(
					'numberposts' => 20,
					'post_type'   => 'faktury-spolecnosti',
					'order'       => 'ASC',
					'orderby'     => 'date'
				);

				echo '<select name="fakturace_dodavatel">';
				$spolecnosti = get_posts( $args );
				foreach($spolecnosti as $spolecnost) {

					if( $posts[0]->ID == $spolecnost->ID ) {
						echo '<option selected value="'.$spolecnost->ID.'">'.$spolecnost->post_title.'</option>';
					} else {
						echo '<option value="'.$spolecnost->ID.'">'.$spolecnost->post_title.'</option>';
					}

				}
				echo '</select>';
				echo '<p>Vyberte dodavatele, do faktury se po-té načtou uložené fakturační údaje. Podle zvoleného dodavatele bude dokladu přiděleno také číslo.</p>';
				echo '</div>';

				echo '<h2>Odběratel</h2>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_odberatel_nazev">Název společnosti</label>';
				echo '<input type="text" size="10" aria-required="true" value="'.$fakturace_odberatel_nazev.'" name="fakturace_odberatel_nazev" id="fakturace_odberatel_nazev"><br>';
				echo '<p>Název se bude v této podobě zobrazovat na faktuře v části odběratel.</p>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_odberatel_ulice">Ulice, č.p.</label>';
				echo '<input type="text" size="10" aria-required="true" value="'.$fakturace_odberatel_ulice.'" name="fakturace_odberatel_ulice" id="fakturace_odberatel_ulice"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_odberatel_mesto">PSČ, Město</label>';
				echo '<input type="text" size="10" aria-required="true" value="'.$fakturace_odberatel_mesto.'" name="fakturace_odberatel_mesto" id="fakturace_odberatel_mesto"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_odberatel_ico">IČ</label>';
				echo '<input type="text" size="10" aria-required="true" value="'.$fakturace_odberatel_ico.'" name="fakturace_odberatel_ico" id="fakturace_odberatel_ico"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_odberatel_dic">DIČ</label>';
				echo '<input type="text" size="10" aria-required="true" value="'.$fakturace_odberatel_dic.'" name="fakturace_odberatel_dic" id="fakturace_odberatel_dic"><br>';
				echo '</div>';

				echo '<h2>Platební údaje</h2>';



				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_variabilni_symbol">Variabilní symbol</label>';
				echo '<input type="number" size="10" aria-required="true" value="'.$objednavka_cislo.'" name="fakturace_variabilni_symbol" id="fakturace_variabilni_symbol"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="zprava_pro_prijemce">Zpráva pro příjemce</label>';
				echo '<input type="text" size="10" aria-required="true" name="zprava_pro_prijemce" value="'.get_post_meta( $objednavka_cislo, 'jmeno' )[0] . ' ' . get_post_meta( $objednavka_cislo, 'prijmeni' )[0] . '" id="zprava_pro_prijemce">';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_vystaveno">Datum vystavení</label>';
				echo '<input type="date" size="10" value="'.date('Y-m-d', time()).'" aria-required="true" name="fakturace_vystaveno" id="fakturace_vystaveno"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_splatnost">Datum splatnosti</label>';
				echo '<input type="date" size="10" value="'.date('Y-m-d', strtotime('+30 days',time())).'" aria-required="true" name="fakturace_splatnost" id="fakturace_splatnost"><br>';
				echo '</div>';

				

				echo '<input type="submit" class="button button-primary" value="Vytvořit fakturu">';
				echo '</div>';
				echo '</div>';
				echo '</div><!-- /col-left -->';

				echo '<div id="col-right">';
				echo '<div class="col-wrap">';

				echo '<h2>Položky faktury</h2>';

				echo '<table class="wp-list-table widefat fixed striped posts">';

				echo '<thead>';
				echo '<tr>';
				echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo položky</th>';
				echo '<th scope="col" id="" class="manage-column column-primary">Položka</th>';
				echo '<th scope="col" id="" class="manage-column column-primary column-date">Částka</th>';
				echo '</tr>';
				echo '</thead>';

				echo '<tbody id="the-list">';


				foreach ( $polozky as $polozka ) {

					echo '<tr>';
					echo '<td><input type="number" value="'.$polozka[0].'" name="faktura_cislo_polozky[]"></td>';
					echo '<td><input type="text" value="'.$polozka[1].'" name="faktura_polozka[]"></td>';
					echo '<td><input type="number" value="'.ceil($polozka[2]).'" name="faktura_castka[]"></td>';
					echo '</tr>';

				}

				echo '</tbody>';

				echo '<tfoot>';
				echo '<tr>';
				echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo položky</th>';
				echo '<th scope="col" id="" class="manage-column column-primary">Položka</th>';
				echo '<th scope="col" id="" class="manage-column column-primary column-date">Částka</th>';
				echo '</tr>';
				echo '</tfoot>';

				echo '</table>';

				echo '</div>';
				echo '</div><!-- /col-right -->';
				echo '</div>';


				echo '</form>';

			} else {

				echo '<h1 class="wp-heading-inline">Vytvořit novou fakturu</h1>';
				echo '<hr class="wp-header-end">';
				echo '<br>';

				echo '<form method="POST" class="validate invoice">';
				echo '<div id="col-container" class="wp-clearfix">';
				echo '<div id="col-left">';
				echo '<div class="col-wrap">';

				echo '<div class="form-wrap">';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_cislo">Číslo faktury</label>';
				echo '<input type="number" size="10" aria-required="true" name="fakturace_cislo" id="fakturace_cislo" readonly><br>';
				echo '<p>Číslo faktury ponechte prázdné, bude automaticky přiděleno po vytvoření faktury.</p>';
				echo '</div>';


				echo '<div class="form-field form-required">';
				echo '<label for="objednavka_cislo">Číslo objednávky</label>';
				echo '<input type="number" size="10" aria-required="true" name="objednavka_cislo" id="objednavka_cislo"><br>';
				echo '<p>Zde zadejte číslo objednávky tábora. Systém pomocí tohoto čísla páruje objednávky s fakturou.</p>';
				echo '</div>';

				echo '<h2>Dodavatel</h2>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_dodavatel">Dodavatel</label>';
				$args = array(
					'numberposts' => 20,
					'post_type'   => 'faktury-spolecnosti',
					'order'       => 'ASC',
					'orderby'     => 'date'
				);

				echo '<select name="fakturace_dodavatel">';
				$spolecnosti = get_posts( $args );
				foreach($spolecnosti as $spolecnost) {

					echo '<option value="'.$spolecnost->ID.'">'.$spolecnost->post_title.'</option>';

				}
				echo '</select>';
				echo '<p>Vyberte dodavatele, do faktury se po-té načtou uložené fakturační údaje. Podle zvoleného dodavatele bude dokladu přiděleno také číslo.</p>';
				echo '</div>';

				echo '<h2>Odběratel</h2>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_odberatel_nazev">Název společnosti</label>';
				echo '<input type="text" size="10" aria-required="true" name="fakturace_odberatel_nazev" id="fakturace_odberatel_nazev"><br>';
				echo '<p>Název se bude v této podobě zobrazovat na faktuře v části odběratel.</p>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_odberatel_ulice">Ulice, č.p.</label>';
				echo '<input type="text" size="10" aria-required="true" name="fakturace_odberatel_ulice" id="fakturace_odberatel_ulice"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_odberatel_mesto">PSČ, Město</label>';
				echo '<input type="text" size="10" aria-required="true" name="fakturace_odberatel_mesto" id="fakturace_odberatel_mesto"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_odberatel_ico">IČ</label>';
				echo '<input type="text" size="10" aria-required="true" name="fakturace_odberatel_ico" id="fakturace_odberatel_ico"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_odberatel_dic">DIČ</label>';
				echo '<input type="text" size="10" aria-required="true" name="fakturace_odberatel_dic" id="fakturace_odberatel_dic"><br>';
				echo '</div>';

				echo '<h2>Platební údaje</h2>';



				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_variabilni_symbol">Variabilní symbol</label>';
				echo '<input type="number" size="10" aria-required="true" name="fakturace_variabilni_symbol" id="fakturace_variabilni_symbol"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="zprava_pro_prijemce">Zpráva pro příjemce</label>';
				echo '<input type="text" size="10" aria-required="true" name="zprava_pro_prijemce" id="zprava_pro_prijemce"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_vystaveno">Datum vystavení</label>';
				echo '<input type="date" size="10" value="'.date('Y-m-d', time()).'" aria-required="true" name="fakturace_vystaveno" id="fakturace_vystaveno"><br>';
				echo '</div>';

				echo '<div class="form-field form-required">';
				echo '<label for="fakturace_splatnost">Datum splatnosti</label>';
				echo '<input type="date" size="10" value="'.date('Y-m-d', strtotime('+30 days',time())).'" aria-required="true" name="fakturace_splatnost" id="fakturace_splatnost"><br>';
				echo '</div>';

				

				echo '<input type="submit" class="button button-primary" value="Vytvořit fakturu">';
				echo '</div>';
				echo '</div>';
				echo '</div><!-- /col-left -->';

				echo '<div id="col-right">';
				echo '<div class="col-wrap">';

				echo '<h2>Položky faktury</h2>';

				echo '<table class="wp-list-table widefat fixed striped posts">';

				echo '<thead>';
				echo '<tr>';
				echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo položky</th>';
				echo '<th scope="col" id="" class="manage-column column-primary">Položka</th>';
				echo '<th scope="col" id="" class="manage-column column-primary column-date">Částka</th>';
				echo '</tr>';
				echo '</thead>';

				echo '<tbody id="the-list">';

				for ($i=0; $i < 10; $i++) { 
					echo '<tr>';
					echo '<td><input type="number" name="faktura_cislo_polozky[]"></td>';
					echo '<td><input type="text" name="faktura_polozka[]"></td>';
					echo '<td><input type="number" name="faktura_castka[]"></td>';
					echo '</tr>';
				}

				echo '</tbody>';

				echo '<tfoot>';
				echo '<tr>';
				echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo položky</th>';
				echo '<th scope="col" id="" class="manage-column column-primary">Položka</th>';
				echo '<th scope="col" id="" class="manage-column column-primary column-date">Částka</th>';
				echo '</tr>';
				echo '</tfoot>';

				echo '</table>';

				echo '</div>';
				echo '</div><!-- /col-right -->';
				echo '</div>';


				echo '</form>';
			}
		
// Editace faktury

		} else if( !empty( $_GET['action']) && $_GET['action'] == 'update' ) {

			$post_id = $_GET['id'];

			if(!empty($_POST)) {
				if ( !empty( $post_id ) ) {

					foreach ($_POST as $key => $value) {

						if( $value != array() ) {
							update_post_meta( $post_id, $key, $value );
						} else {
							$value = serialize( $value );
							update_post_meta( $post_id, $key, $value );
						}

					}
					wp_redirect( admin_url('admin.php?page=fakturace&message=updated&action=update&id=' . $post_id) );
				}
				wp_die();
			} 

			echo '<h1 class="wp-heading-inline">Upravit fakturu č.: '.get_post_meta( $post_id, 'fakturace_cislo', true ).'</h1>';
			echo '<hr class="wp-header-end">';

			if( !empty($_GET['message']) && $_GET['message'] == 'created' ) {
				echo '<div id="message" class="updated notice notice-success"><p>Faktura byla vytvořena.</p></div>';
			}

			if( !empty($_GET['message']) && $_GET['message'] == 'updated' ) {
				echo '<div id="message" class="updated notice notice-success"><p>Faktura byla uspěšně upravena.</p></div>';
			}

			echo '<br>';

			echo '<form method="POST" class="validate invoice">';
			echo '<div id="col-container" class="wp-clearfix">';
			echo '<div id="col-left">';
			echo '<div class="col-wrap">';

			echo '<div class="form-wrap">';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_cislo">Číslo faktury</label>';
			echo '<input type="number" size="10" aria-required="true" value="'.get_post_meta( $post_id, 'fakturace_cislo', true ).'" name="fakturace_cislo" id="fakturace_cislo" ><br>';
			echo '<p>Číslo faktury ponechte prázdné, bude automaticky přiděleno po vytvoření faktury.</p>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="objednavka_cislo">Číslo objednávky</label>';
			echo '<input type="number" size="10" aria-required="true" value="'.get_post_meta( $post_id, 'objednavka_cislo', true ).'" name="objednavka_cislo" id="objednavka_cislo"><br>';
			echo '<p>Zde zadejte číslo objednávky tábora. Systém pomocí tohoto čísla páruje objednávky s fakturou.</p>';
			echo '</div>';

			echo '<h2>Dodavatel</h2>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_dodavatel">Dodavatel</label>';
			$args = array(
				'numberposts' => 20,
				'post_type'   => 'faktury-spolecnosti',
				'order'       => 'ASC',
				'orderby'     => 'date'
			);

			echo '<select name="fakturace_dodavatel">';
			$spolecnosti = get_posts( $args );
			$dodavatel = get_post_meta( $post_id, 'fakturace_dodavatel', true );
			foreach($spolecnosti as $spolecnost) {

				if( $spolecnost->ID == $dodavatel ) {
					echo '<option selected value="'.$spolecnost->ID.'">'.$spolecnost->post_title.'</option>';
				} else {
					echo '<option value="'.$spolecnost->ID.'">'.$spolecnost->post_title.'</option>';
				}

			}

			echo '</select>';
			echo '<p>Vyberte dodavatele, do faktury se po-té načtou uložené fakturační údaje. Podle zvoleného dodavatele bude dokladu přiděleno také číslo.</p>';
			echo '</div>';

			echo '<h2>Odběratel</h2>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_odberatel_nazev">Název společnosti</label>';
			echo '<input type="text" size="10" aria-required="true" value="'.get_post_meta( $post_id, 'fakturace_odberatel_nazev', true ).'" name="fakturace_odberatel_nazev" id="fakturace_odberatel_nazev"><br>';
			echo '<p>Název se bude v této podobě zobrazovat na faktuře v části odběratel.</p>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_odberatel_ulice">Ulice, č.p.</label>';
			echo '<input type="text" size="10" aria-required="true" value="'.get_post_meta( $post_id, 'fakturace_odberatel_ulice', true ).'" name="fakturace_odberatel_ulice" id="fakturace_odberatel_ulice"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_odberatel_mesto">PSČ, Město</label>';
			echo '<input type="text" size="10" aria-required="true" value="'.get_post_meta( $post_id, 'fakturace_odberatel_mesto', true ).'" name="fakturace_odberatel_mesto" id="fakturace_odberatel_mesto"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_odberatel_ico">IČ</label>';
			echo '<input type="text" size="10" aria-required="true" value="'.get_post_meta( $post_id, 'fakturace_odberatel_ico', true ).'" name="fakturace_odberatel_ico" id="fakturace_odberatel_ico"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_odberatel_dic">DIČ</label>';
			echo '<input type="text" size="10" aria-required="true" value="'.get_post_meta( $post_id, 'fakturace_odberatel_dic', true ).'" name="fakturace_odberatel_dic" id="fakturace_odberatel_dic"><br>';
			echo '</div>';

			echo '<h2>Platební údaje</h2>';



			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_variabilni_symbol">Variabilní symbol</label>';
			echo '<input type="number" size="10" aria-required="true" value="'.get_post_meta( $post_id, 'fakturace_variabilni_symbol', true ).'" name="fakturace_variabilni_symbol" id="fakturace_variabilni_symbol"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="zprava_pro_prijemce">Zpráva pro příjemce</label>';
			echo '<input type="text" size="10" aria-required="true" value="'.get_post_meta( $post_id, 'zprava_pro_prijemce', true ).'" name="zprava_pro_prijemce" id="zprava_pro_prijemce"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_vystaveno">Datum vystavení</label>';
			// date('Y-m-d', stringtotime(get_post_meta( $post_id, 'fakturace_variabilni_symbol', true )))
			echo '<input type="date" size="10" value="'.date('Y-m-d', strtotime(get_post_meta( $post_id, 'fakturace_vystaveno', true ))).'" aria-required="true" name="fakturace_vystaveno" id="fakturace_vystaveno"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_splatnost">Datum splatnosti</label>';
			echo '<input type="date" size="10" value="'.date('Y-m-d', strtotime(get_post_meta( $post_id, 'fakturace_splatnost', true ))).'" aria-required="true" name="fakturace_splatnost" id="fakturace_splatnost"><br>';
			echo '</div>';

			

			echo '<input type="submit" class="button button-primary" value="Upravit fakturu">';
			echo ' <a target="_blank" href="'.admin_url('admin.php?invoice=' . $post_id) .'" class="button")">Export PDF</a>';
			echo ' <a target="_blank" href="'.admin_url('admin.php?isdoc=' . $post_id) .'" class="button")">Export ISDOC</a>';
			echo ' <a href="'.admin_url('admin.php?page=fakturace&action=delete&id=' . $post_id) .'" class="button button-link-delete" onclick="return confirm(\'Opravdu chcete fakturu smazat?\')">Smazat fakturu</a>';

			echo '</div>';
			echo '</div>';
			echo '</div><!-- /col-left -->';

			echo '<div id="col-right">';
			echo '<div class="col-wrap">';

			echo '<h2>Položky faktury</h2>';

			echo '<table class="wp-list-table widefat fixed striped posts">';

			echo '<thead>';
			echo '<tr>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo položky</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Položka</th>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Částka</th>';
			echo '</tr>';
			echo '</thead>';

			echo '<tbody id="the-list">';

			$faktura_cislo_polozky = get_post_meta( $post_id, 'faktura_cislo_polozky', true );
			$faktura_polozka = get_post_meta( $post_id, 'faktura_polozka', true );
			$faktura_castka = get_post_meta( $post_id, 'faktura_castka', true );

			for ($i=0; $i < 10; $i++) { 
				echo '<tr>';
				echo '<td><input type="number" value="'.$faktura_cislo_polozky[$i].'" name="faktura_cislo_polozky[]"></td>';
				echo '<td><input type="text" value="'.$faktura_polozka[$i].'" name="faktura_polozka[]"></td>';
				echo '<td><input type="number" value="'.$faktura_castka[$i].'" name="faktura_castka[]"></td>';
				echo '</tr>';
			}

			echo '</tbody>';

			echo '<tfoot>';
			echo '<tr>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo položky</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Položka</th>';
			echo '<th scope="col" id="" class="manage-column column-primary column-date">Částka</th>';
			echo '</tr>';
			echo '</tfoot>';

			echo '</table>';

			echo '</div>';
			echo '</div><!-- /col-right -->';
			echo '</div>';


			echo '</form>';
		}


		echo '</div>';

	}

	function fakturace_spolecnosti_content() {

// Zobrazení seznamu společností

		echo '<div class="wrap">';

		if( empty( $_GET['action'] ) ) {
		echo '<h1 class="wp-heading-inline">Společnosti</h1>';
		echo '<a href="' . admin_url('admin.php?page=fakturace-spolecnosti&action=new') . '" class="page-title-action">Vytvořit společnost</a>';
		echo '<hr class="wp-header-end">';
		if( !empty($_GET['message']) && $_GET['message'] == 'deleted' ) {
				echo '<div id="message" class="error notice notice-success"><p>Společnost byla smazána.</p></div>';
		}
		echo '<br>';
		$args = array(
			'numberposts' => 20,
			'post_type'   => 'faktury-spolecnosti',
			'order'       => 'ASC',
			'orderby'     => 'date'
		);

		echo '<table class="wp-list-table widefat fixed striped posts">';

		echo '<thead>';
		echo '<tr>';
		echo '<th scope="col" id="" class="manage-column column-primary">Název společnosti</th>';
		echo '<th scope="col" id="" class="manage-column column-primary column-date">IČ</th>';
		echo '<th scope="col" id="" class="manage-column column-primary column-date">DIČ</th>';
		echo '<th scope="col" id="" class="manage-column column-primary">Sídlo</th>';
		echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslo účtu</th>';
		echo '<th scope="col" id="" class="manage-column column-primary column-date">Číslování</th>';
		echo '<th scope="col" id="" class="manage-column column-primary column-date">Akce</th>';
		echo '</tr>';
		echo '</thead>';

		echo '<tbody id="the-list">';

		$spolecnosti = get_posts( $args );
		foreach($spolecnosti as $spolecnost) {
			
			$post_id = $spolecnost->ID;	
			echo '<tr>';
			echo '<td><a href="'.admin_url('admin.php?page=fakturace-spolecnosti&action=update&id=' . $post_id).'">' . $spolecnost->post_title . '</a></td>';
			echo '<td>' . get_post_meta( $post_id, 'fakturace_spolecnost_ico', true ) . '</a></td>';
			echo '<td>' . get_post_meta( $post_id, 'fakturace_spolecnost_dic', true ) . '</a></td>';
			echo '<td>' . get_post_meta( $post_id, 'fakturace_spolecnost_ulice', true ) . ', ' . get_post_meta( $post_id, 'fakturace_spolecnost_mesto', true ) . '</a></td>';
			echo '<td>' . get_post_meta( $post_id, 'fakturace_spolecnost_bankovni_ucet', true ) . '</a></td>';
			echo '<td>' . get_post_meta( $post_id, 'fakturace_spolecnost_cislovani', true ) . '</a></td>';
			echo '<td><a href="'.admin_url('admin.php?page=fakturace-spolecnosti&action=update&id=' . $post_id).'">Upravit společnost</a></td>';
			echo '</tr>';

		}

		echo '</tbody>';

		echo '<tfoot>';
		echo '<tr>';
		echo '<th scope="col" id="" class="manage-column column-primary">Název společnosti</th>';
		echo '<th scope="col" id="" class="manage-column column-primary">IČ</th>';
		echo '<th scope="col" id="" class="manage-column column-primary">DIČ</th>';
		echo '<th scope="col" id="" class="manage-column column-primary">Sídlo</th>';
		echo '<th scope="col" id="" class="manage-column column-primary">Číslo účtu</th>';
		echo '<th scope="col" id="" class="manage-column column-primary">Akce</th>';
		echo '</tr>';
		echo '</tfoot>';

		echo '</table>';

// Vytvoření nové společnosti

		} else if( !empty( $_GET['action']) && $_GET['action'] == 'new' ) {
			
			if(!empty($_POST)) {

				$post_id = wp_insert_post( array (
					'post_type' => 'faktury-spolecnosti',
					'post_title' => $_POST['fakturace_spolecnost_nazev'],
					'post_status' => 'publish',
					'comment_status' => 'closed',
					'ping_status' => 'closed'
				) );

				if ( !empty( $post_id ) ) {
					foreach ($_POST as $key => $value) {
						if($key == 'fakturace_spolecnost_dic' && empty($value) ) {
							$value = "Nejsme plátci DPH!";
						} 
						update_post_meta( $post_id, $key, $value );
					}
					wp_redirect( admin_url('admin.php?page=fakturace-spolecnosti&message=created&action=update&id=' . $post_id) );
				}
				wp_die();
			} 

			echo '<h1 class="wp-heading-inline">Vytvořit novou společnost</h1>';
			echo '<hr class="wp-header-end">';
			echo '<br>';
			echo '<div id="col-container" class="wp-clearfix">';
			echo '<div id="col-left">';
			echo '<div class="col-wrap">';

			echo '<div class="form-wrap">';
			echo '<form method="POST" class="validate">';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_nazev">Název společnosti</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_nazev" id="fakturace_spolecnost_nazev"><br>';
			echo '<p>Název se bude v této podobě zobrazovat na faktuře v části dodavatel.</p>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_ulice">Ulice č.p.</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_ulice" id="fakturace_spolecnost_ulice"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_mesto">PSČ, město</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_mesto" id="fakturace_spolecnost_mesto"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_ico">IČ</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_ico" id="fakturace_spolecnost_ico"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_dic">DIČ</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_dic" id="fakturace_spolecnost_dic"><br>';
			echo '<p>Není-li subjekt plátce DPH, zapište do pole "<strong>Nejsme plátci DPH!</strong>, nebo ponechte toto pole prázdné."</p>';
			echo '</div>';


			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_bankovni_ucet">Číslo bankovního účtu včetně kódu banky</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_bankovni_ucet" id="fakturace_spolecnost_bankovni_ucet"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_iban">IBAN</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_iban" id="fakturace_spolecnost_iban"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_swift">SWIFT / BIC</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_swift" id="fakturace_spolecnost_swift"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_cislovani">Číslování faktur</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_cislovani" id="fakturace_spolecnost_cislovani"><br>';
			echo '<p>Zadejte číslo faktury, od kterého chcete pokračovat v číslování dokladů. Při vystavení nové faktury bude tato hodnota vždy navýšena o 1. Doporučený formát je 20230001, kde první čtyři číslice reprezentují rok vystavení faktury. Na začátku nového roku je nutné toto číslo upravit např. na 20240001.<br><strong>Důležité upozornění: </strong>Nesprávné nastavení číslování může mít fatální vliv na fakturace."</p>';
			echo '</div>';

			echo '<input type="submit" class="button button-primary" value="Vytvořit společnost">';
			echo '</form>';


			echo '</div>';
			echo '</div>';
			echo '</div><!-- /col-left -->';

			echo '<div id="col-right">';
			echo '<div class="col-wrap">';

			echo '<h2>Správa závislostí</h2>';
			echo '<p>Vyberte termíny u kterých chcete tuto společnost nastavit jako dodavatele. <strong>Upozornění!</strong> Pro jeden termín je možné přiřadit pouze jednu společnost!</p>';

			$terms = get_terms([
				'taxonomy' => 'pa_terminy',
				'hide_empty' => false,
			]);

			echo '<table class="wp-list-table widefat fixed striped posts">';

			echo '<thead>';
			echo '<tr>';
			echo '<td id="cb" class="manage-column column-cb check-column"></td>';
			echo '<th scope="col" id="" class="manage-column column-primary">Dostupné termíny</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Přiřazená společnost</th>';
			echo '</tr>';
			echo '</thead>';

			echo '<tbody id="the-list">';

			foreach($terms as $term) {
				$term_id = $term->term_id;
				$name = $term->name;

				$verify_loop = get_posts( array(
					'post_type'  => 'faktury-spolecnosti',
					'meta_key'   => 'terminy',
					'meta_value' => $term_id
				) );
				
				$terminy = get_post_meta( $post_id, 'terminy' );
				
				echo '<tr>';
				echo '<th scope="row" class="check-column">';
				echo '<label class="screen-reader-text" for="cb-select-34620">Zvolit: '.$name.'</label>';
				if( !empty($verify_loop) ) {
					echo '<input id="cb-select-'.$term_id.'" disabled type="checkbox" name="terminy[]" value="'.$term_id.'">';
				} else {
					echo '<input id="cb-select-'.$term_id.'" type="checkbox" name="terminy[]" value="'.$term_id.'">';
				}
				echo '<td>' . $name . '</td>';
				echo '<td>'; 
				if( !empty($verify_loop) ) {

					echo '<a href="' . admin_url('admin.php?page=fakturace-spolecnosti&action=update&id=' . $verify_loop[0]->ID) . '">' . $verify_loop[0]->post_title . '</a>';

				}
				echo '</td>';
				echo '</tr>';
			}

			echo '</tbody>';

			echo '<tfoot>';
			echo '<tr>';
			echo '<td id="cb" class="manage-column column-cb check-column"></td>';
			echo '<th scope="col" id="" class="manage-column column-primary">Dostupné termíny</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Přiřazená společnost</th>';
			echo '</tr>';
			echo '</tfoot>';

			echo '</table>';

			echo '</div>';
			echo '</div><!-- /col-right -->';
			echo '</div>';
		
// Editace společnosti

		} else if( !empty( $_GET['action'] ) && !empty( $_GET['id'] ) && $_GET['action'] == 'update' ) {

			$post_id = $_GET['id'];
			if(!empty($_POST)) {
				if ( !empty( $post_id ) ) {

					delete_post_meta( $post_id, 'terminy');

					foreach ($_POST as $key => $value) {

						if($key == 'fakturace_spolecnost_dic' && empty($value) ) {
							$value = "Nejsme plátci DPH!";
						} 

						if($key != 'terminy') {
							update_post_meta( $post_id, $key, $value );
						} else {

							foreach( $value as $termin ) {
								add_post_meta( $post_id, 'terminy', $termin );
							}

						}
					}
					wp_redirect( admin_url('admin.php?page=fakturace-spolecnosti&message=updated&action=update&id=' . $post_id) );
				}
				wp_die();
			} 

			echo '<h1 class="wp-heading-inline">Upravit společnost: '.get_post_meta( $post_id, 'fakturace_spolecnost_nazev', true ).'</h1>';
			echo '<hr class="wp-header-end">';
			
			if( !empty($_GET['message']) && $_GET['message'] == 'created' ) {
				echo '<div id="message" class="updated notice notice-success"><p>Společnost byla vytvořena.</p></div>';
			}	

			if( !empty($_GET['message']) && $_GET['message'] == 'updated' ) {
				echo '<div id="message" class="updated notice notice-success"><p>Společnost byla aktualizována.</p></div>';
			}

			echo '<br>';
			echo '<form method="POST" class="validate">';
			echo '<div id="col-container" class="wp-clearfix">';
			echo '<div id="col-left">';
			echo '<div class="col-wrap">';

			echo '<div class="form-wrap">';
			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_nazev">Název společnosti</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_nazev" id="fakturace_spolecnost_nazev" value="'.get_post_meta( $post_id, 'fakturace_spolecnost_nazev', true ).'"><br>';
			echo '<p>Název se bude v této podobě zobrazovat na faktuře v části dodavatel.</p>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_ulice">Ulice č.p.</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_ulice" id="fakturace_spolecnost_ulice" value="'.get_post_meta( $post_id, 'fakturace_spolecnost_ulice', true ).'"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_mesto">PSČ, město</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_mesto" id="fakturace_spolecnost_mesto" value="'.get_post_meta( $post_id, 'fakturace_spolecnost_mesto', true ).'"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_ico">IČ</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_ico" id="fakturace_spolecnost_ico" value="'.get_post_meta( $post_id, 'fakturace_spolecnost_ico', true ).'"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_dic">DIČ</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_dic" id="fakturace_spolecnost_dic" value="'.get_post_meta( $post_id, 'fakturace_spolecnost_dic', true ).'"><br>';
			echo '<p>Není-li subjekt plátce DPH, zapište do pole "<strong>Nejsme plátci DPH!</strong>, nebo ponechte toto pole prázdné."</p>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_bankovni_ucet">Číslo bankovního účtu včetně kódu banky</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_bankovni_ucet" id="fakturace_spolecnost_bankovni_ucet" value="'.get_post_meta( $post_id, 'fakturace_spolecnost_bankovni_ucet', true ).'"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_iban">IBAN</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_iban" id="fakturace_spolecnost_iban" value="'.get_post_meta( $post_id, 'fakturace_spolecnost_iban', true ).'"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_swift">SWIFT / BIC</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_swift" id="fakturace_spolecnost_swift" value="'.get_post_meta( $post_id, 'fakturace_spolecnost_swift', true ).'"><br>';
			echo '</div>';

			echo '<div class="form-field form-required">';
			echo '<label for="fakturace_spolecnost_cislovani">Číslování faktur</label>';
			echo '<input type="text" size="40" aria-required="true" name="fakturace_spolecnost_cislovani" id="fakturace_spolecnost_cislovani" value="'.get_post_meta( $post_id, 'fakturace_spolecnost_cislovani', true ).'"><br>';
			echo '<p>Zadejte číslo faktury, od kterého chcete pokračovat v číslování dokladů. Při vystavení nové faktury bude tato hodnota vždy navýšena o 1. Doporučený formát je 20230001, kde první čtyři číslice reprezentují rok vystavení faktury. Na začátku nového roku je nutné toto číslo upravit např. na 20240001.<br><strong>Důležité upozornění: </strong>Nesprávné nastavení číslování může mít fatální vliv na fakturace."</p>';
			echo '</div>';

			echo '<input type="submit" class="button button-primary" value="Upravit společnost">';
			echo ' <a href="'.admin_url('admin.php?page=fakturace-spolecnosti&action=delete&id=' . $post_id) .'" class="button button-link-delete" onclick="return confirm(\'Opravdu chcete společnost smazat?\')">Smazat společnost</a>';

			echo '</div>';
			echo '</div>';
			echo '</div><!-- /col-left -->';

			echo '<div id="col-right">';
			echo '<div class="col-wrap">';

			echo '<h2>Správa závislostí</h2>';
			echo '<p>Vyberte termíny u kterých chcete tuto společnost nastavit jako dodavatele. <strong>Upozornění!</strong> Pro jeden termín je možné přiřadit pouze jednu společnost!</p>';

			echo '<table class="wp-list-table widefat fixed striped posts">';

			echo '<thead>';
			echo '<tr>';
			echo '<td id="cb" class="manage-column column-cb check-column"></td>';
			echo '<th scope="col" id="" class="manage-column column-primary">Dostupné termíny</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Přiřazená společnost</th>';
			echo '</tr>';
			echo '</thead>';

			echo '<tbody id="the-list">';

			$terms = get_terms([
				'taxonomy' => 'pa_terminy',
				'hide_empty' => false,
			]);

			foreach($terms as $term) {

				$term_id = $term->term_id;
				$name = $term->name;
				echo '<tr>';
				echo '<th scope="row" class="check-column">';
				echo '<label class="screen-reader-text" for="cb-select-34620">Zvolit: '.$name.'</label>';

				$verify_loop = get_posts( array(
					'post_type'  => 'faktury-spolecnosti',
					'meta_key'   => 'terminy',
					'meta_value' => $term_id
				) );
				
				$terminy = get_post_meta( $post_id, 'terminy' );

				if( in_array($term_id, $terminy) ) {
					echo '<input id="cb-select-'.$term_id.'" checked="checked" type="checkbox" name="terminy[]" value="'.$term_id.'">';
				} else {
					if( !empty($verify_loop) ) {
						echo '<input id="cb-select-'.$term_id.'" disabled type="checkbox" name="terminy[]" value="'.$term_id.'">';
					} else {
						echo '<input id="cb-select-'.$term_id.'" type="checkbox" name="terminy[]" value="'.$term_id.'">';
					}
				}

				echo '<td>' . $name . '</td>';
				echo '<td>'; 
				if( !empty($verify_loop) ) {

					echo '<a href="' . admin_url('admin.php?page=fakturace-spolecnosti&action=update&id=' . $verify_loop[0]->ID) . '">' . $verify_loop[0]->post_title . '</a>';

				}
				echo '</td>';
				echo '</tr>';
			}

			echo '</tbody>';

			echo '<tfoot>';
			echo '<tr>';
			echo '<td id="cb" class="manage-column column-cb check-column"></td>';
			echo '<th scope="col" id="" class="manage-column column-primary">Dostupné termíny</th>';
			echo '<th scope="col" id="" class="manage-column column-primary">Přiřazená společnost</th>';
			echo '</tr>';
			echo '</tfoot>';

			echo '</table>';

			echo '</div>';
			echo '</div><!-- /col-right -->';
			echo '</div>';

			echo '</form>';

		} else if( !empty( $_GET['action'] ) && !empty( $_GET['id'] ) && $_GET['action'] == 'delete' ) {

			wp_delete_post($_GET['id']);
			wp_redirect( admin_url('admin.php?page=fakturace-spolecnosti&message=deleted') );
			wp_die();

		}

	}

	function fakturace_nastaveni_content() {

		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">Nastavení</h1>';
		echo '</div>';

	}

	add_action('admin_head', 'my_custom_fonts');

	function my_custom_fonts() {
	  echo '<style>
	    .invoice table input {
	      width: 100%;
	    } 

	    .iactions .button {
	    	width: 32px;
	    }

	    .iactions .button.dashicons-trash {
	    	color: #C10000;
	    }

	    .toplevel_page_fakturace  .wp-filter .button {
	    	height: 32px;
    		line-height: 29px;
	    }

	    .toplevel_page_fakturace  .tablenav .displaying-num {
	    	padding: 0 10px;
	    }
	  </style>';
	}

// Přidání metaboxu do objednávek

	add_action( 'add_meta_boxes', 'of_add_meta_boxes' );
	if ( ! function_exists( 'of_add_meta_boxes' ) ) {
	    function of_add_meta_boxes() {
	        add_meta_box( 'of_other_fields', __('Fakturace','woocommerce'), 'mv_add_invoice_button', 'shop_order', 'side', 'core' );
	    }
	}

	if ( ! function_exists( 'mv_add_invoice_button' ) ) {
		function mv_add_invoice_button() {
			global $post;
			echo '<a href="'.admin_url('admin.php?page=fakturace&action=new&order=').$post->ID.'" class="button button-primary">Vytvořit fakturu</a>';
		}
	}


// Generátor faktury

	if( !empty( $_GET['invoice'] ) ) {

		if( ! current_user_can( 'edit_posts' ) ) {
			wp_die('403 - Přístup zamítnut! Zkuste se přihlásit do systému znovu.');
		}

		$faktura_id = $_GET['invoice'];

		$faktura = get_post_meta( $faktura_id );

		$spolecnost = $faktura['fakturace_dodavatel'][0];
		$spolecnost = get_post_meta( $spolecnost );

		$invoice_nr = $faktura['fakturace_cislo'][0];

		$faktura_cislo_polozky = unserialize($faktura['faktura_cislo_polozky'][0]);
		$faktura_polozka = unserialize($faktura['faktura_polozka'][0]);
		$faktura_castka = unserialize($faktura['faktura_castka'][0]);

		$z = 0;

		foreach($faktura_castka as $key => $value) {
			$z = $z + (int)$value;
		}

		$invoice_ammount = $z;

		$qr_code = null;
		$qr_code .= 'SPD*1.0';
		$qr_code .= '*ACC:' . $spolecnost['fakturace_spolecnost_iban'][0];
		$qr_code .= '*AM:' . $invoice_ammount;
		$qr_code .= '*CC:CZK';
		$qr_code .= '*MSG:' . rawurlencode($faktura['zprava_pro_prijemce'][0]);
		$qr_code .= '*X-VS:' . $faktura['fakturace_variabilni_symbol'][0];
		
		define( 'FPDF_FONTPATH', dirname(__FILE__) . '/fpdf/font');
		require(dirname(__FILE__) . '/fpdf/fpdf.php');

		$pdf = new FPDF('P','mm','A4');
		$pdf->SetMargins(0, 0, 0, 0);
		$pdf->AddFont('OpenSans','','opensans.php');
		$pdf->AddFont('OpenSans','B','opensansb.php');
		$pdf->AddPage();
		$pdf->SetFont('OpenSans','',10);
		$pdf->SetLineWidth(0.1);

		$pdf->Image('https://www.fajntabory.cz/wp-content/uploads/2017/03/logo_na_web.png', 10, 12, 39, 15 );

		// Hlavička 
		$pdf->SetFont('OpenSans', 'B', 16);

		if( $invoice_ammount < 0 ) {
			$pdf->text(105, 16, iconv("UTF-8", "WINDOWS-1250", "Storno faktura číslo: " . $invoice_nr ) );
		} else {
			$pdf->text(105, 16, iconv("UTF-8", "WINDOWS-1250", "Faktura číslo: " . $invoice_nr ) );
		}

		$pdf->SetFont('OpenSans', '', 9);
		$pdf->setTextColor(100,100,100);
		$pdf->text(105, 22, iconv("UTF-8", "WINDOWS-1250", "DAŇOVÝ DOKLAD" ) );
		$pdf->setTextColor(0,0,0);

		$pdf->SetFont('OpenSans', '', 9);
		$pdf->text(10, 38, iconv("UTF-8", "WINDOWS-1250", "DODAVATEL:" ) );
		$pdf->SetFont('OpenSans', 'B', 12);
		$pdf->text(10, 45, iconv("UTF-8", "WINDOWS-1250", $spolecnost['fakturace_spolecnost_nazev'][0] ) );
		$pdf->SetFont('OpenSans', '', 9);
		$pdf->text(10, 52, iconv("UTF-8", "WINDOWS-1250", $spolecnost['fakturace_spolecnost_ulice'][0] ) );
		$pdf->text(10, 57, iconv("UTF-8", "WINDOWS-1250", $spolecnost['fakturace_spolecnost_mesto'][0] ) );
		$pdf->text(10, 62, iconv("UTF-8", "WINDOWS-1250", "Česká republika" ) );
		$pdf->text(60, 57, iconv("UTF-8", "WINDOWS-1250", "IČ: ".$spolecnost['fakturace_spolecnost_ico'][0] ) );
		$pdf->text(60, 62, iconv("UTF-8", "WINDOWS-1250", "DIČ: ".$spolecnost['fakturace_spolecnost_dic'][0] ) );

		$pdf->setFillColor(240,240,240);
		$pdf->rect(105,30,105,40,'F');


		$pdf->text(115, 38, iconv("UTF-8", "WINDOWS-1250", "ODBĚRATEL:" ) );
		$pdf->SetFont('OpenSans', 'B', 9);
		$pdf->text(115, 45, iconv("UTF-8", "WINDOWS-1250", $faktura['fakturace_odberatel_nazev'][0] ) );
		$pdf->SetFont('OpenSans', '', 9);
		$pdf->text(115, 52, iconv("UTF-8", "WINDOWS-1250", $faktura['fakturace_odberatel_ulice'][0] ) );
		$pdf->text(115, 57, iconv("UTF-8", "WINDOWS-1250", $faktura['fakturace_odberatel_mesto'][0] ) );
		$pdf->text(115, 62, iconv("UTF-8", "WINDOWS-1250", "Česká republika" ) );
		$pdf->text(165, 57, iconv("UTF-8", "WINDOWS-1250", "IČ: ".$faktura['fakturace_odberatel_ico'][0] ) );
		$pdf->text(165, 62, iconv("UTF-8", "WINDOWS-1250", "DIČ: ".$faktura['fakturace_odberatel_dic'][0] ) );

		$pdf->rect(0,75,210,45,'F');

		$pdf->SetFont('OpenSans', '', 9);
		$pdf->text(10, 86, iconv("UTF-8", "WINDOWS-1250", "Datum vystavení:" ) );
		$pdf->text(10, 91, iconv("UTF-8", "WINDOWS-1250", "Datum splatnosti:" ) );
		$pdf->text(10, 96, iconv("UTF-8", "WINDOWS-1250", "Datum zdan. plnění:" ) );

		$pdf->setFont('OpenSans', 'B', 11);
		$pdf->text(10, 112, iconv("UTF-8", "WINDOWS-1250", "DPH – zvláštní režim 0 %" ) );

		$pdf->SetFont('OpenSans', 'B', 9);
		$pdf->text(45, 86, iconv("UTF-8", "WINDOWS-1250", date('d.m.Y', strtotime($faktura['fakturace_vystaveno'][0]) ) ) );
		$pdf->text(45, 91, iconv("UTF-8", "WINDOWS-1250", date('d.m.Y', strtotime($faktura['fakturace_splatnost'][0]) ) ) );
		$pdf->text(45, 96, iconv("UTF-8", "WINDOWS-1250", date('d.m.Y', strtotime($faktura['fakturace_vystaveno'][0]) ) ) );

		$pdf->SetFont('OpenSans', '', 9);
		$pdf->text(80, 86, iconv("UTF-8", "WINDOWS-1250", "Způsob platby:" ) );
		$pdf->text(80, 91, iconv("UTF-8", "WINDOWS-1250", "Číslo účtu:" ) );
		$pdf->text(80, 96, iconv("UTF-8", "WINDOWS-1250", "Variabilní symbol:" ) );
		$pdf->text(80, 101, iconv("UTF-8", "WINDOWS-1250", "Zpráva pro příjemce:" ) );
		$pdf->text(80, 112, iconv("UTF-8", "WINDOWS-1250", "Částka k úhradě:" ) );
		
		$pdf->SetFont('OpenSans', 'B', 9);
		$pdf->text(115, 86, iconv("UTF-8", "WINDOWS-1250", "Bankovním převodem" ) );
		$pdf->text(115, 91, iconv("UTF-8", "WINDOWS-1250", $spolecnost['fakturace_spolecnost_bankovni_ucet'][0] ) );
		$pdf->text(115, 96, iconv("UTF-8", "WINDOWS-1250", $faktura['fakturace_variabilni_symbol'][0] ) );
		$pdf->text(115, 101, iconv("UTF-8", "WINDOWS-1250", $faktura['zprava_pro_prijemce'][0] ) );
		$pdf->SetFont('OpenSans', 'B', 16);
		$pdf->text(115, 112, iconv("UTF-8", "WINDOWS-1250", number_format($invoice_ammount, 2, ',', ' ') . " Kč" ) );

		$pdf->setTextColor(0,0,0);

		if( $invoice_ammount > 0 ) {
			$pdf->Image( get_template_directory_uri() . '/functions/qr.php?code=' . $qr_code, 165, 80, 35, 35, 'png');
		}

		$pdf->setFont('OpenSans', 'B', 9);
		$pdf->text(10, 130, iconv("UTF-8", "WINDOWS-1250", "Fakturujeme Vám pobyt na letním táboře pro dítě Vašeho zaměstnance:" ) );

		$pdf->setXY(10, 140);
		$pdf->setDrawColor(150,150,150);
		$pdf->setFont('OpenSans', 'B', 9);
		$pdf->cell(15, 8, iconv("UTF-8", "WINDOWS-1250", "Číslo:" ), 'B', 0);
		$pdf->cell(145, 8, iconv("UTF-8", "WINDOWS-1250", "Popis:" ), 'B', 0);
		$pdf->cell(30, 8, iconv("UTF-8", "WINDOWS-1250", "Částka:" ), 'B', 1);

		$pdf->SetFont('OpenSans', '', 9);

		// Tady nějaký loop který tam bude dávat položky

		$faktura_cislo_polozky;
		$faktura_polozka;
		$faktura_castka;

		for ($i=0; $i < 10; $i++) { 
			if( empty( $faktura_cislo_polozky[$i] ) && empty( $faktura_polozka[$i] ) && empty( $faktura_castka[$i] ) ) {

			} else {
				$pdf->setX(10);
				$pdf->cell(15, 7, iconv("UTF-8", "WINDOWS-1250", $faktura_cislo_polozky[$i]), 'B', 0);
				$pdf->cell(145, 7, iconv("UTF-8", "WINDOWS-1250", $faktura_polozka[$i] ), 'B', 0);
				if( ! empty( $faktura_castka[$i] ) ) {
					$pdf->cell(30, 7, iconv("UTF-8", "WINDOWS-1250", number_format((int)$faktura_castka[$i], 2, ',', ' ') . " Kč" ), 'B', 1);
				} else {
					$pdf->cell(30, 7, iconv("UTF-8", "WINDOWS-1250", "" ), 'B', 1);
				}
			}
		}

		// Tady součet

		$pdf->setX(10);
		$pdf->setFont('OpenSans', 'B', 9);
		$pdf->setFillColor(20,181,225);
		$pdf->setTextColor(255,255,255);

		$pdf->cell(160, 8, iconv("UTF-8", "WINDOWS-1250", "CENA CELKEM:" ), 0, 0, 'R', 1);
		$pdf->cell(30, 8, iconv("UTF-8", "WINDOWS-1250", number_format($invoice_ammount, 2, ',', ' ') . " Kč"), 0, 1, 'L', 1);

		// Důležité upozornění

		$pdf->setX(10);
		$pdf->setTextColor(0,0,0);
		$pdf->cell( 190, 6, '', 0, 1);
		$pdf->setX(10);
		$pdf->cell( 190, 6, iconv("UTF-8", "WINDOWS-1250", "Důležité upozornění pro Odběratele a klienta:" ), 0, 1, 'L', 0);

		$pdf->setX(10);
		$pdf->setFont('OpenSans', '', 9);
		$pdf->multiCell(190, 5, iconv("UTF-8", "WINDOWS-1250", "V případě, že se klient, respektive dítě klienta z jakéhokoliv důvodu na straně klienta nezúčastní sjednaného pobytu, bude Dodavatel Odběrateli účtovat smluvní Storno poplatek dle aktuálních VOP Dodavatele. Uhrazením ceny rekreace Odběratel přijímá Storno podmínky stanovené Dodavatelem dle jeho aktuálních VOP." ), 0, 'L');

		$pdf->Image( get_template_directory_uri() . '/functions/podpis.png', 160, 240, 40, 16, 'png');

		// Vygenerujeme fakturu
		$pdf->Output('I', 'Faktura_' . $invoice_nr . '.pdf');
		
	}

	function generateUuidV4()
{
    $data = random_bytes(16);

    // verze 4
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);

    // varianta RFC 4122
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return strtoupper(
        substr(bin2hex($data), 0, 8) . '-' .
        substr(bin2hex($data), 8, 4) . '-' .
        substr(bin2hex($data), 12, 4) . '-' .
        substr(bin2hex($data), 16, 4) . '-' .
        substr(bin2hex($data), 20, 12)
    );
}




	if( !empty( $_GET['isdoc'] ) ) {
		$id = $_GET['isdoc'];
		$xml = generateIsdoc($id);
		$nazevSouboru = "{$id}.isdoc";

		// echo $xml; die();

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $nazevSouboru . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . strlen($xml));

		echo $xml;
		exit;
	}

	
	function generateIsdoc($faktura_id) {
	    $faktura = get_post_meta($faktura_id);
	    $spolecnost_id = $faktura['fakturace_dodavatel'][0];
	    $spolecnost = get_post_meta($spolecnost_id);
	    var_dump($faktura);
	    var_dump($spolecnost);
	    exit;

	    function val($arr, $key) {
	        return isset($arr[$key][0]) ? trim($arr[$key][0]) : '';
	    }

	    function uuidV4() {
	        $data = random_bytes(16);
	        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
	        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
	        return strtoupper(
	            substr(bin2hex($data), 0, 8) . '-' .
	            substr(bin2hex($data), 8, 4) . '-' .
	            substr(bin2hex($data), 12, 4) . '-' .
	            substr(bin2hex($data), 16, 4) . '-' .
	            substr(bin2hex($data), 20, 12)
	        );
	    }

	    function parseCityPostal($input) {
		    $input = trim($input);

		    // Najdi PSČ (123 45 nebo 12345)
		    if (preg_match('/(\d{3})\s?(\d{2})/', $input, $m)) {
		        $psc = $m[1] . $m[2];

		        // Odstraní PSČ ze stringu
		        $city = preg_replace('/(\d{3})\s?(\d{2})/', '', $input);
		        $city = trim(str_replace(',', '', $city));

		        return [
		            'psc' => $psc,
		            'city' => trim($city)
		        ];
		    }

		    // Pokud PSČ nenalezeno
		    return [
		        'psc' => '',
		        'city' => $input
		    ];
		}

		function parseStreet($input) {
		    $input = trim($input);

		    // hledá poslední číslo s případným /číslem a písmenem
		    if (preg_match('/(.+?)\s+(\d+\/?\d*[A-Za-z]*)$/u', $input, $m)) {
		        return [
		            'street' => trim($m[1]),
		            'number' => trim($m[2])
		        ];
		    }

		    return [
		        'street' => $input,
		        'number' => ''
		    ];
		}



	    // === DATA ===
	    $cisloFaktury = val($faktura, "fakturace_cislo");
	    $vs = val($faktura, "fakturace_variabilni_symbol");
	    $datumVystaveni = val($faktura, "fakturace_vystaveno");
	    $datumSplatnosti = val($faktura, "fakturace_splatnost");

	    $nazevOdberatele = val($faktura, "fakturace_odberatel_nazev");
	    $uliceOdberatele = val($faktura, "fakturace_odberatel_ulice");
	    $mestoRaw = val($faktura, "fakturace_odberatel_mesto");
	    $icoOdberatele = val($faktura, "fakturace_odberatel_ico");
	    $dicOdberatele = val($faktura, "fakturace_odberatel_dic");

	    preg_match('/(\d{3})\s?(\d{2})\s+(.*)/', $mestoRaw, $m);
	    $pscOdberatele = $m[1] . $m[2] ?? '';
	    $mestoOdberatele = $m[3] ?? '';

	    $nazevDodavatele = val($spolecnost, "fakturace_spolecnost_nazev");
	    $uliceDodavatele = val($spolecnost, "fakturace_spolecnost_ulice");
	    $mestoSpRaw = val($spolecnost, "fakturace_spolecnost_mesto");
	    $icoDodavatele = val($spolecnost, "fakturace_spolecnost_ico");
	    $dicDodavatele = val($spolecnost, "fakturace_spolecnost_dic");
	    $iban = val($spolecnost, "fakturace_spolecnost_iban");

	    preg_match('/(\d{3})\s?(\d{2}).*?,\s*(.*)/', $mestoSpRaw, $m2);
	    $pscDodavatele = $m2[1] . $m2[2] ?? '';
	    $mestoDodavatele = $m2[3] ?? '';

	    list($cisloUctu, $kodBanky) = explode('/', val($spolecnost, "fakturace_spolecnost_bankovni_ucet"));

	    // === POLOŽKY ===
	    $polozky = unserialize($faktura["faktura_polozka"][0]);
	    $castky = unserialize($faktura["faktura_castka"][0]);

	    $celkem = 0;

	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $dom->formatOutput = true;

	    $invoice = $dom->createElementNS('http://isdoc.cz/namespace/2013', 'Invoice');
	    $invoice->setAttribute('version', '6.0.1');
	    $dom->appendChild($invoice);

	    /*  enumeration	1	Faktura - daňový doklad
			enumeration	2	Opravný daňový doklad (dobropis)
			enumeration	3	Opravný daňový doklad (vrubopis)
			enumeration	4	Zálohová faktura (nedaňový zálohový list)
			enumeration	5	Daňový doklad při přijetí platby (daňový zálohový list)
			enumeration	6	Opravný daňový doklad při přijetí platby (dobropis DZL)
			enumeration	7	Zjednodušený daňový doklad */

	    $typeOfDocument = 1; // Faktura - daňový doklad
	    $typeOfDocument = 2; // Opravný daňový doklad (dobropis)
	    $typeOfDocument = 4; // Zálohová faktura (nedaňový zálohový list)


	    // === HLAVIČKA (správné pořadí!) ===
	    $invoice->appendChild($dom->createElement('DocumentType', '1'));
	    $invoice->appendChild($dom->createElement('SubDocumentType', '0'));
	    $invoice->appendChild($dom->createElement('SubDocumentTypeOrigin', '0'));
	    $invoice->appendChild($dom->createElement('ID', $cisloFaktury));
	    $invoice->appendChild($dom->createElement('UUID', uuidV4()));
	    $invoice->appendChild($dom->createElement('IssueDate', $datumVystaveni));
	    $invoice->appendChild($dom->createElement('TaxPointDate', $datumVystaveni));
	    $invoice->appendChild($dom->createElement('VATApplicable', 'true'));

	    $epa = $dom->createElement('ElectronicPossibilityAgreementReference', 'ISDOC');
	    $epa->setAttribute('languageID', 'cs');
	    $invoice->appendChild($epa);

	    $invoice->appendChild($dom->createElement('LocalCurrencyCode', 'CZK'));
	    $invoice->appendChild($dom->createElement('CurrRate', '1'));
	    $invoice->appendChild($dom->createElement('RefCurrRate', '1'));

	    // === DODAVATEL ===
	    $supplier = $dom->createElement('AccountingSupplierParty');
	    $invoice->appendChild($supplier);

	    $party = $dom->createElement('Party');
	    $supplier->appendChild($party);

	    $pid = $dom->createElement('PartyIdentification');
	    $pid->appendChild($dom->createElement('ID', $icoDodavatele));
	    $party->appendChild($pid);

	    $pname = $dom->createElement('PartyName');
	    $pname->appendChild($dom->createElement('Name', $nazevDodavatele));
	    $party->appendChild($pname);

	    $postal = $dom->createElement('PostalAddress');
	    $party->appendChild($postal);

	    $streetParts = explode(' ', $uliceDodavatele, 2);
	    $postal->appendChild($dom->createElement('StreetName', parseStreet($uliceDodavatele)['street']));
	    $postal->appendChild($dom->createElement('BuildingNumber', parseStreet($uliceDodavatele)['number'] ));
	    $postal->appendChild($dom->createElement('CityName', parseCityPostal($mestoSpRaw)['city']));
	    $postal->appendChild($dom->createElement('PostalZone', parseCityPostal($mestoSpRaw)['psc']));

		$country = $dom->createElement('Country');
		$country->appendChild($dom->createElement('IdentificationCode', 'CZ'));
		$country->appendChild($dom->createElement('Name', 'Česká republika'));
		$postal->appendChild($country);

		$tax = $dom->createElement('PartyTaxScheme');
		$tax->appendChild( $dom->createElement('CompanyID', $dicDodavatele) );
		$tax->appendChild( $dom->createElement('TaxScheme') );
		$party->appendChild($tax);

	    // === ODBĚRATEL ===
	    $customer = $dom->createElement('AccountingCustomerParty');
	    $invoice->appendChild($customer);

	    $cparty = $dom->createElement('Party');
	    $customer->appendChild($cparty);

	    $cpid = $dom->createElement('PartyIdentification');
	    $cpid->appendChild($dom->createElement('ID', $icoOdberatele));
	    $cparty->appendChild($cpid);

	    $cpname = $dom->createElement('PartyName');
	    $cpname->appendChild($dom->createElement('Name', $nazevOdberatele));
	    $cparty->appendChild($cpname);

	    $cpostal = $dom->createElement('PostalAddress');
	    $cparty->appendChild($cpostal);

	    $streetParts2 = explode(' ', $uliceOdberatele, 2);
	    $cpostal->appendChild($dom->createElement('StreetName', parseStreet($uliceOdberatele)['street']));
	    $cpostal->appendChild($dom->createElement('BuildingNumber', parseStreet($uliceOdberatele)['number']));
	    $cpostal->appendChild($dom->createElement('CityName', parseCityPostal($mestoRaw)['city']));
	    $cpostal->appendChild($dom->createElement('PostalZone', parseCityPostal($mestoRaw)['psc']));

		$ccountry = $dom->createElement('Country');
		$ccountry->appendChild($dom->createElement('IdentificationCode', 'CZ'));
		$ccountry->appendChild($dom->createElement('Name', 'Česká republika'));
		$cpostal->appendChild($ccountry);

		$ctax = $dom->createElement('PartyTaxScheme');
		$ctax->appendChild( $dom->createElement('CompanyID', $dicOdberatele) );
		$ctax->appendChild( $dom->createElement('TaxScheme') );
		$cparty->appendChild($ctax);	    

	    // === POLOŽKY ===
	    $lines = $dom->createElement('InvoiceLines');
	    $invoice->appendChild($lines);

	    $i = 1;
	    foreach ($castky as $k => $castka) {

	        if ($castka !== '' && floatval($castka) > 0) {
	            $castka = number_format(floatval($castka), 2, '.', '');
	            $celkem += floatval($castka);

	            $line = $dom->createElement('InvoiceLine');
				$lines->appendChild($line);

				$line->appendChild($dom->createElement('ID', $i));

				$qty = $dom->createElement('InvoicedQuantity', '1');
				$qty->setAttribute('unitCode', 'Ks');
				$line->appendChild($qty);

				$line->appendChild($dom->createElement('LineExtensionAmount', $castka));
				$line->appendChild($dom->createElement('LineExtensionAmountTaxInclusive', $castka));
				// $line->appendChild($dom->createElement('LineExtensionAmountTaxInclusiveBeforeDiscount', $castka)); // Cena před slevou
				$line->appendChild($dom->createElement('LineExtensionTaxAmount', '0.00'));

				$line->appendChild($dom->createElement('UnitPrice', $castka));
				$line->appendChild($dom->createElement('UnitPriceTaxInclusive', $castka));

				/* 🔴 ClassifiedTaxCategory MUSÍ být před Item */
				$taxcat = $dom->createElement('ClassifiedTaxCategory');
				$taxcat->appendChild($dom->createElement('Percent', '0'));
				$taxcat->appendChild( $dom->createElement('VATCalculationMethod', '0'));
				$line->appendChild($taxcat);
				$item = $dom->createElement('Item');
				$item->appendChild( $dom->createElement('Description', $polozky[$k]) );
				
				$ean = $dom->createElement('SellersItemIdentification');
				$ean->appendChild($dom->createElement('ID', '1234')); // Kód zboží 1. dle prodejce 

				$line->appendChild($item);
				$item->appendChild($ean);

	            $i++;
	        }
	    }

	    $celkem = number_format($celkem, 2, '.', '');

	    // === DPH ===
		$taxTotal = $dom->createElement('TaxTotal');
		$invoice->appendChild($taxTotal);

		$sub = $dom->createElement('TaxSubTotal');
		$taxTotal->appendChild($sub);

		$sub->appendChild( $dom->createElement('TaxableAmount', $celkem) );
		$sub->appendChild( $dom->createElement('TaxAmount', '0') );
		$sub->appendChild( $dom->createElement('TaxInclusiveAmount', $celkem) );

		$sub->appendChild( $dom->createElement('AlreadyClaimedTaxableAmount', 0));
		$sub->appendChild( $dom->createElement('AlreadyClaimedTaxAmount', 0));
		$sub->appendChild( $dom->createElement('AlreadyClaimedTaxInclusiveAmount', 0));
		$sub->appendChild( $dom->createElement('DifferenceTaxableAmount', 0));
		$sub->appendChild( $dom->createElement('DifferenceTaxAmount', 0));
		$sub->appendChild( $dom->createElement('DifferenceTaxInclusiveAmount', 0));

		$taxCategory = $dom->createElement('TaxCategory');
		$taxCategory->appendChild( $dom->createElement('Percent', '0') );
		$sub->appendChild($taxCategory);
		$taxTotal->appendChild( $dom->createElement('TaxAmount', '0.00') );

	    // === TOTAL ===
	    $legal = $dom->createElement('LegalMonetaryTotal');
	    $invoice->appendChild($legal);

	    // $legal->appendChild($dom->createElement('LineExtensionAmount', $celkem));
	    $legal->appendChild($dom->createElement('TaxExclusiveAmount', $celkem));
	    $legal->appendChild($dom->createElement('TaxInclusiveAmount', $celkem));
	    $legal->appendChild($dom->createElement('AlreadyClaimedTaxExclusiveAmount', 0));
	    $legal->appendChild($dom->createElement('AlreadyClaimedTaxInclusiveAmount', 0));
	    $legal->appendChild($dom->createElement('DifferenceTaxExclusiveAmount', 0));
	    $legal->appendChild($dom->createElement('DifferenceTaxInclusiveAmount', 0));
	    $legal->appendChild($dom->createElement('PayableRoundingAmount', 0));
	    $legal->appendChild($dom->createElement('PaidDepositsAmount', 0));
	    $legal->appendChild($dom->createElement('PayableAmount', $celkem));

	    // === PLATBA ===
	    $paymentMeans = $dom->createElement('PaymentMeans');
		$invoice->appendChild($paymentMeans);
		$payment = $dom->createElement('Payment');
		$paymentMeans->appendChild($payment);
		$payment->appendChild( $dom->createElement('PaidAmount', '0') );
		$payment->appendChild( $dom->createElement('PaymentMeansCode', '42') );
		$details = $dom->createElement('Details');
		$payment->appendChild($details);
		$details->appendChild( $dom->createElement('PaymentDueDate', $datumSplatnosti) );
		$details->appendChild( $dom->createElement('ID', $cisloUctu) );
		$details->appendChild( $dom->createElement('BankCode', $kodBanky) );
		$details->appendChild( $dom->createElement('Name', 'Fio banka, a.s.') );
		$details->appendChild( $dom->createElement('IBAN', $iban) );
		$details->appendChild( $dom->createElement('BIC', 'FIOBCZPP') );
		$details->appendChild( $dom->createElement('VariableSymbol', $vs) );

	    return $dom->saveXML();
	}



?>