<?php
/**
 * Custom Table List for displaying Site Contacts
 *
 * Implements WP_List_Table to list Linkbuildr Site Contacts
 *
 * @link http://codex.wordpress.org/Class_Reference/WP_List_Table
 * @link http://wordpress.org/extend/plugins/custom-list-table-template/
 *
 * @package Linkbuildr
 * @since 1.0.0
 */

if ( ! class_exists( 'Linkbuildr_Ignored_Domains_Table' ) ) {

	if ( ! class_exists( 'WP_List_Table' ) ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	}

	/**
	 * Class used to display a list of Site Contacts
	 *
	 * An implementation of the WP_List_Table used to display Site Contacts from Linkbuildr.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_List_Table
	 */
	class Linkbuildr_Ignored_Domains_Table extends WP_List_Table {
		/**
		 * Stores the database table name for the site contacts.
		 *
		 * @since 1.0.0
		 * @var String $site_contacts_table_name Stores the database table name for the site contacts.
		 */
		protected static $ignored_domains_table_name;

		/**
		 * Stores the cache key for table items.
		 *
		 * @since 1.3.0
		 * @var String $table_items_cache_key Stores the cache key for table items.
		 */
		protected static $table_items_cache_key;

		/**
		 * Stores the cache key for table filtering and sorting values.
		 *
		 * @since 1.3.0
		 * @var String $table_filter_sort_cache_key Stores the cache key for table filtering and sorting values.
		 */
		protected static $table_filter_sort_cache_key;

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

			self::$ignored_domains_table_name  = $wpdb->base_prefix . 'linkbuildr_ignore_domains';
			self::$table_items_cache_key       = 'linkbuildr_ignored_domains_table_items';
			self::$table_filter_sort_cache_key = 'linkbuildr_ignored_domains_table_filter_sort';

			$args = wp_parse_args(
				$args,
				array(
					'singular' => 'ignored-domain',
					'plural'   => 'ignored-domains',
					'ajax'     => false,
					'screen'   => 'ignored-domains',
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
		 * Handling for the Domain column.
		 *
		 * @param Array $item - row (key, value array).
		 * @return HTML
		 */
		protected function column_domain( $item ) {
			$page  = $this->_args['screen'];
			$nonce = wp_create_nonce( 'bulk-' . $this->_args['plural'] );

			$actions = array(
				'delete' => sprintf( '<a href="?page=%s&action=delete&id=%s&_wpnonce=%s">%s</a>', $page, $item['id'], $nonce, __( 'Delete', 'linkbuildr' ) ),
			);

			return sprintf(
				'%s %s',
				$item['domain'],
				$this->row_actions( $actions )
			);
		}

		/**
		 * Checkbox column renders
		 *
		 * @param Array $item - row (key, value array).
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
				'cb'     => '<input type="checkbox" />', // Render a checkbox instead of text.
				'domain' => __( 'Domain', 'linkbuildr' ),
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
				'domain' => array( 'domain', false ),
			);
			return $sortable_columns;
		}

		/**
		 * Return array of bult actions if has any
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
			$ignored_domains_table_name = self::$ignored_domains_table_name;
			$table_items_cache_key      = self::$table_items_cache_key;

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
						$delete_from_ignored_domains_sql = "DELETE FROM $ignored_domains_table_name WHERE id IN(" . implode( ', ', array_fill( 0, count( $ids ), '%s' ) ) . ')';
						$wpdb->query( $wpdb->prepare( $delete_from_ignored_domains_sql, $ids ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
						wp_cache_delete( $table_items_cache_key );
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
			$ignored_domains_table_name  = self::$ignored_domains_table_name;
			$table_items_cache_key       = self::$table_items_cache_key;
			$table_filter_sort_cache_key = self::$table_filter_sort_cache_key;

			$per_page = 25; // constant, how much records will be shown per page.

			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();
			$primary  = 'domain';

			// here we configure table headers, defined in our methods.
			$this->_column_headers = array( $columns, $hidden, $sortable, $primary );

			// [OPTIONAL] process bulk action if any.
			$this->process_bulk_action();

			$filter_sort_args = array(
				'paged'   => 0,
				'orderby' => 'domain',
				'order'   => 'asc',
				'search'  => '',
			);

			if ( isset( $_REQUEST['paged'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$filter_sort_args['paged'] = max( 0, intval( wp_unslash( $_REQUEST['paged'] ) ) - 1 ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			if ( isset( $_REQUEST['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$orderby_val = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				if ( in_array( $orderby_val, array_keys( $this->get_sortable_columns() ), true ) ) {
					$filter_sort_args['orderby'] = $orderby_val;
				}
			}

			if ( isset( $_REQUEST['order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$order_val = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				if ( in_array( $order_val, array( 'asc', 'desc' ), true ) ) {
					$filter_sort_args['order'] = $order_val;
				}
			}

			if ( isset( $_REQUEST['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$search_val = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				if ( '' !== $search_val ) {
					$filter_sort_args['search'] = $search_val;
				}
			}

			$offsetter = $filter_sort_args['paged'] * $per_page;

			$_ignored_domains_table_filter_sort = wp_cache_get( $table_filter_sort_cache_key );
			if ( false === $_ignored_domains_table_filter_sort ) {
				$_ignored_domains_table_filter_sort = $filter_sort_args;
				wp_cache_set( $table_filter_sort_cache_key, $_ignored_domains_table_filter_sort );
			}

			if ( $_ignored_domains_table_filter_sort === $filter_sort_args ) {
				$_ignored_domains_table_items = wp_cache_get( $table_items_cache_key );
			} else {
				$_ignored_domains_table_items = false;
			}

			if ( false === $_ignored_domains_table_items ) {
				$orderby = $_ignored_domains_table_filter_sort['orderby'];
				$order   = $_ignored_domains_table_filter_sort['order'];
				$search  = $_ignored_domains_table_filter_sort['search'];

				if ( '' !== $search ) {
					$search_query                 = "domain LIKE '%%" . $search . "%%'";
					$_ignored_domains_table_items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
						$wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL
							"SELECT id, domain
							FROM $ignored_domains_table_name WHERE $search_query ORDER BY $orderby $order LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
							$per_page,
							$offsetter
						),
						ARRAY_A
					);
					// will be used in pagination settings.
					$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $ignored_domains_table_name WHERE domain LIKE '%$search%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
				} else {
					$_ignored_domains_table_items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
						$wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL
							"SELECT id, domain
							FROM $ignored_domains_table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
							$per_page,
							$offsetter
						),
						ARRAY_A
					);

					$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $ignored_domains_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
				}

				wp_cache_set( $table_items_cache_key, $_ignored_domains_table_items );
			}

			$this->items = $_ignored_domains_table_items;

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
