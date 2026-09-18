<?php
/**
 * Plugin Name: Dashboard To-Do List
 * Plugin URI: https://martech.zone/wordpress-plugin-to-do-widget/
 * Description: A dashboard to-do list with assignments, priorities, due dates, related posts, and completion tracking. Lives on the WordPress dashboard.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Douglas Karr
 * Author URI: https://dknewmedia.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dashboard-todo-list-widget
 *
 * @package WordPressDashboardToDoList
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MTZ_TODO_VERSION', '1.0.0' );
define( 'MTZ_TODO_PATH', plugin_dir_path( __FILE__ ) );
define( 'MTZ_TODO_URL', plugin_dir_url( __FILE__ ) );
define( 'MTZ_TODO_CPT', 'mtztodo_task' );

require_once MTZ_TODO_PATH . 'includes/class-mtz-todo-cpt.php';
require_once MTZ_TODO_PATH . 'includes/class-mtz-todo-dashboard.php';
require_once MTZ_TODO_PATH . 'includes/class-mtz-todo-ajax.php';
require_once MTZ_TODO_PATH . 'includes/class-mtz-todo-adminbar.php';

new MTZ_Todo_CPT();
new MTZ_Todo_Dashboard();
new MTZ_Todo_Ajax();
new MTZ_Todo_AdminBar();
