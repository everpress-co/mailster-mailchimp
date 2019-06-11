<?php

class MailsterMailchimp {

	private $plugin_path;
	private $plugin_url;
	private $api;

	/**
	 *
	 */
	public function __construct() {

		$this->plugin_path = plugin_dir_path( MAILSTER_MAILCHIMP_FILE );
		$this->plugin_url = plugin_dir_url( MAILSTER_MAILCHIMP_FILE );

		register_activation_hook( MAILSTER_MAILCHIMP_FILE, array( &$this, 'activate' ) );
		register_deactivation_hook( MAILSTER_MAILCHIMP_FILE, array( &$this, 'deactivate' ) );

		load_plugin_textdomain( 'mailster-mailchimp' );

		add_action( 'plugins_loaded', array( &$this, 'init' ), 1 );

		add_action( 'wp_ajax_mailster_mailchimp_verify_api_key', array( &$this, 'api_request' ) );
		add_action( 'wp_ajax_mailster_mailchimp_lists', array( &$this, 'api_request' ) );
		add_action( 'wp_ajax_mailster_mailchimp_import_list', array( &$this, 'api_request' ) );

		add_action( 'load-newsletter_page_mailster_manage_subscribers', array( &$this, 'import_tab_notice' ) );

	}


	public function init() {

		if ( is_admin() ) {

			require_once $this->plugin_path . 'classes/merlin/vendor/autoload.php';
			require_once $this->plugin_path . 'classes/custom.merlin.class.php';
			require_once $this->plugin_path . 'classes/merlin-config.php';

		}

	}

	public function apikey() {

		if ( isset( $_POST['apikey'] ) ) {
			$apikey = sanitize_key( $_POST['apikey'] );
			update_option( 'mailster_mailchimp_apikey', $apikey );
		}

		return get_option( 'mailster_mailchimp_apikey' );

	}

	public function list( $id ) {

		$list = get_transient( 'mailster_mailchimp_list_' . $id );

		if ( ! $list ) {
			$list = $this->api()->list( $id );
			set_transient( 'mailster_mailchimp_list_' . $id, $list, HOUR_IN_SECONDS );
		}

		return $list;

	}

	public function api() {
		if ( ! $this->api ) {
			$this->api = new MailsterMailchimpAPI( $this->apikey() );
		}

		return $this->api;
	}

	public function api_request() {

		$endpoint = str_replace( 'wp_ajax_mailster_mailchimp_', '', current_filter() );

		require_once dirname( MAILSTER_MAILCHIMP_FILE ) . '/classes/api.class.php';

		switch ( $endpoint ) {
			case 'lists':
				$lists = $this->api()->lists();
				wp_send_json_success( array(
					'lists' => $lists,
				) );
				break;
			case 'import_list':

				if ( ! isset( $_POST['id'] ) ) {
					wp_send_json_error( array(
						'message' => 'no list',
					) );
				}

				$limit = isset( $_POST['limit'] ) ? (int) $_POST['limit'] : 100;
				$offset = isset( $_POST['offset'] ) ? (int) $_POST['offset'] : 0;
				$status = isset( $_POST['status'] ) ? (array) $_POST['status'] : array( 'subscribed' );

				$list_id = sanitize_key( $_POST['id'] );

				$mailster_list_id = $this->get_mailster_list_id( $list_id );

				$subscribers = $this->api()->members($list_id, array(
					'count' => $limit,
					'offset' => $offset,
					'status' => $status,
				));

				$this->import_subscribers( $subscribers, $list_id );

				wp_send_json_success( array(
					'total' => $this->api()->get_total_items( ),
					'added' => count( $subscribers ),
					'subscribers' => count( $subscribers ),
				) );

				break;
			case 'verify_api_key':
				$result = $this->api()->ping();
				if ( $result ) {
					wp_send_json_success( array(
						'message' => $result->health_status,
					) );
				}

				break;
		}

		wp_send_json_error();

	}


	public function import_subscribers( $subscribers, $list_id ) {
		foreach ( $subscribers as $subscriber ) {
			if ( ! $this->import_subscriber( $subscriber, $list_id ) ) {
				return false;
			}
		}

		return true;
	}


	public function import_subscriber( $subscriber, $list_id ) {

		global $wpdb;

		$stati = array( 'subscribed' => 1, 'unsubscribed' => 2, 'cleaned' => 3, 'pending' => 0, 'transactional' => 1 );

		$mailster_list_id = $this->get_mailster_list_id( $list_id );

		$entry = array(
			'email' => $subscriber->email_address,
			'hash' => md5( $subscriber->email_address ),
			'added' => $subscriber->timestamp_signup ? strtotime( $subscriber->timestamp_signup ) : time(),
			'status' => isset( $stati[ $subscriber->status ] ) ? $stati[ $subscriber->status ] : 1,
			'updated' => $subscriber->last_changed ? strtotime( $subscriber->last_changed ) : 0,
			'signup' => $subscriber->timestamp_signup ? strtotime( $subscriber->timestamp_signup ) : 0,
			'confirm' => $subscriber->timestamp_opt ? strtotime( $subscriber->timestamp_opt ) : 0,
			'ip_signup' => $subscriber->ip_signup,
			'ip_confirm' => $subscriber->ip_opt,
		);

		$custom_fields = array(
			'firstname' => $subscriber->merge_fields->FNAME,
			'lastname' => $subscriber->merge_fields->LNAME,
		);

		$meta = array(
			'lang' => $subscriber->language,
			'client' => $subscriber->email_client,
			'referer' => 'Mailchimp' . ( ! empty( $subscriber->source ) ? ' (' . $subscriber->source . ')' : null),
			'coords' => $subscriber->location->latitude ? $subscriber->location->latitude . '|' . $subscriber->location->longitude : null,
			'geo' => $subscriber->location->country_code ? $subscriber->location->country_code . '|' : null,
			'timeoffset' => $subscriber->location->gmtoff,
			'ip' => $subscriber->ip_opt,
		);

		$subscriber_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}mailster_subscribers WHERE email = %s", $subscriber->email_address ) );

		if ( $this->mailster() ) {

			if ( ! $subscriber_id ) {
				$subscriber_id = mailster( 'subscribers' )->add( $entry );
			}

			if ( is_wp_error( $subscriber_id ) ) {
				return false;
			}

			if ( $subscriber_id ) {
				mailster( 'subscribers' )->update_meta( $subscriber_id, 0, array_filter( $meta ) );
				mailster( 'subscribers' )->add_custom_value( $subscriber_id, array_filter( $custom_fields ) );
				mailster( 'subscribers' )->assign_lists( $subscriber_id, $mailster_list_id );
			}
		} else {

			$this->maybe_db_structure();

			if ( ! $subscriber_id ) {
				if ( false !== $wpdb->insert( "{$wpdb->prefix}mailster_subscribers", $entry ) ) {
					$subscriber_id = $wpdb->insert_id;
				}
			}

			if ( $subscriber_id ) {
				$wpdb->insert("{$wpdb->prefix}mailster_lists_subscribers", array(
					'list_id' => $mailster_list_id,
					'subscriber_id' => $subscriber_id,
					'added' => $entry['added'],
				));
				foreach ( $custom_fields as $key => $value ) {
					if ( empty( $value ) ) {
						continue;
					}
					$wpdb->insert("{$wpdb->prefix}mailster_subscriber_fields", array(
						'subscriber_id' => $subscriber_id,
						'meta_key' => $key,
						'meta_value' => $value,
					));
				}
				foreach ( $meta as $key => $value ) {
					if ( empty( $value ) ) {
						continue;
					}
					$wpdb->insert("{$wpdb->prefix}mailster_subscriber_meta", array(
						'subscriber_id' => $subscriber_id,
						'meta_key' => $key,
						'meta_value' => $value,
					));
				}
			}
		}

		return $subscriber_id;

	}


	public function get_mailster_list_id( $list_id, $create_if_not_exists = true ) {

		global $wpdb;
		$wpdb->suppress_errors();
		$list = $this->list( $list_id );

		$entry = array(
			'parent_id' => 0,
			'name' => $list->name,
			'slug' => sanitize_title( $list->name ),
			//'description' => 'Mailchimp Import',
			'added' => strtotime( $list->date_created ),
			'updated' => time(),
		);

		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}mailster_lists WHERE name = %s", $list->name ) );
		if ( $exists ) {
			return $exists;
		}

		if ( ! $create_if_not_exists ) {
			return false;
		}

		if ( $this->mailster() ) {

			$id = mailster( 'lists' )->add( $entry );
			if ( is_wp_error( $id ) ) {
				return false;
			}
		} else {

			$this->maybe_db_structure();

			$id = $wpdb->insert( "{$wpdb->prefix}mailster_lists", $entry );

		}

		return $id;

	}


	private function maybe_db_structure( $output = false, $execute = true, $set_charset = true, $hide_errors = true ) {

		global $wpdb;

		include $this->plugin_path . 'includes/db-structure.php';

		$return = '';

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$results = array();

		if ( $hide_errors ) {
			$wpdb->hide_errors();
		}

		foreach ( $tables as $tablequery ) {
			if ( $result = dbDelta( $tablequery, $execute ) ) {
				$results[] = array(
					'error' => $wpdb->last_error,
					'output' => implode( ', ', $result ),
				);
			}
		}

		foreach ( $results as $result ) {
			$return .= ( ! empty( $result['error'] ) ? $result['error'] . ' => ' : '') . $result['output'] . "\n";
		}
		if ( $output ) {
			echo $return;
		}

		return empty( $return ) ? true : $return;

	}


	public function import_tab_notice() {

		mailster_notice( sprintf( esc_html__( 'Import your Mailchimp subscribers %s.', 'mailster-mailchimp' ), '<a href="' . admin_url( 'tools.php?page=mailster_mailchimp' ) . '">' . esc_html__( 'here', 'mailster-mailchimp' ) . '</a>' ), 'error', true );
	}


	private function mailster() {

		return function_exists( 'mailster' );
	}

	/**
	 *
	 *
	 * @param unknown $network_wide
	 */
	public function activate( $network_wide ) {

		if ( $this->mailster() ) {

		}
	}


	/**
	 *
	 *
	 * @param unknown $network_wide
	 */
	public function deactivate( $network_wide ) {

		if ( $this->mailster() ) {

		}

	}



}
