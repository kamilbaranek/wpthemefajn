<?php



	add_filter( 'woocommerce_hide_invisible_variations', '__return_true' );



	add_filter( 'woocommerce_admin_meta_boxes_variations_per_page', 'handsome_bearded_guy_increase_variations_per_page' );



	function filter_woocommerce_can_reduce_order_stock( $true, $instance ) { 

		return false; 

	}; 

	add_filter( 'woocommerce_can_reduce_order_stock','filter_woocommerce_can_reduce_order_stock', 10, 2 ); 



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

		wp_localize_script( 'general', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

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


		echo '<a href="'.admin_url( 'themes.php?page=theme-options&https://www.fajntabory.cz/wp-admin/themes.php?page=theme-options&export-transport=true' ).'" target="_blank" class="button">Exportovat společnou dopravu</a>';

		echo '</p>';

		echo '<h2>Import táborů</h2>';

		echo '<form action="'.admin_url( 'themes.php?page=theme-options' ).'" method="POST" enctype="multipart/form-data">';

		echo '<p>';

		echo 'Níže vyberte .csv soubor, který chcete importovat.';

		echo '</p>';

		echo '<p>';

		echo '<input type="hidden" name="csv" value="true">';

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

				echo '<img id="image-preview" src="'.wp_get_attachment_image_src( get_option( 'website_logo' ), 'logo' )[0].'">';

				echo '</a>';

			} else {

				echo '<button class="button logopicker">Vybrat soubor</button>';

			}

			echo '<input type="hidden" id="website_logo" name="website_logo" value="'.get_option('website_logo').'">';

			echo '</p>';



		echo '</div>';



		echo '<div class="option_box">';

			echo '<h2>Bankovní účet</h2>';

			echo '<p>';

			echo '<label for="bank_account">Číslo bankovního účtu</label>';

			echo '<input type="text" id="bank_account" name="bank_account" value="'.get_option('bank_account').'">';

			echo '</p>';

			echo '<p>';

			echo '<label for="bank_iban">Mezinárodní číslo bankovního účtu (IBAN)</label>';

			echo '<input type="text" id="bank_iban" name="bank_iban" value="'.get_option('bank_iban').'">';

			echo '</p>';

			echo '<p>';

			echo '<label for="bank_swift">SWIFT / BIC kód banky</label>';

			echo '<input type="text" id="bank_swift" name="bank_swift" value="'.get_option('bank_swift').'">';

			echo '</p>';

			// IBAN

			// SWIFT

		echo '</div>';



		echo '<div class="option_box">';

			

			echo '<h2>Sociální sítě</h2>';



			echo '<p>';

			echo '<label for="facebook_uri">Odkaz na Facebook profil</label>';

			echo '<input type="text" id="facebook_uri" name="facebook_uri" value="'.get_option('facebook_uri').'">';

			echo '</p>';



			echo '<p>';

			echo '<label for="youtube_uri">Odkaz na YouTube profil</label>';

			echo '<input type="text" id="youtube_uri" name="youtube_uri" value="'.get_option('youtube_uri').'">';

			echo '</p>';



			echo '<p>';

			echo '<label for="twitter_uri">Odkaz na Twitter profil</label>';

			echo '<input type="text" id="twitter_uri" name="twitter_uri" value="'.get_option('twitter_uri').'">';

			echo '</p>';



			echo '<p>';

			echo '<label for="instagram_uri">Odkaz na Instagram profil</label>';

			echo '<input type="text" id="instagram_uri" name="instagram_uri" value="'.get_option('instagram_uri').'">';

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



	if( !empty( $_POST['tcsv'] ) ) {

		add_action( 'admin_init', function() {

			if( ! is_user_logged_in() || ! current_user_can( 'manage_options' )) {

				wp_redirect( wp_login_url() );

				exit;

			} else {

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

				echo '<td>'.$metas['jmeno'][0].'</td>';

				echo '<td>'.$metas['prijmeni'][0].'</td>';

				echo '<td>'.$metas['datum_narozeni'][0].'</td>';

				echo '<td>'.$metas['ulice'][0].', '.$metas['mesto'][0].' '.$metas['psc'][0].'</td>';

				echo '<td>'.$metas['narodnost'][0].'</td>';

				echo '<td>'.$metas['skola'][0].'</td>';

				echo '<td>'.$metas['triko'][0].'</td>';



				echo '<td>'.$tabor['typ'].'</td>';

				echo '<td>'.$tabor['lokalita'].'</td>';

				echo '<td>'.$tabor['name'].'</td>';

				echo '<td>'.$tabor['termin'].'</td>';



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

				echo '<td>'.$zamestnavatel.'</td>';

				echo '<td>'.get_post_meta( $id, 'coupon_code', true ).'</td>';



				echo '<td>'.$metas['zpusobilost'][0].'</td>';

				echo '<td>'.$metas['Z_prijmeni'][0].' '.$metas['Z_jmeno'][0].'</td>';

				echo '<td>'.$metas['telefon'][0].'</td>';

				echo '<td>'.$metas['email'][0].'</td>';

				echo '<td>'.$propagace.'</td>';

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

								echo '<td>'.get_the_title( $variation['variation_id'] ).'</td>'; // B

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

								echo '<td>'.get_the_title( $variation['variation_id'] ).'</td>';

								echo '<td>'.$lokalita.'</td>';

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


	if( !empty( $_POST['objednavka']) ) {

		add_action( 'init',  'complete_order' );

	}



	function complete_order() {



		global $woocommerce;

		$bank_account_number = get_option('bank_account');



		if( WC()->cart->get_cart_contents_count() < 1 ) {

			wp_redirect( wc_get_cart_url() );

			exit;

		}

	

		$address = array(

		    'first_name' => $_POST['jmeno'],

		    'last_name'  => $_POST['prijmeni'],

		    'email'      => $_POST['email'],

		    'phone'      => $_POST['telefon'],

		    'address_1'  => $_POST['ulice'],

		    'city'       => $_POST['mesto'],

		    'postcode'   => $_POST['psc'],

		    'country'    => 'CZ'

		);



		$order_data = array(

	        'status' => apply_filters('woocommerce_default_order_status', 'pending'),

	        'customer_id' => $user_id

	    );



	    $new_order = wc_create_order( $order_data );

        $new_order->set_address($address, 'billing');



        if( !empty( $_POST['coupon_code'] ) ) {

        	update_post_meta( $new_order->id, 'coupon_code', sanitize_text_field($_POST['coupon_code']) );

    	}



		foreach ($woocommerce->cart->get_cart() as $cart_item_key => $values) {


				$variation_id = $values['variation_id'];
				$variation = new WC_Product_Variation( $variation_id );
				$variation_slug = $variation->get_attributes();
				$variation_slug = $variation_slug['pa_terminy'];
				if( !empty( $variation_slug ) ) {
					$term = get_term_by('slug', $variation_slug, 'pa_terminy');
					$term_id = $term->term_id;
					$term_meta = get_option( "taxonomy_$term_id" );
					$term_meta = $term_meta['ucet'];
					if( !empty( $term_meta ) ) {
						$bank_account_number = $term_meta;
					}
				}

		    $product = $values['data'];

		    /* $item_id = $new_order->add_product( $product, (int)$values['quantity'] ); */

		    $item_id = $new_order->add_product(

               	$values['data'], 

               	$values['quantity'], 

               	array(

                	'totals' => array(

                    	'subtotal' => $values['line_subtotal'],

                    	'subtotal_tax' => $values['line_subtotal_tax'],

                    	'total' => $values['line_total'],

                    	'tax' => $values['line_tax'],

                    	'tax_data' => $values['line_tax_data'] // Since 2.2

                	)

                )

            );



        }



        $new_order->calculate_totals();

        $newid = $new_order->id;



        foreach ($_POST as $key => $value) {

        	update_post_meta( $newid, $key, $value );

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

		WC()->cart->empty_cart( true );

        wp_redirect( home_url() . '?oid=' . $newid . '&email=' . $_POST['email']  );

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

    	echo '<input type="text" class="short wc_input_price" style="" name="variable_first_sale_price['.$loop.']" id="variable_first_sale_price_'.$loop.'" value="'.get_post_meta( $variation->ID, 'variable_first_sale_price', true ).'" placeholder="Počáteční cena po slevě">';

    	echo '</p>';

    	echo '<p class="form-field form-row form-row-last">';

    	echo '<label for="variable_raising_sale_price'.$loop.'">Částka pro týdenní navyšování (Kč)</label>';

    	echo '<input type="text" class="short wc_input_price" style="" name="variable_raising_sale_price['.$loop.']" id="variable_raising_sale_price_'.$loop.'" value="'.get_post_meta( $variation->ID, 'variable_raising_sale_price', true ).'" placeholder="Navyšovací cena">';

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

		$id = $_POST['id'];

		$p_attributes = $_POST['post'];

		$p_attributes = $p_attributes[0];

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

		 		

		 		$response = json_encode( $response );

				echo $response;

				exit;



		 	}

		}

		exit;



	}





	add_action( 'wp_ajax_dchose', 'dchose' );

	add_action( 'wp_ajax_nopriv_dchose', 'dchose' );



	function dchose() {



		global $woocommerce;



		$n_chose = unserialize($_POST['n_chose']);

		$v_chose = unserialize($_POST['v_chose']);

		$t_chose = unserialize($_POST['t_chose']);

		$z_chose = unserialize($_POST['z_chose']);



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



		$response = json_encode( $response );

		echo $response;

		exit;

	}





	function retrieve_orders_ids_from_a_product_id( $product_id ) {

	    global $wpdb;



	    $orders_statuses = "'wc-pending', 'wc-completed', 'wc-processing', 'wc-on-hold'";



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