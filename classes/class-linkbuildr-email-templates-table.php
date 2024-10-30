<?php
/**
 * Custom Table List for displaying Email Templates
 *
 * Implements WP_List_Table to list Linkbuildr Email Templates
 *
 * @link http://codex.wordpress.org/Class_Reference/WP_List_Table
 * @link http://wordpress.org/extend/plugins/custom-list-table-template/
 *
 * @package Linkbuildr
 * @since 1.0.0
 */

if ( ! class_exists( 'Linkbuildr_Email_Templates_Table' ) ) {

	if ( ! class_exists( 'WP_List_Table' ) ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	}

	/**
	 * Class used to display a list of Email Templates.
	 *
	 * An implementation of the WP_List_Table used to display Email Templates from Linkbuildr.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_List_Table
	 */
	class Linkbuildr_Email_Templates_Table extends WP_List_Table {
		/**
		 * Stores the database table name for the email templates.
		 *
		 * @since 1.0.0
		 * @var String $email_templates_table_name Stores the database table name for the email templates.
		 */
		protected static $email_templates_table_name;

		/**
		 * Stores the database table name for the site contacts.
		 *
		 * @since 1.0.0
		 * @var String $siteContactsTableName Stores the database table name for the site contacts.
		 */
		protected static $site_contacts_table_name;


		/**
		 * Declare constructor and give some basic params.
		 *
		 * @param array|string $args {
		 *     Array or string of arguments.
		 *
		 *     @type string $plural   Plural value used for labels and the objects being listed.
		 *                            This affects things such as CSS class-names and nonces used
		 *                            in the list table, e.g. 'posts'. Default empty.
		 *     @type string $singular Singular label for an object being listed, e.g. 'post'.
		 *                            Default empty
		 *     @type bool   $ajax     Whether the list table supports Ajax. This includes loading
		 *                            and sorting data, for example. If true, the class will call
		 *                            the _js_vars() method in the footer to provide variables
		 *                            to any scripts handling Ajax events. Default false.
		 *     @type string $screen   String containing the hook name used to determine the current
		 *                            screen. If left null, the current screen will be automatically set.
		 *                            Default null.
		 * }
		 */
		public function __construct( $args = array() ) {
			global $status, $page;

			global $wpdb;

			self::$email_templates_table_name = $wpdb->base_prefix . 'linkbuildr_email_templates';
			self::$site_contacts_table_name   = $wpdb->base_prefix . 'linkbuildr_site_contacts';

			$args = wp_parse_args(
				$args,
				array(
					'singular' => 'email-template',
					'plural'   => 'email-templates',
					'ajax'     => false,
					'screen'   => 'email-templates',
				)
			);

			parent::__construct( $args );
		}

		/**
		 * Default column renderer
		 *
		 * @param Array  $item - row (key, value array).
		 * @param String $column_name - string (key).
		 * @return HTML
		 */
		protected function column_default( $item, $column_name ) {
			return $item[ $column_name ];
		}

		/**
		 * Handling for the Template Name column.
		 *
		 * @param Array $item - row (key, value array).
		 * @return HTML
		 */
		protected function column_templatename( $item ) {
			$page  = $this->_args['screen'];
			$nonce = wp_create_nonce( 'bulk-' . $this->_args['plural'] );

			$actions = array(
				'edit'   => sprintf( '<a href="?page=email-template-form&id=%s">%s</a>', $item['id'], __( 'Edit', 'linkbuildr' ) ),
				'delete' => sprintf( '<a href="?page=%s&action=delete&id=%s&_wpnonce=%s">%s</a>', $page, $item['id'], $nonce, __( 'Delete', 'linkbuildr' ) ),
			);

			return sprintf(
				'%s %s',
				$item['templatename'],
				$this->row_actions( $actions )
			);
		}

		/**
		 * Checkbox column renders.
		 *
		 * @param Array $item row (key, value array).
		 * @return HTML
		 */
		protected function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="id[]" value="%s" />',
				$item['id']
			);
		}

		/**
		 * Returns an Array of all the columns that should be visible in the table.
		 *
		 * @return Array
		 */
		public function get_columns() {
			$columns = array(
				'cb'           => '<input type="checkbox" />', // Render a checkbox instead of text.
				'templatename' => __( 'Template Name', 'linkbuildr' ),
				'sender'       => __( 'Sender', 'linkbuildr' ),
				'subject'      => __( 'Subject', 'linkbuildr' ),
				'content'      => __( 'Content', 'linkbuildr' ),
				'tweet'        => __( 'Tweet', 'linkbuildr' ),
			);
			return $columns;
		}

		/**
		 * Returns columns that may be used to sort table.
		 * All strings in array are column names.
		 *
		 * @return Array
		 */
		protected function get_sortable_columns() {
			$sortable_columns = array(
				'templatename' => array( 'templatename', false ),
				'sender'       => array( 'sender', false ),
				'subject'      => array( 'subject', false ),
				'content'      => array( 'content', false ),
				'tweet'        => array( 'tweet', false ),
			);
			return $sortable_columns;
		}

		/**
		 * Return array of bulk actions if any are applicable.
		 *
		 * @return Array
		 */
		protected function get_bulk_actions() {
			$actions = array(
				'delete' => 'Delete',
			);
			return $actions;
		}

		/**
		 * This method processes bulk actions.
		 * Delete is the only bulk action available in this context.
		 *
		 * @global Object $wpdb
		 */
		protected function process_bulk_action() {
			global $wpdb;

			$email_templates_table_name_local = self::$email_templates_table_name;
			$site_contacts_table_name_local   = self::$site_contacts_table_name;

			if ( 'delete' === $this->current_action() ) {
				$ids = array();

				if (
					isset( $_REQUEST['id'], $_REQUEST['_wpnonce'] )
					&& wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'bulk-' . $this->_args['plural'] )
				) {
					if ( isset( $_REQUEST['table'] ) ) {
						$table = sanitize_text_field( wp_unslash( $_REQUEST['table'] ) );

						if ( $table !== $this->_args['singular'] ) {
							return;
						}
					}

					$id_vals = wp_unslash( $_REQUEST['id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					if ( ! is_array( $id_vals ) ) {
						$ids = array( sanitize_text_field( $id_vals ) );
					} else {
						$ids = array_map( 'sanitize_text_field', $id_vals );
					}

					if ( ! empty( $ids ) ) {
						$set_email_template_in_site_contacts_sql = "UPDATE $site_contacts_table_name_local SET email_template_id = NULL WHERE email_template_id IN(" . implode( ', ', array_fill( 0, count( $ids ), '%s' ) ) . ')';
						$wpdb->query( $wpdb->prepare( $set_email_template_in_site_contacts_sql, $ids ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

						$delete_email_templates_sql = "DELETE FROM $email_templates_table_name_local WHERE id IN(" . implode( ', ', array_fill( 0, count( $ids ), '%s' ) ) . ')';
						$wpdb->query( $wpdb->prepare( $delete_email_templates_sql, $ids ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
					}
				}
			}
		}

		/**
		 * This function gets rows from database and prepares them to be showed in table.
		 *
		 * @global Object $wpdb
		 */
		public function prepare_items() {
			global $wpdb;
			$email_templates_table_name_local = self::$email_templates_table_name;

			$per_page = 25; // constant, how much records will be shown per page.

			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();

			// here we configure table headers, defined in our methods.
			$this->_column_headers = array( $columns, $hidden, $sortable );

			// process bulk action if any.
			$this->process_bulk_action();

			// will be used in pagination settings.
			$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $email_templates_table_name_local" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

			$paged   = 0;
			$orderby = 'id';
			$order   = 'desc';

			if ( isset( $_REQUEST['paged'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$paged = max( 0, intval( wp_unslash( $_REQUEST['paged'] ) ) - 1 ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			if ( isset( $_REQUEST['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$orderby_val = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				if ( in_array( $orderby_val, array_keys( $this->get_sortable_columns() ), true ) ) {
					$orderby = $orderby_val;
				}
			}

			if ( isset( $_REQUEST['order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$order_val = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				if ( in_array( $order_val, array( 'asc', 'desc' ), true ) ) {
					$order = $order_val;
				}
			}

			$offsetter = $paged * $per_page;

			// define $items array.
			$cache_key                   = 'email_template_table_items';
			$_email_template_table_items = wp_cache_get( $cache_key );
			if ( false === $_email_template_table_items ) {
				$_email_template_table_items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $email_templates_table_name_local ORDER BY $orderby $order LIMIT %d OFFSET %d", array( $per_page, $offsetter ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
				wp_cache_set( $cache_key, $_email_template_table_items );
			}

			$this->items = $_email_template_table_items;

			// configure pagination.
			$this->set_pagination_args(
				array(
					'total_items' => $total_items, // total items defined above.
					'per_page'    => $per_page, // per page constant defined at top of method.
					'total_pages' => ceil( $total_items / $per_page ), // calculate pages count.
				)
			);
		}
	}
}
