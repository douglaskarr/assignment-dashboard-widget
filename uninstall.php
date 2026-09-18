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

$post_type = 'mtztodo_task';

$ids = get_posts(
	array(
		'post_type'              => $post_type,
		'post_status'            => 'any',
		'numberposts'            => -1,
		'fields'                 => 'ids',
		'suppress_filters'       => true,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
	)
);

foreach ( $ids as $id ) {
	wp_delete_post( (int) $id, true );
}

// Direct cleanup for leftovers (revisions, orphaned meta, options, user meta).
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE post_type = %s", $post_type ) );

$like_meta = $wpdb->esc_like( '_mtz_' ) . '%';
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $like_meta ) );

$like_opt = $wpdb->esc_like( 'mtz_todo' ) . '%';
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like_opt ) );

$like_user = $wpdb->esc_like( 'mtz_todo' ) . '%';
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s", $like_user ) );
// phpcs:enable

wp_cache_flush();
