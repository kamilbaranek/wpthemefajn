<?php

	add_action( 'init', function() {

		$labels = array(
			'name'                  => _x( 'Vedoucí', 'Post Type General Name', 'fajntabory' ),
			'singular_name'         => _x( 'Vedoucí', 'Post Type Singular Name', 'fajntabory' ),
			'menu_name'             => __( 'Vedoucí', 'fajntabory' ),
			'name_admin_bar'        => __( 'Vedoucí', 'fajntabory' ),
			'archives'              => __( 'Vedoucí archiv', 'fajntabory' ),
			'attributes'            => __( 'Atributy vedoucí', 'fajntabory' ),
			'parent_item_colon'     => __( 'Nadřazený vedoucí', 'fajntabory' ),
			'all_items'             => __( 'Všichni vedoucí', 'fajntabory' ),
			'add_new_item'          => __( 'Přidat nového vedoucího', 'fajntabory' ),
			'add_new'               => __( 'Přidat vedoucího', 'fajntabory' ),
			'new_item'              => __( 'Nový vedoucí', 'fajntabory' ),
			'edit_item'             => __( 'Upravit vedoucího', 'fajntabory' ),
			'update_item'           => __( 'Aktualizovat vedoucího', 'fajntabory' ),
			'view_item'             => __( 'Zobrazit vedoucího', 'fajntabory' ),
			'view_items'            => __( 'Zobrazit vedoucí', 'fajntabory' ),
			'search_items'          => __( 'Hledat vedoucí', 'fajntabory' ),
			'not_found'             => __( 'Nenalezeno', 'fajntabory' ),
			'not_found_in_trash'    => __( 'Nenalezeno', 'fajntabory' ),
			'featured_image'        => __( 'Náhledový obrázek', 'fajntabory' ),
			'set_featured_image'    => __( 'Nastavit náhledový obrázek', 'fajntabory' ),
			'remove_featured_image' => __( 'Odstranit náhledový obrázek', 'fajntabory' ),
			'use_featured_image'    => __( 'Použít jako náhledový obrázek', 'fajntabory' ),
			'insert_into_item'      => __( 'Vložit do vizitky', 'fajntabory' ),
			'uploaded_to_this_item' => __( 'Nahrát do této vizitky', 'fajntabory' ),
			'items_list'            => __( 'Seznam vedoucích', 'fajntabory' ),
			'items_list_navigation' => __( 'Navigace seznamu vedoucích', 'fajntabory' ),
			'filter_items_list'     => __( 'Filtr seznamu vedoucích', 'fajntabory' ),
		);

		$args = array(
			'label'                 => __( 'Vedoucí', 'fajntabory' ),
			'description'           => __( 'Vedoucí', 'fajntabory' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-groups',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,		
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);

		register_post_type( 'vedouci', $args );

	} );

?>