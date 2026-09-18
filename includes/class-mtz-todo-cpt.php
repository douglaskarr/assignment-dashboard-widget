<?php
/**
 * Private custom post type for to-dos.
 *
 * @package WordPressDashboardToDoList
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the private to-do post type.
 */
class MTZ_Todo_CPT {

	/**
	 * Hook registration.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register the hidden CPT.
	 */
	public function register() {
		register_post_type(
			MTZ_TODO_CPT,
			array(
				'labels'              => array(
					'name'          => __( 'To-Dos', 'assignment-dashboard-widget' ),
					'singular_name' => __( 'To-Do', 'assignment-dashboard-widget' ),
				),
				'public'              => false,
				'show_ui'             => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
			)
		);
	}
}
