<?php
/**
 * Remove every piece of data this plugin stored.
 *
 * Runs only when the plugin is deleted from Plugins → Installed Plugins,
 * not on deactivate.
 *
 * @package WordPressDashboardToDoList
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$mtz_todo_post_type = 'mtztodo_task';

$mtz_todo_ids = get_posts(
	array(
		'post_type'           => $mtz_todo_post_type,
		'post_status'         => 'any',
		'numberposts'         => -1,
		'fields'              => 'ids',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

foreach ( $mtz_todo_ids as $mtz_todo_id ) {
	wp_delete_post( (int) $mtz_todo_id, true );
}

// Direct cleanup for leftovers (revisions, orphaned meta, options, user meta).
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE post_type = %s", $mtz_todo_post_type ) );

$mtz_todo_like_meta = $wpdb->esc_like( '_mtz_' ) . '%';
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $mtz_todo_like_meta ) );

$mtz_todo_like_opt = $wpdb->esc_like( 'mtz_todo' ) . '%';
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $mtz_todo_like_opt ) );

$mtz_todo_like_user = $wpdb->esc_like( 'mtz_todo' ) . '%';
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s", $mtz_todo_like_user ) );
// phpcs:enable

wp_cache_flush();
