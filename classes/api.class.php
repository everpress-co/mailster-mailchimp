<?php

class MailsterMailchimpAPI {

	private $domain = 'api.mailchimp.com';
	private $version = '3.0';
	private $total_items = null;

	public function __construct( $apikey ) {

		$this->apikey = $apikey;
		$this->dc = preg_replace( '/^([a-f0-9]+)-([a-z0-9]+)$/', '$2', $apikey );
		$this->url = trailingslashit( 'https://' . $this->dc . '.' . $this->domain . '/' . $this->version );

	}

	public function call( $action, $args = array() ) {

		$response = $this->get( $action, $args );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'error' => $response->get_error_message() ), $response->get_error_code() );
		}

		return $response;

	}

	public function get_total_items() {
		return $this->total_items;
	}

	public function ping() {
		return $this->call( 'ping' );
	}

	public function lists( $args = array() ) {
		$result = $this->call( 'lists', $args );
		return isset( $result->lists ) ? $result->lists : array();
	}

	public function list( $list_id, $args = array() ) {
		return $this->call( 'lists/' . $list_id, $args );
	}

	public function members( $list_id, $args = array() ) {
		$result = $this->call( 'lists/' . $list_id . '/members', $args );
		return isset( $result->members ) ? $result->members : array();
	}

	public function get( $action, $args = array(), $timeout = 15 ) {
		return $this->do_call( 'GET', $action, $args, $timeout );
	}
	public function post( $action, $args = array(), $timeout = 15 ) {
		return $this->do_call( 'POST', $action, $args, $timeout );
	}


	/**
	 *
	 * @access public
	 * @param unknown $apikey  (optional)
	 * @return void
	 */
	private function do_call( $method, $action, $args = array(), $timeout = 15 ) {

		$url = $this->url . $action;

		$headers = array(
			'Authorization' => 'apikey ' . $this->apikey,
		);

		$body = null;

		if ( 'GET' == $method ) {
			$url = add_query_arg( $args, $url );
		} elseif ( 'POST' == $method ) {
			$body = $args;
		} else {
			return new WP_Error( 'method_not_allowed', 'This method is not allowed' );
		}

		$this->total_items = null;
		$response = wp_remote_request( $url, array(
			'method' => $method,
			'headers' => $headers,
			'timeout' => $timeout,
			'body' => $body,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 200 != $code ) {

			return new WP_Error( $body->status, $body->title . ': ' . $body->detail, $body );

		}

		if ( isset( $body->total_items ) ) {
			$this->total_items = $body->total_items;
		}

		return $body;

	}

}
