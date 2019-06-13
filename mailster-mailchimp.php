<?php
/*
Plugin Name: Mailchimp Importer for Mailster
Plugin URI: https://mailster.co/?utm_campaign=wporg&utm_source=Mailchimp
Description: Import your Lists from Mailchimp into WordPress and use it with the Mailster Newsletter Plugin for WordPress.
Version: 1.0.2
Author: EverPress
Author URI: https://everpress.co
Text Domain: mailster-mailchimp
License: GPLv2 or later
*/


define( 'MAILSTER_MAILCHIMP_VERSION', '1.0.2' );
define( 'MAILSTER_MAILCHIMP_FILE', __FILE__ );

require_once dirname( __FILE__ ) . '/classes/mailchimp.class.php';
new MailsterMailchimp();
