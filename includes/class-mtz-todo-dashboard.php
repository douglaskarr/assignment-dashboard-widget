<?php
/**
 * Dashboard widget UI.
 *
 * @package WordPressDashboardToDoList
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the dashboard widget.
 */
class MTZ_Todo_Dashboard {

	/**
	 * Hook registration.
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add the widget for users who can edit posts.
	 */
	public function register_widget() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'mtz_todo_widget',
			__( 'To-Do List', 'todo-dashboard-widget' ),
			array( $this, 'render' )
		);
	}

	/**
	 * Enqueue assets on the dashboard only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'index.php' !== $hook || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		wp_enqueue_style(
			'mtz-todo-dashboard',
			MTZ_TODO_URL . 'assets/css/dashboard.css',
			array(),
			MTZ_TODO_VERSION
		);

		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

		wp_enqueue_script(
			'mtz-todo-dashboard',
			MTZ_TODO_URL . 'assets/js/dashboard.js',
			array( 'jquery', 'jquery-ui-dialog', 'jquery-ui-autocomplete' ),
			MTZ_TODO_VERSION,
			true
		);

		wp_localize_script(
			'mtz-todo-dashboard',
			'MTZTodo',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'mtz_todo_nonce' ),
				'currentUserId' => get_current_user_id(),
			)
		);
	}

	/**
	 * Widget markup.
	 */
	public function render() {
		$users = get_users(
			array(
				'role__in' => array( 'administrator', 'editor', 'author', 'contributor' ),
				'orderby'  => 'display_name',
				'order'    => 'ASC',
			)
		);
		?>
		<div id="mtz-todo-widget">
			<div class="mtz-todo-toolbar">
				<div class="mtz-todo-toolbar-left">
					<button class="button button-primary" id="mtz-todo-add" type="button"><?php esc_html_e( 'Add To-Do', 'todo-dashboard-widget' ); ?></button>
				</div>
				<div class="mtz-todo-toolbar-right">
					<label class="mtz-view-completed">
						<input type="checkbox" id="mtz-view-completed">
						<?php esc_html_e( 'View Completed', 'todo-dashboard-widget' ); ?>
					</label>
				</div>
			</div>

			<table class="widefat striped" id="mtz-todo-table">
				<thead>
					<tr>
						<th class="mtz-sort" data-sort="title"><?php esc_html_e( 'To-Do', 'todo-dashboard-widget' ); ?><span class="mtz-sort-icon"></span></th>
						<th class="mtz-sort" data-sort="assigned_name"><?php esc_html_e( 'Assigned', 'todo-dashboard-widget' ); ?><span class="mtz-sort-icon"></span></th>
						<th class="mtz-sort" data-sort="due_date"><?php esc_html_e( 'Due', 'todo-dashboard-widget' ); ?><span class="mtz-sort-icon"></span></th>
						<th class="mtz-sort" data-sort="priority"><?php esc_html_e( 'Priority', 'todo-dashboard-widget' ); ?><span class="mtz-sort-icon"></span></th>
					</tr>
				</thead>
				<tbody id="mtz-todo-body">
					<tr><td colspan="4"><?php esc_html_e( 'Loading…', 'todo-dashboard-widget' ); ?></td></tr>
				</tbody>
			</table>
		</div>

		<div id="mtz-todo-view-modal" style="display:none;">
			<input type="hidden" id="mtz-view-task-id" />
			<div class="mtz-view-line"><strong><?php esc_html_e( 'Title:', 'todo-dashboard-widget' ); ?></strong> <span id="mtz-view-title"></span></div>
			<div class="mtz-view-line"><strong><?php esc_html_e( 'Description:', 'todo-dashboard-widget' ); ?></strong> <span id="mtz-view-description"></span></div>
			<div class="mtz-view-line" id="mtz-view-related-row" style="display:none;">
				<strong><?php esc_html_e( 'Related:', 'todo-dashboard-widget' ); ?></strong>
				<a href="#" target="_blank" rel="noopener noreferrer" id="mtz-view-related-link"></a>
			</div>
			<div class="mtz-view-line"><strong><?php esc_html_e( 'Assigned:', 'todo-dashboard-widget' ); ?></strong> <span id="mtz-view-assigned"></span></div>
			<div class="mtz-view-line"><strong><?php esc_html_e( 'Due:', 'todo-dashboard-widget' ); ?></strong> <span id="mtz-view-due"></span></div>
			<div class="mtz-view-line"><strong><?php esc_html_e( 'Priority:', 'todo-dashboard-widget' ); ?></strong> <span id="mtz-view-priority"></span></div>
			<div class="mtz-view-line"><strong><?php esc_html_e( 'Completed:', 'todo-dashboard-widget' ); ?></strong> <span id="mtz-view-completed-text"></span></div>
		</div>

		<div id="mtz-todo-modal" style="display:none;">
			<input type="hidden" id="mtz-task-id" />
			<input type="hidden" id="mtz-related-id" />

			<label for="mtz-title"><?php esc_html_e( 'Title', 'todo-dashboard-widget' ); ?></label>
			<input type="text" id="mtz-title" />

			<label for="mtz-description"><?php esc_html_e( 'Description', 'todo-dashboard-widget' ); ?></label>
			<textarea id="mtz-description"></textarea>

			<label for="mtz-related-search"><?php esc_html_e( 'Related page or post', 'todo-dashboard-widget' ); ?></label>
			<input type="text" id="mtz-related-search" placeholder="<?php esc_attr_e( 'Search posts or pages…', 'todo-dashboard-widget' ); ?>" />

			<label for="mtz-assigned-user"><?php esc_html_e( 'Assigned user', 'todo-dashboard-widget' ); ?></label>
			<select id="mtz-assigned-user">
				<?php foreach ( $users as $user ) : ?>
					<option value="<?php echo esc_attr( $user->ID ); ?>">
						<?php echo esc_html( $user->display_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<label for="mtz-due-date"><?php esc_html_e( 'Due date', 'todo-dashboard-widget' ); ?></label>
			<input type="date" id="mtz-due-date" />

			<label for="mtz-priority"><?php esc_html_e( 'Priority', 'todo-dashboard-widget' ); ?></label>
			<select id="mtz-priority">
				<option value="1"><?php esc_html_e( '1 (Highest)', 'todo-dashboard-widget' ); ?></option>
				<option value="2">2</option>
				<option value="3" selected>3</option>
				<option value="4">4</option>
				<option value="5"><?php esc_html_e( '5 (Lowest)', 'todo-dashboard-widget' ); ?></option>
			</select>

			<div class="mtz-checkbox-row">
				<input type="checkbox" id="mtz-completed" />
				<label for="mtz-completed"><?php esc_html_e( 'Completed', 'todo-dashboard-widget' ); ?></label>
			</div>
		</div>
		<?php
	}
}
