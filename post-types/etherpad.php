<?php

/**
 * Registers the `etherpad` post type.
 */
function etherpad_init() {
	register_post_type( 'etherpad', array(
		'labels'                => array(
			'name'                  => __( 'Etherpads', 'etherpad-integration' ),
			'singular_name'         => __( 'Etherpad', 'etherpad-integration' ),
			'all_items'             => __( 'All Etherpads', 'etherpad-integration' ),
			'archives'              => __( 'Etherpad Archives', 'etherpad-integration' ),
			'attributes'            => __( 'Etherpad Attributes', 'etherpad-integration' ),
			'insert_into_item'      => __( 'Insert into Etherpad', 'etherpad-integration' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Etherpad', 'etherpad-integration' ),
			'featured_image'        => _x( 'Featured Image', 'etherpad', 'etherpad-integration' ),
			'set_featured_image'    => _x( 'Set featured image', 'etherpad', 'etherpad-integration' ),
			'remove_featured_image' => _x( 'Remove featured image', 'etherpad', 'etherpad-integration' ),
			'use_featured_image'    => _x( 'Use as featured image', 'etherpad', 'etherpad-integration' ),
			'filter_items_list'     => __( 'Filter Etherpads list', 'etherpad-integration' ),
			'items_list_navigation' => __( 'Etherpads list navigation', 'etherpad-integration' ),
			'items_list'            => __( 'Etherpads list', 'etherpad-integration' ),
			'new_item'              => __( 'New Etherpad', 'etherpad-integration' ),
			'add_new'               => __( 'Add New', 'etherpad-integration' ),
			'add_new_item'          => __( 'Add New Etherpad', 'etherpad-integration' ),
			'edit_item'             => __( 'Edit Etherpad', 'etherpad-integration' ),
			'view_item'             => __( 'View Etherpad', 'etherpad-integration' ),
			'view_items'            => __( 'View Etherpads', 'etherpad-integration' ),
			'search_items'          => __( 'Search Etherpads', 'etherpad-integration' ),
			'not_found'             => __( 'No Etherpads found', 'etherpad-integration' ),
			'not_found_in_trash'    => __( 'No Etherpads found in trash', 'etherpad-integration' ),
			'parent_item_colon'     => __( 'Parent Etherpad:', 'etherpad-integration' ),
			'menu_name'             => __( 'Etherpads', 'etherpad-integration' ),
		),
		'public'                => true,
		'hierarchical'          => false,
		'show_ui'               => true,
		'show_in_nav_menus'     => true,
		'supports'              => array( 'title', 'editor' ),
		'has_archive'           => true,
		'rewrite'               => true,
		'query_var'             => true,
		'menu_position'         => null,
		'menu_icon'             => 'dashicons-admin-post',
		'show_in_rest'          => true,
		'rest_base'             => 'etherpad',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'etherpad_init' );

/**
 * Sets the post updated messages for the `etherpad` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `etherpad` post type.
 */
function etherpad_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['etherpad'] = array(
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'Etherpad updated. <a target="_blank" href="%s">View Etherpad</a>', 'etherpad-integration' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'etherpad-integration' ),
		3  => __( 'Custom field deleted.', 'etherpad-integration' ),
		4  => __( 'Etherpad updated.', 'etherpad-integration' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Etherpad restored to revision from %s', 'etherpad-integration' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		/* translators: %s: post permalink */
		6  => sprintf( __( 'Etherpad published. <a href="%s">View Etherpad</a>', 'etherpad-integration' ), esc_url( $permalink ) ),
		7  => __( 'Etherpad saved.', 'etherpad-integration' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'Etherpad submitted. <a target="_blank" href="%s">Preview Etherpad</a>', 'etherpad-integration' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'Etherpad scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Etherpad</a>', 'etherpad-integration' ),
		date_i18n( __( 'M j, Y @ G:i', 'etherpad-integration' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'Etherpad draft updated. <a target="_blank" href="%s">Preview Etherpad</a>', 'etherpad-integration' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'etherpad_updated_messages' );
