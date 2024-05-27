<?php
/*
Plugin Name: Mailchimp Importer for Mailster
Requires Plugins: mailster
Plugin URI: https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=plugin&utm_term=Mailchimp
Description: Import your Lists from Mailchimp into WordPress and use it with the Mailster Newsletter Plugin for WordPress.
Version: 2.0.1
Author: EverPress
Author URI: https://everpress.co
Text Domain: mailster-mailchimp
License: GPLv2 or later
*/


define( 'MAILSTER_MAILCHIMP_VERSION', '2.0.1' );
define( 'MAILSTER_MAILCHIMP_FILE', __FILE__ );

require_once __DIR__ . '/classes/mailchimp.class.php';
new MailsterMailchimp();
