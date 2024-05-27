<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MailsterImportMailchimp extends MailsterImport {



	protected $type = 'mailchimp';
	protected $name = 'Mailchimp';

	private $api;

	function init() {
	}

	public function api() {
		if ( ! $this->api ) {
			$data      = $this->get_credentials();
			$this->api = $this->get_api_class( $data['apikey'] );
		}

		return $this->api;
	}


	function get_lists( $statuses = null ) {

		$lists = array();

		$api_result = $this->api()->lists(
			array(
				'count'                  => 1000,
				'include_total_contacts' => true,
			)
		);

		foreach ( $api_result as $list ) {

			$total = 0;

			if ( is_null( $statuses ) ) {
				$total = $list->stats->member_count;
			} else {
				foreach ( $statuses as $status ) {
					switch ( $status ) {
						case 'unsubscribed':
							$total += $list->stats->{'unsubscribe_count'};
							break;
						case 'cleaned':
							$total += $list->stats->{'cleaned_count'};
							break;
						case 'subscribed':
							$total += $list->stats->{'member_count'};
							break;
					}
				}
			}

			$lists[ $list->id ] = array(
				'id'    => $list->id,
				'name'  => $list->name,
				'total' => $total,
			);
		}

		return $lists;
	}

	function get_statuses() {

		$statuses = array(
			'pending'       => 'pending',
			'subscribed'    => 'subscribed',
			'unsubscribed'  => 'unsubscribed',
			'transactional' => 'subscribed',
			'cleaned'       => 'hardbounced',
		// 'archived'      => 'deleted',
		);

		$return = array();

		foreach ( $statuses as $name => $status ) {
			$return[ $name ] = array(
				'id'     => $name,
				'name'   => $name,
				'mapped' => $status,
			);
		}
		return $return;
	}

	public function valid_credentials() {

		if ( $this->get_credentials() ) {
			return true;
		}

		parse_str( $_POST['data'], $data );

		$response = $this->get_api_class( $data['apikey'] )->ping();

		if ( is_wp_error( $response ) ) {

			return $response;
		}

		$this->update_credentials( $data, DAY_IN_SECONDS );

		$return['html'] = $this->get_import_options();
		wp_send_json_success( $return );
		exit;
	}


	public function get_import_part( &$import_data ) {

		$list_id  = $import_data['extra']['selected_lists'][ $import_data['extra']['current_list'] ];
		$statuses = $import_data['extra']['selected_statuses'];
		$offset   = $import_data['extra']['offset'];
		$limit    = $import_data['performance'] ? 20 : 250;

		$api_result = $this->api()->members(
			$list_id,
			array(
				'offset' => $offset,
				'count'  => $limit,
				'status' => implode( ',', $statuses ),
			)
		);

		if ( is_wp_error( $api_result ) ) {
			return $api_result;
		}

		$count = count( $api_result->members );

		$data     = array();
		$lists    = $this->get_lists( $statuses );
		$listname = $lists[ $list_id ]['name'];

		foreach ( $api_result->members as $entry ) {
			$e      = $this->map_entry( $entry, $listname );
			$data[] = array_values( $e );
		}

		$import_data['extra']['offset'] += $limit;
		if ( $count < $limit && isset( $import_data['extra']['selected_lists'][ $import_data['extra']['current_list'] + 1 ] ) ) {
			$import_data['extra']['offset'] = 0;
			++$import_data['extra']['current_list'];
		}

		return $data;
	}


	public function get_import_data() {

		parse_str( $_POST['data'], $import_options );

		$sample_size = 10;
		$total       = 0;

		$col_count = 0;

		$data = array();

		if ( ! empty( $import_options['lists'] ) ) {

			$lists = $this->get_lists( $import_options['statuses'] );

			// for each selected list
			foreach ( $import_options['lists'] as $list_id ) {

				$listname = $lists[ $list_id ]['name'];
				$total   += $lists[ $list_id ]['total'];

				// get two members as sample
				$api_result = $this->api()->members(
					$list_id,
					array(
						'count'  => ceil( $sample_size / count( $import_options['lists'] ) ),
						'status' => implode( ',', $import_options['statuses'] ),
					)
				);

				if ( is_wp_error( $api_result ) ) {
					return $api_result;
				}

				foreach ( $api_result->members as $entry ) {
					$e     = $this->map_entry( $entry, $listname );
					$count = count( $e );
					if ( $count > $col_count ) {
						$header    = array_keys( $e );
						$col_count = max( $col_count, $count );

					}
					$data[] = $e;
				}
			}
		}

		$header_array = array();
		foreach ( $header as $key => $value ) {
			$header_array[ $value ] = preg_replace( '/^_(.*)/', '$1', $value );
		}
		return array(
			'total'    => $total,
			'removed'  => null,
			'header'   => $header_array,
			'sample'   => $data,
			'extra'    => array(
				'current_list'      => 0,
				'offset'            => 0,
				'selected_lists'    => array_values( $import_options['lists'] ),
				'selected_statuses' => array_values( $import_options['statuses'] ),
			),
			'defaults' => array(
				'existing' => 'merge',
			),
		);
	}

	private function map_entry( $entry, $listnames ) {

		$maped = array();

		$maped['email']    = $entry->email_address;
		$maped['fullname'] = $entry->full_name;
		$maped['_lists']   = $listnames;

		foreach ( $entry->merge_fields as $merge_tag => $value ) {
			if ( is_object( $value ) ) {
				$maped[ '_merge_field_' . $merge_tag ] = implode( ' ', (array) $value );
			} else {
				$maped[ '_merge_field_' . $merge_tag ] = $value;
			}
		}
		$maped['_status']     = $this->map_status( $entry->status );
		$maped['_ip_signup']  = $entry->ip_signup;
		$maped['_signup']     = $entry->timestamp_signup;
		$maped['_ip_confirm'] = $entry->ip_opt;
		$maped['_confirm']    = $entry->timestamp_opt;
		$maped['_lang']       = $entry->language;

		$maped['_tags']       = implode( ',', wp_list_pluck( $entry->tags, 'name' ) );
		$maped['_lat']        = $entry->location->latitude ? $entry->location->latitude : null;
		$maped['_long']       = $entry->location->longitude ? $entry->location->longitude : null;
		$maped['_country']    = $entry->location->country_code;
		$maped['_timeoffset'] = $entry->location->gmtoff;
		$maped['_timezone']   = $entry->location->timezone;

		return $maped;
	}

	private function map_status( $org_status ) {

		$statuses = $this->get_statuses();
		return isset( $statuses[ $org_status ] ) ? $statuses[ $org_status ]['mapped'] : null;
	}

	protected function credentials_form() {

		?>
			<p><label><?php esc_html_e( 'Enter your Mailchimp API Key.' ); ?></label></p>
			<input type="text" name="apikey" value="" class="widefat regular-text" autocomplete="off">
			<p class="howto"><?php printf( esc_html__( 'You can find your API Key %s.', 'mailster-mailchimp' ), '<a href="https://us2.admin.mailchimp.com/account/api-key-popup/" class="external">' . esc_html__( 'here', 'mailster-mailchimp' ) . '</a>' ); ?> <?php esc_html_e( 'Mailster will store this key for 24 hours.' ); ?></p>

		<?php
	}

	private function get_api_class( $apikey = null ) {
		include_once 'api.class.php';
		return new MailsterMailchimpAPI( $apikey );
	}

	public function filter( $insert, $data, $import_data ) {

		$insert['referer'] = 'mailchimp';
		return $insert;
	}
}
