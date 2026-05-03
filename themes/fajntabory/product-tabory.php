<?php
$term = wp_get_post_terms( get_the_id(), 'typ-tabora' );
$term = ! empty( $term ) ? $term[0] : null;

$barva = '#14b5e1';
if ( ! empty( $term ) ) {
	$term_barva = get_field( 'barva', 'typ-tabora_' . $term->term_id );
	if ( ! empty( $term_barva ) ) {
		$barva = $term_barva;
	}
}

$pobytovy_content = get_field( 'popis_pro_pobytovy_tabor' );
$primestsky_content = get_field( 'popis_pro_primestsky_tabor' );

$hero_summary = trim( wp_strip_all_tags( get_the_excerpt() ) );
$hero_summary = ! empty( $hero_summary ) ? wp_trim_words( $hero_summary, 42, '…' ) : '';

$hero_image_full = get_the_post_thumbnail_url( get_the_id(), 'full' );
$hero_tiles = array();
if ( has_post_thumbnail() ) {
	$hero_tiles[] = array(
		'class' => 'is-featured',
		'full'  => $hero_image_full,
		'image' => get_the_post_thumbnail(
			get_the_id(),
			'large',
			array(
				'class' => 'camp-hero__tile-image',
				'alt'   => trim( wp_strip_all_tags( get_the_title() ) ),
			)
		),
	);
}

global $product;
$attachment_ids = array();
if ( ! empty( $product ) ) {
	$attachment_ids = $product->get_gallery_attachment_ids();
}

if ( ! empty( $attachment_ids ) ) {
	foreach ( array_slice( $attachment_ids, 0, 4 ) as $attachment_id ) {
		$hero_tiles[] = array(
			'class' => '',
			'full'  => wp_get_attachment_url( $attachment_id ),
			'image' => wp_get_attachment_image(
				$attachment_id,
				'large',
				false,
				array(
					'class' => 'camp-hero__tile-image',
				)
			),
		);
	}
}

$pobytovy = array();
$primestsky = array();
$count_pobytove = 0;
$count_primestske = 0;

if ( ! empty( $product ) && $product->is_type( 'variable' ) ) {
	$available_variations = $product->get_available_variations();

	foreach ( $available_variations as $variation ) {
		$regular_price = (float) $variation['display_regular_price'];
		$sale_price = (float) wc_format_decimal( get_post_meta( $variation['variation_id'], '_sale_price', true ) );
		$display_price = $regular_price > $sale_price && $sale_price > 0 ? $sale_price : (float) $variation['display_price'];

		$taxonomy = 'pa_lokalita';
		$lokalita = get_post_meta( $variation['variation_id'], 'attribute_' . $taxonomy, true );
		$lokalita = get_term_by( 'slug', $lokalita, $taxonomy );
		$lokalita = ! empty( $lokalita->name ) ? $lokalita->name : '';

		$taxonomy = 'pa_typ-tabora';
		$typ_tabora = get_post_meta( $variation['variation_id'], 'attribute_' . $taxonomy, true );
		$typ_tabora = get_term_by( 'slug', $typ_tabora, $taxonomy );
		$typ_tabora_slug = ! empty( $typ_tabora->slug ) ? $typ_tabora->slug : '';
		$typ_tabora_name = ! empty( $typ_tabora->name ) ? $typ_tabora->name : '';

		$taxonomy = 'pa_terminy';
		$terminy = get_post_meta( $variation['variation_id'], 'attribute_' . $taxonomy, true );
		$terminy = get_term_by( 'slug', $terminy, $taxonomy );
		$terminy = ! empty( $terminy->name ) ? $terminy->name : '';

		$discount_to = (int) get_post_meta( $variation['variation_id'], '_sale_price_dates_to', true );
		$manage_stock = get_post_meta( $variation['variation_id'], '_manage_stock', true ) != 'no';
		$max_qty = ! empty( $variation['max_qty'] ) ? (int) $variation['max_qty'] : 0;
		$current_qty = $manage_stock ? $max_qty - count( retrieve_orders_ids_from_a_product_id( $variation['variation_id'] ) ) : null;

		$availability_label = '';
		$availability_class = '';
		$can_order = true;
		$order_link = '?add_to_cart=' . $variation['variation_id'];

		if ( $manage_stock ) {
			$current_qty = max( 0, (int) $current_qty );

			if ( $current_qty <= 0 ) {
				$availability_label = 'Obsazeno';
				$availability_class = 'is-full';
				$can_order = false;
				$order_link = '#';
			} elseif ( $current_qty < 6 ) {
				$availability_label = 'Zbývá ' . $current_qty . ' míst';
				$availability_class = 'is-low';
			} else {
				$availability_label = 'Zbývá ' . $current_qty . ' míst';
				$availability_class = 'is-open';
			}
		}

		$item = array(
			'lokalita'              => $lokalita,
			'typ_tabora'            => $typ_tabora_name,
			'terminy'               => $terminy,
			'regular_price'         => $regular_price,
			'discount_price'        => $display_price,
			'discount_to'           => $discount_to,
			'variation_id'          => $variation['variation_id'],
			'current_qty'           => $current_qty,
			'manage_stock'          => $manage_stock,
			'can_order'             => $can_order,
			'order_link'            => $order_link,
			'availability_label'    => $availability_label,
			'availability_class'    => $availability_class,
		);

		if ( $typ_tabora_slug == 'pobytovy-tabor' ) {
			$pobytovy[] = $item;
			$count_pobytove++;
		}

		if ( $typ_tabora_slug == 'primestsky-tabor' ) {
			$primestsky[] = $item;
			$count_primestske++;
		}
	}
}

$has_pobytovy = $count_pobytove > 0;
$has_primestsky = $count_primestske > 0;
$default_tab = $has_pobytovy ? 'pobytovy' : 'primestsky';
$camp_currency_symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : 'Kč';
$camp_get_display_price = function( $item ) {
	return $item['discount_price'] > 0 ? $item['discount_price'] : $item['regular_price'];
};
$camp_format_price_text = function( $price ) use ( $camp_currency_symbol ) {
	return number_format_i18n( (float) $price, 0 ) . ' ' . $camp_currency_symbol;
};
$camp_summary_price = function( $price ) use ( $camp_currency_symbol ) {
	return number_format_i18n( (float) $price, 0 ) . '&nbsp;' . esc_html( $camp_currency_symbol );
};
$camp_discount_note = function( $discount_to ) {
	if ( ! empty( $discount_to ) && $discount_to >= time() ) {
		return 'Platí do ' . date_i18n( 'j.n.Y', $discount_to );
	}

	return '';
};

$pobytovy_location = '';
if ( ! empty( $pobytovy ) ) {
	$pobytovy_location = $pobytovy[0]['lokalita'];
}

$primestsky_location = '';
if ( ! empty( $primestsky ) ) {
	$primestsky_location = $primestsky[0]['lokalita'];
}
$render_booking_panel = function( $panel_id, $items, $location, $is_active ) use ( $camp_summary_price, $camp_get_display_price, $camp_format_price_text, $camp_discount_note ) {
	if ( empty( $items ) ) {
		return;
	}

	$selected_item = $items[0];
	$selected_price = $camp_get_display_price( $selected_item );
	$selected_has_discount = $selected_item['regular_price'] > $selected_price;
	$selected_discount_note = $camp_discount_note( $selected_item['discount_to'] );
	?>
	<div class="tabcontent camp-tab-panel camp-booking__panel" data-tab-panel="<?php echo esc_attr( $panel_id ); ?>" style="<?php echo $is_active ? 'display:block;' : 'display:none;'; ?>">
		<div class="camp-booking__summary">
			<?php if ( ! empty( $location ) ) { ?>
				<div class="camp-booking__summary-item camp-booking__summary-item--location">
					<span>Lokalita</span>
					<strong><?php echo esc_html( $location ); ?></strong>
				</div>
			<?php } ?>
			<div class="camp-booking__summary-item camp-booking__summary-item--price">
				<span>Cena</span>
				<strong data-camp-summary-price><?php echo $camp_summary_price( $selected_price ); ?></strong>
				<small class="camp-booking__summary-status <?php echo esc_attr( $selected_item['availability_class'] ); ?><?php echo $selected_item['manage_stock'] ? '' : ' is-hidden'; ?>" data-camp-summary-availability><?php echo esc_html( $selected_item['availability_label'] ); ?></small>
			</div>
		</div>

		<div class="camp-booking__picker" data-camp-picker>
			<div class="camp-booking__field">
				<p class="camp-booking__mobile-title">Vyberte vhodný termín</p>
				<label class="camp-booking__label" for="camp-booking-select-<?php echo esc_attr( $panel_id ); ?>">Termín</label>
				<div class="camp-booking__select-wrap">
					<select class="camp-booking__select" id="camp-booking-select-<?php echo esc_attr( $panel_id ); ?>" data-camp-select>
						<?php foreach ( $items as $tabor ) { ?>
							<?php
							$item_price = $camp_get_display_price( $tabor );
							$item_has_discount = $tabor['regular_price'] > $item_price;
							$item_discount_note = $camp_discount_note( $tabor['discount_to'] );
							?>
							<option
								value="<?php echo esc_attr( $tabor['variation_id'] ); ?>"
								data-location="<?php echo esc_attr( $tabor['lokalita'] ); ?>"
								data-term="<?php echo esc_attr( $tabor['terminy'] ); ?>"
								data-price="<?php echo esc_attr( $camp_format_price_text( $item_price ) ); ?>"
								data-price-old="<?php echo esc_attr( $item_has_discount ? $camp_format_price_text( $tabor['regular_price'] ) : '' ); ?>"
								data-discount-note="<?php echo esc_attr( $item_discount_note ); ?>"
								data-availability-label="<?php echo esc_attr( $tabor['availability_label'] ); ?>"
								data-availability-class="<?php echo esc_attr( $tabor['availability_class'] ); ?>"
								data-manage-stock="<?php echo $tabor['manage_stock'] ? '1' : '0'; ?>"
								data-can-order="<?php echo $tabor['can_order'] ? '1' : '0'; ?>"
								data-order-link="<?php echo esc_attr( $tabor['order_link'] ); ?>"
								data-button-label="<?php echo esc_attr( $tabor['can_order'] ? 'Rezervovat' : 'Obsazeno' ); ?>"
							><?php echo esc_html( $tabor['terminy'] ); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div class="camp-booking-card camp-booking-card--selected" data-camp-selection>
				<p class="camp-booking-card__location<?php echo empty( $selected_item['lokalita'] ) ? ' is-hidden' : ''; ?>" data-camp-location><?php echo esc_html( $selected_item['lokalita'] ); ?></p>
				<h3 data-camp-term><?php echo esc_html( $selected_item['terminy'] ); ?></h3>

				<div class="camp-booking-card__meta">
					<div class="camp-booking-card__prices">
						<span class="camp-booking-card__price-old<?php echo $selected_has_discount ? '' : ' is-hidden'; ?>" data-camp-price-old><?php echo esc_html( $selected_has_discount ? $camp_format_price_text( $selected_item['regular_price'] ) : '' ); ?></span>
						<span class="camp-booking-card__price-current" data-camp-price-current><?php echo esc_html( $camp_format_price_text( $selected_price ) ); ?></span>
						<small class="camp-booking-card__discount-note<?php echo ! empty( $selected_discount_note ) ? '' : ' is-hidden'; ?>" data-camp-discount-note><?php echo esc_html( $selected_discount_note ); ?></small>
					</div>

					<div class="camp-booking-card__availability <?php echo esc_attr( $selected_item['availability_class'] ); ?><?php echo $selected_item['manage_stock'] ? '' : ' is-hidden'; ?>" data-camp-availability>
						<?php echo esc_html( $selected_item['availability_label'] ); ?>
					</div>
				</div>

				<a class="camp-booking-card__cta <?php echo ! $selected_item['can_order'] ? 'disabled' : ''; ?>" data-camp-cta href="<?php echo esc_url( $selected_item['order_link'] ); ?>">
					<?php echo $selected_item['can_order'] ? 'Rezervovat' : 'Obsazeno'; ?>
				</a>
			</div>
		</div>
	</div>
	<?php
};
?>

<div id="main" class="camp-product" style="--camp-accent: <?php echo esc_attr( $barva ); ?>;">
	<div class="camp-product__top">
		<section class="camp-hero">
			<div class="camp-hero__tiles">
				<?php if ( ! empty( $hero_tiles ) ) { ?>
					<?php foreach ( $hero_tiles as $tile ) { ?>
						<div class="camp-hero__tile <?php echo esc_attr( $tile['class'] ); ?>">
							<a href="<?php echo esc_url( $tile['full'] ); ?>" rel="prettyPhoto[gallery]">
								<?php echo $tile['image']; ?>
							</a>
						</div>
					<?php } ?>
				<?php } elseif ( ! empty( $hero_image_full ) ) { ?>
					<div class="camp-hero__tile is-featured">
						<img class="camp-hero__tile-image" src="<?php echo esc_url( $hero_image_full ); ?>" alt="<?php echo esc_attr( trim( wp_strip_all_tags( get_the_title() ) ) ); ?>">
					</div>
				<?php } ?>
			</div>

			<div class="camp-hero__copy">
				<?php if ( ! empty( $term->name ) ) { ?>
					<div class="camp-hero__eyebrow"><?php echo esc_html( $term->name ); ?></div>
				<?php } ?>

				<?php the_title( '<h1 class="camp-hero__title">', '</h1>' ); ?>

				<?php if ( ! empty( $hero_summary ) ) { ?>
					<p class="camp-hero__summary"><?php echo esc_html( $hero_summary ); ?></p>
				<?php } ?>

				<div class="camp-hero__actions">
					<?php if ( $has_pobytovy || $has_primestsky ) { ?>
						<a class="camp-hero__button" href="#camp-booking">Vybrat termín</a>
					<?php } ?>

					<?php if ( ! empty( $attachment_ids ) ) { ?>
						<a class="camp-hero__button is-secondary" href="#camp-gallery">Fotogalerie</a>
					<?php } ?>
				</div>
			</div>
		</section>

		<?php if ( $has_pobytovy || $has_primestsky ) { ?>
			<aside class="camp-booking" id="camp-booking">
				<?php wc_print_notices(); ?>

				<div class="camp-booking__header">
					<p class="camp-booking__eyebrow">Termíny a ceny</p>
					<h2>Vyberte vhodný termín</h2>
				</div>

				<?php if ( $has_pobytovy && $has_primestsky ) { ?>
					<div class="tab camp-booking__tabs">
						<button class="tablinks active" onclick="openCard(event, 'pobytovy')" data-tab-target="pobytovy" id="defaultOpen">Pobytový tábor</button>
						<button class="tablinks" onclick="openCard(event, 'primestsky')" data-tab-target="primestsky">Příměstský tábor</button>
					</div>
				<?php } ?>

				<?php if ( $has_pobytovy ) { ?>
					<?php $render_booking_panel( 'pobytovy', $pobytovy, $pobytovy_location, $default_tab === 'pobytovy' ); ?>
				<?php } ?>

				<?php if ( $has_primestsky ) { ?>
					<?php $render_booking_panel( 'primestsky', $primestsky, $primestsky_location, $default_tab === 'primestsky' ); ?>
				<?php } ?>
			</aside>
		<?php } ?>
	</div>

	<?php if ( ! empty( $attachment_ids ) ) { ?>
		<section class="camp-gallery-section" id="camp-gallery">
			<h2 class="entry-title" style="background-color: <?php echo esc_attr( $barva ); ?>;">Fotografie<span>Výběr z atmosféry a programu</span></h2>
			<ul id="gallery">
				<?php foreach ( $attachment_ids as $attachment_id ) { ?>
					<li>
						<a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" rel="prettyPhoto[gallery]">
							<?php echo wp_get_attachment_image( $attachment_id, 'gallery' ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</section>
	<?php } ?>

	<div class="description camp-description" id="camp-program">
		<div class="camp-description__intro">
			<h2>Program a detaily tábora</h2>
			<p>Vše podstatné o programu, lokalitě, vybavení a organizaci najdete níže.</p>
		</div>

		<?php if ( $has_pobytovy ) { ?>
			<div class="tabcontent camp-tab-panel camp-description__panel" data-tab-panel="pobytovy" style="<?php echo $default_tab === 'pobytovy' ? 'display:block;' : 'display:none;'; ?>">
				<?php echo do_shortcode( $pobytovy_content ); ?>
			</div>
		<?php } ?>

		<?php if ( $has_primestsky ) { ?>
			<div class="tabcontent camp-tab-panel camp-description__panel" data-tab-panel="primestsky" style="<?php echo $default_tab === 'primestsky' ? 'display:block;' : 'display:none;'; ?>">
				<?php echo do_shortcode( $primestsky_content ); ?>
			</div>
		<?php } ?>
	</div>
</div>
