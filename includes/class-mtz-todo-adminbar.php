<?php
/**
 * Admin bar count of open to-dos assigned to the current user.
 *
 * @package WordPressDashboardToDoList
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin bar node.
 */
class MTZ_Todo_AdminBar {

	/**
	 * Hook registration.
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'register_node' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_mtz_todo_adminbar_count', array( $this, 'ajax_count' ) );
	}

	/**
	 * Add the To-Do node.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar.
	 */
	public function register_node( $wp_admin_bar ) {
		if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'mtz-todo-adminbar',
				'title' => '<span class="ab-label">' . esc_html__( 'To-Do', 'dashboard-todo-list-widget' ) . '</span><span class="mtz-todo-count" style="display:none;">0</span>',
				'href'  => admin_url( 'index.php' ),
				'meta'  => array(
					'class' => 'mtz-todo-adminbar-node',
				),
			)
		);
	}

	/**
	 * Enqueue count script when the admin bar is visible.
	 */
	public function enqueue_assets() {
		if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) || ! is_admin_bar_showing() ) {
			return;
		}

		wp_enqueue_script(
			'mtz-todo-adminbar',
			MTZ_TODO_URL . 'assets/js/adminbar.js',
			array( 'jquery' ),
			MTZ_TODO_VERSION,
			true
		);

		wp_localize_script(
			'mtz-todo-adminbar',
			'MTZTodoAdminBar',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mtz_todo_nonce' ),
			)
		);

		wp_enqueue_style(
			'mtz-todo-adminbar',
			MTZ_TODO_URL . 'assets/css/adminbar.css',
			array(),
			MTZ_TODO_VERSION
		);
	}

	/**
	 * Return the number of incomplete to-dos assigned to the current user.
	 */
	public function ajax_count() {
		check_ajax_referer( 'mtz_todo_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error();
		}

		$user_id = get_current_user_id();

		$q = new WP_Query(
			array(
				'post_type'      => MTZ_TODO_CPT,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => 1,
				'no_found_rows'  => false,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_mtz_assigned_user',
						'value' => $user_id,
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => '_mtz_completed',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_mtz_completed',
							'value'   => '0',
							'compare' => '=',
						),
					),
				),
			)
		);

		wp_send_json_success(
			array(
				'count' => (int) $q->found_posts,
			)
		);
	}
}
