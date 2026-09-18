<?php
/**
 * AJAX handlers for the dashboard widget.
 *
 * @package WordPressDashboardToDoList
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * To-do AJAX.
 */
class MTZ_Todo_Ajax {

	/**
	 * Hook registration.
	 */
	public function __construct() {
		add_action( 'wp_ajax_mtz_todo_list', array( $this, 'list_tasks' ) );
		add_action( 'wp_ajax_mtz_todo_save', array( $this, 'save_task' ) );
		add_action( 'wp_ajax_mtz_todo_toggle', array( $this, 'toggle_complete' ) );
		add_action( 'wp_ajax_mtz_todo_delete', array( $this, 'delete_task' ) );
		add_action( 'wp_ajax_mtz_todo_search_posts', array( $this, 'search_posts' ) );
	}

	/**
	 * Verify nonce and capability.
	 */
	private function verify() {
		check_ajax_referer( 'mtz_todo_nonce', 'nonce' );
		if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Not allowed.', 'dashboard-todo-list-widget' ) ) );
		}
	}

	/**
	 * Whether the current user may change a to-do.
	 *
	 * @param int $id Post ID.
	 * @return bool
	 */
	private function can_access( $id ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		return (int) get_post_meta( $id, '_mtz_assigned_user', true ) === get_current_user_id();
	}

	/**
	 * Format a due date with the site date format.
	 *
	 * @param string $due_raw Raw Y-m-d value.
	 * @return string
	 */
	private function format_due_date_for_site( $due_raw ) {
		$due_raw = (string) $due_raw;
		if ( '' === $due_raw ) {
			return '';
		}
		$ts = strtotime( $due_raw );
		if ( ! $ts ) {
			return $due_raw;
		}
		return date_i18n( get_option( 'date_format' ), $ts );
	}

	/**
	 * List to-dos.
	 */
	public function list_tasks() {
		$this->verify();
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in verify().

		$include_completed = ! empty( $_POST['include_completed'] );

		$args = array(
			'post_type'      => MTZ_TODO_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		);

		if ( ! $include_completed ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
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
			);
		}

		$q    = new WP_Query( $args );
		$data = array();

		foreach ( $q->posts as $post ) {
			$uid  = (int) get_post_meta( $post->ID, '_mtz_assigned_user', true );
			$user = $uid ? get_user_by( 'id', $uid ) : null;

			$due_raw  = (string) get_post_meta( $post->ID, '_mtz_due_date', true );
			$priority = (int) get_post_meta( $post->ID, '_mtz_priority', true );

			$related_id        = (int) get_post_meta( $post->ID, '_mtz_related_post_id', true );
			$related_title     = '';
			$related_edit_link = '';

			if ( $related_id ) {
				$related_title     = get_the_title( $related_id );
				$related_edit_link = get_edit_post_link( $related_id, 'raw' );
				if ( ! $related_edit_link ) {
					$related_edit_link = '';
				}
			}

			$data[] = array(
				'id'                => $post->ID,
				'title'             => $post->post_title,
				'description'       => get_post_meta( $post->ID, '_mtz_description', true ),
				'assigned_user_id'  => $uid,
				'assigned_name'     => $user ? $user->display_name : '',
				'due_date'          => $due_raw,
				'due_date_display'  => $this->format_due_date_for_site( $due_raw ),
				'priority'          => $priority,
				'completed'         => (int) get_post_meta( $post->ID, '_mtz_completed', true ),
				'related_id'        => $related_id ? $related_id : 0,
				'related_title'     => $related_title ? $related_title : '',
				'related_edit_link' => $related_edit_link ? $related_edit_link : '',
			);
		}

		usort(
			$data,
			function ( $a, $b ) {
				$pa = (int) ( $a['priority'] ?? 0 );
				$pb = (int) ( $b['priority'] ?? 0 );
				if ( $pa !== $pb ) {
					return $pa <=> $pb;
				}
				$da  = (string) ( $a['due_date'] ?? '' );
				$db  = (string) ( $b['due_date'] ?? '' );
				$tsa = '' !== $da ? strtotime( $da ) : PHP_INT_MAX;
				$tsb = '' !== $db ? strtotime( $db ) : PHP_INT_MAX;
				if ( $tsa !== $tsb ) {
					return $tsa <=> $tsb;
				}
				return strcasecmp( (string) ( $a['title'] ?? '' ), (string) ( $b['title'] ?? '' ) );
			}
		);

		wp_send_json_success( $data );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Create or update a to-do.
	 */
	public function save_task() {
		$this->verify();
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in verify().

		$id    = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		if ( ! $title ) {
			wp_send_json_error( array( 'message' => __( 'Title required', 'dashboard-todo-list-widget' ) ) );
		}

		if ( $id ) {
			if ( ! $this->can_access( $id ) ) {
				wp_send_json_error( array( 'message' => __( 'Unauthorized', 'dashboard-todo-list-widget' ) ) );
			}
			wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => $title,
				)
			);
		} else {
			$id = wp_insert_post(
				array(
					'post_type'   => MTZ_TODO_CPT,
					'post_status' => 'publish',
					'post_title'  => $title,
				)
			);
		}

		if ( ! $id || is_wp_error( $id ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not save to-do.', 'dashboard-todo-list-widget' ) ) );
		}

		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$assigned    = isset( $_POST['assigned_user_id'] ) ? (int) $_POST['assigned_user_id'] : 0;
		$due         = isset( $_POST['due_date'] ) ? sanitize_text_field( wp_unslash( $_POST['due_date'] ) ) : '';
		$priority    = isset( $_POST['priority'] ) ? (int) $_POST['priority'] : 3;
		$completed   = ! empty( $_POST['completed'] ) ? 1 : 0;
		$related_id  = isset( $_POST['related_post_id'] ) ? (int) $_POST['related_post_id'] : 0;

		update_post_meta( $id, '_mtz_description', $description );
		update_post_meta( $id, '_mtz_assigned_user', $assigned );
		update_post_meta( $id, '_mtz_due_date', $due );
		update_post_meta( $id, '_mtz_priority', $priority );
		update_post_meta( $id, '_mtz_completed', $completed );
		update_post_meta( $id, '_mtz_related_post_id', $related_id );

		wp_send_json_success();
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Toggle completed.
	 */
	public function toggle_complete() {
		$this->verify();
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in verify().

		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		if ( ! $this->can_access( $id ) ) {
			wp_send_json_error();
		}
		update_post_meta( $id, '_mtz_completed', isset( $_POST['completed'] ) ? (int) $_POST['completed'] : 0 );
		wp_send_json_success();
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Permanently delete a to-do.
	 */
	public function delete_task() {
		$this->verify();
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in verify().

		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		if ( ! $this->can_access( $id ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'dashboard-todo-list-widget' ) ) );
		}
		wp_delete_post( $id, true );
		wp_send_json_success();
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Search public posts/pages for the related-content field.
	 */
	public function search_posts() {
		$this->verify();
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in verify().

		$term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
		if ( strlen( $term ) < 2 ) {
			wp_send_json_success( array() );
		}

		$types = get_post_types( array( 'public' => true ), 'names' );
		unset( $types[ MTZ_TODO_CPT ] );

		$q = new WP_Query(
			array(
				's'              => $term,
				'post_type'      => array_values( $types ),
				'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
				'posts_per_page' => 20,
				'orderby'        => 'relevance',
				'no_found_rows'  => true,
				'fields'         => 'ids',
			)
		);

		$results = array();
		foreach ( $q->posts as $pid ) {
			$results[] = array(
				'label' => get_the_title( $pid ) . ' (#' . $pid . ')',
				'value' => get_the_title( $pid ),
				'id'    => (int) $pid,
			);
		}

		wp_send_json_success( $results );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}
}
