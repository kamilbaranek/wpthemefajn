<?php

	add_action( 'init', function() {

		$labels = array(
			'name'                  => _x( 'Galerie', 'Post Type General Name', 'fajntabory' ),
			'singular_name'         => _x( 'Galerie', 'Post Type Singular Name', 'fajntabory' ),
			'menu_name'             => __( 'Galerie', 'fajntabory' ),
			'name_admin_bar'        => __( 'Galerie', 'fajntabory' ),
			'archives'              => __( 'Galerie archiv', 'fajntabory' ),
			'attributes'            => __( 'Atributy Galerie', 'fajntabory' ),
			'parent_item_colon'     => __( 'Nadřazená galerie', 'fajntabory' ),
			'all_items'             => __( 'Všechny galerie', 'fajntabory' ),
			'add_new_item'          => __( 'Přidat novou galerii', 'fajntabory' ),
			'add_new'               => __( 'Přidat galerii', 'fajntabory' ),
			'new_item'              => __( 'Nová galerie', 'fajntabory' ),
			'edit_item'             => __( 'Upravit galerii', 'fajntabory' ),
			'update_item'           => __( 'Aktualizovat galerii', 'fajntabory' ),
			'view_item'             => __( 'Zobrazit galerii', 'fajntabory' ),
			'view_items'            => __( 'Zobrazit galerii', 'fajntabory' ),
			'search_items'          => __( 'Hledat galerii', 'fajntabory' ),
			'not_found'             => __( 'Nenalezeno', 'fajntabory' ),
			'not_found_in_trash'    => __( 'Nenalezeno', 'fajntabory' ),
			'featured_image'        => __( 'Náhledový obrázek', 'fajntabory' ),
			'set_featured_image'    => __( 'Nastavit náhledový obrázek', 'fajntabory' ),
			'remove_featured_image' => __( 'Odstranit náhledový obrázek', 'fajntabory' ),
			'use_featured_image'    => __( 'Použít jako náhledový obrázek', 'fajntabory' ),
			'insert_into_item'      => __( 'Vložit do galerie', 'fajntabory' ),
			'uploaded_to_this_item' => __( 'Nahrát do této galerie', 'fajntabory' ),
			'items_list'            => __( 'Seznam galerií', 'fajntabory' ),
			'items_list_navigation' => __( 'Navigace seznamu galerií', 'fajntabory' ),
			'filter_items_list'     => __( 'Filtr seznamu galerií', 'fajntabory' ),
		);

		$args = array(
			'label'                 => __( 'Galerie', 'fajntabory' ),
			'description'           => __( 'Galerie', 'fajntabory' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-images-alt2',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,		
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);

		register_post_type( 'galerie', $args );

	} );

?>