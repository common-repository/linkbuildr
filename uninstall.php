<?php
/**
 * Linkbuildr: uninstall
 *
 * Removes all database tables added by Linkbuildr before the plugin files are removed.
 *
 * @package Linkbuildr
 * @since 1.0.0
 */

// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

delete_option( 'LINKBUILDR_DB_VERSION' );
delete_option( 'linkbuildr_default_send_on_publish' );
delete_option( 'linkbuildr_show_notifications' );
delete_option( 'linkbuildr_unsubsribe_link_text' );
delete_option( 'linkbuildr_unsubsribe_message' );
delete_option( 'linkbuildr_unsubsribe_landing' );

global $wpdb;

$wpdb->query( 'SET foreign_key_checks = 0' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
$wpdb->query( "DELETE FROM {$wpdb->base_prefix}postmeta WHERE meta_key = 'linkbuildr_send_email_on_publish' OR meta_key = 'linkbuildr_not_new_post'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}linkbuildr_email_templates" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}linkbuildr_site_contacts" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}linkbuildr_posts_site_contacts" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}linkbuildr_ignore_domains" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
$wpdb->query( 'SET foreign_key_checks = 1' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
