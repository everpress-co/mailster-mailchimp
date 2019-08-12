<?php
/**
 * Merlin WP configuration file.
 *
 * @package   Merlin WP
 * @version   @@pkg.version
 * @link      https://merlinwp.com/
 * @author    Rich Tabor, from ThemeBeans.com & the team at ProteusThemes.com
 * @copyright Copyright (c) 2018, Merlin WP of Inventionn LLC
 * @license   Licensed GPLv3 for Open Source Use
 */

if ( ! class_exists( 'MailsterMerlin' ) ) {
	return;
}

/**
 * Set directory locations, text strings, and settings.
 */

$wizard = new MailsterMerlin(

	$config  = array(
		'directory'            => '../../plugins/mailster-mailchimp/classes/merlin', // Location / directory where Merlin WP is placed in your theme.
		'plugin_path'          => $this->plugin_path, // Location / directory where Merlin WP is placed in your theme.
		'plugin_url'           => $this->plugin_url, // Location / directory where Merlin WP is placed in your theme.
		'merlin_url'           => 'mailster_mailchimp', // The wp-admin page slug where Merlin WP loads.
		'parent_slug'          => 'tools.php', // The wp-admin parent page slug for the admin menu item.
		'capability'           => 'manage_options', // The capability required for this menu to be displayed to the user.
		'dev_mode'             => defined( 'WP_DEBUG' ) ? WP_DEBUG : false, // Enable development mode for testing.
		'license_step'         => false, // EDD license activation step.
		'license_required'     => false, // Require the license activation step.
		'license_help_url'     => '', // URL for the 'license-tooltip'.
		'edd_remote_api_url'   => '', // EDD_Theme_Updater_Admin remote_api_url.
		'edd_item_name'        => '', // EDD_Theme_Updater_Admin item_name.
		'edd_theme_slug'       => '', // EDD_Theme_Updater_Admin item_slug.
		'ready_big_button_url' => function_exists( 'mailster' ) ? admin_url( 'admin.php?page=mailster_dashboard' ) : 'https://mailster.co/?utm_campaign=wporg&utm_source=Mailchimp&utm_term=big+button', // Link for the big button on the ready step.
	),
	$strings = array(
		'admin-menu'               => esc_html__( 'Mailchimp Import', 'mailster-mailchimp' ),

		/* translators: 1: Title Tag 2: Theme Name 3: Closing Title Tag */
		'title%s%s%s%s'            => esc_html__( '%1$s%2$s Tools &lsaquo; Mailchimp Setup: %3$s%4$s', 'mailster-mailchimp' ),
		'return-to-dashboard'      => esc_html__( 'Return to the dashboard', 'mailster-mailchimp' ),
		'ignore'                   => '',

		'btn-skip'                 => esc_html__( 'Skip', 'mailster-mailchimp' ),
		'btn-next'                 => esc_html__( 'Next', 'mailster-mailchimp' ),
		'btn-start'                => esc_html__( 'Start', 'mailster-mailchimp' ),
		'btn-no'                   => esc_html__( 'Cancel', 'mailster-mailchimp' ),
		'btn-plugins-install'      => esc_html__( 'Install', 'mailster-mailchimp' ),
		'btn-child-install'        => esc_html__( 'Install', 'mailster-mailchimp' ),
		'btn-content-install'      => esc_html__( 'Install', 'mailster-mailchimp' ),
		'btn-import'               => esc_html__( 'Import', 'mailster-mailchimp' ),
		'btn-license-activate'     => esc_html__( 'Activate', 'mailster-mailchimp' ),
		'btn-license-skip'         => esc_html__( 'Later', 'mailster-mailchimp' ),

		/* translators: Theme Name */
		'license-header%s'         => esc_html__( 'Activate %s', 'mailster-mailchimp' ),
		/* translators: Theme Name */
		'license-header-success%s' => esc_html__( '%s is Activated', 'mailster-mailchimp' ),
		/* translators: Theme Name */
		'license%s'                => esc_html__( 'Enter your license key to enable remote updates and theme support.', 'mailster-mailchimp' ),
		'license-label'            => esc_html__( 'License key', 'mailster-mailchimp' ),
		'license-success%s'        => esc_html__( 'The theme is already registered, so you can go to the next step!', 'mailster-mailchimp' ),
		'license-json-success%s'   => esc_html__( 'Your theme is activated! Remote updates and theme support are enabled.', 'mailster-mailchimp' ),
		'license-tooltip'          => esc_html__( 'Need help?', 'mailster-mailchimp' ),

		/* translators: Theme Name */
		'welcome-header%s'         => esc_html__( 'Welcome to the Mailchimp Importer for Mailster', 'mailster-mailchimp' ),
		'welcome-header-success%s' => esc_html__( 'Hi. Welcome back', 'mailster-mailchimp' ),
		'welcome%s'                => esc_html__( 'This wizard will import your lists and subscribers from Mailchimp for Mailster and should take only a few minutes.', 'mailster-mailchimp' ),
		'welcome-success%s'        => esc_html__( 'You may have already run this wizard. If you would like to proceed anyway, click on the "Start" button below.', 'mailster-mailchimp' ),

		'ready-header'             => esc_html__( 'All done. Have fun!', 'mailster-mailchimp' ),

		/* translators: Theme Author */
		'ready%s'                  => esc_html__( 'Your lists have been imported.', 'mailster-mailchimp' ),
		'ready-action-link'        => esc_html__( 'Extras', 'mailster-mailchimp' ),
		'ready-big-button'         => function_exists( 'mailster' ) ? esc_html__( 'Back to Mailster Dashboard', 'mailster-mailchimp' ) : esc_html__( 'Get Mailster now!', 'mailster-mailchimp' ),
		'ready-link-1'             => sprintf( '<a href="%1$s" target="_blank">%2$s</a>', 'https://mailster.co/go/buy', esc_html__( 'Buy Mailster', 'mailster-mailchimp' ) ),
		'ready-link-2'             => sprintf( '<a href="%1$s" target="_blank">%2$s</a>', 'https://mailster.co/support', esc_html__( 'Get Support', 'mailster-mailchimp' ) ),
		'rseady-link-3'            => sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'customize.php' ), esc_html__( 'Start Customizing', 'mailster-mailchimp' ) ),
	)
);
