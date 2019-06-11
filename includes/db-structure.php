<?php
global $wpdb;

$collate = '';

if ( $set_charset && $wpdb->has_cap( 'collation' ) ) {
	$collate = $wpdb->get_charset_collate();
}

$tables = apply_filters( 'mailster_table_structure', array(

	"CREATE TABLE {$wpdb->prefix}mailster_subscribers (
        `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `hash` varchar(32) NOT NULL,
        `email` varchar(191) NOT NULL,
        `wp_id` bigint(20) unsigned NOT NULL DEFAULT 0,
        `status` int(11) unsigned NOT NULL DEFAULT 0,
        `added` int(11) unsigned NOT NULL DEFAULT 0,
        `updated` int(11) unsigned NOT NULL DEFAULT 0,
        `signup` int(11) unsigned NOT NULL DEFAULT 0,
        `confirm` int(11) unsigned NOT NULL DEFAULT 0,
        `ip_signup` varchar(45) NOT NULL DEFAULT '',
        `ip_confirm` varchar(45) NOT NULL DEFAULT '',
        `rating` decimal(3,2) unsigned NOT NULL DEFAULT 0.25,
        PRIMARY KEY  (`ID`),
        UNIQUE KEY `email` (`email`),
        UNIQUE KEY `hash` (`hash`),
        KEY `wp_id` (`wp_id`),
        KEY `status` (`status`),
        KEY `rating` (`rating`)
    ) $collate;",

	"CREATE TABLE {$wpdb->prefix}mailster_subscriber_fields (
        `subscriber_id` bigint(20) unsigned NOT NULL,
        `meta_key` varchar(191) NOT NULL,
        `meta_value` longtext NOT NULL,
        UNIQUE KEY `id` (`subscriber_id`,`meta_key`),
        KEY `subscriber_id` (`subscriber_id`),
        KEY `meta_key` (`meta_key`)
    ) $collate;",

	"CREATE TABLE {$wpdb->prefix}mailster_subscriber_meta (
        `subscriber_id` bigint(20) unsigned NOT NULL,
        `campaign_id` bigint(20) unsigned NOT NULL,
        `meta_key` varchar(191) NOT NULL,
        `meta_value` longtext NOT NULL,
        UNIQUE KEY `id` (`subscriber_id`,`campaign_id`,`meta_key`),
        KEY `subscriber_id` (`subscriber_id`),
        KEY `campaign_id` (`campaign_id`),
        KEY `meta_key` (`meta_key`)
    ) $collate;",

	"CREATE TABLE {$wpdb->prefix}mailster_queue (
        `subscriber_id` bigint(20) unsigned NOT NULL DEFAULT 0,
        `campaign_id` bigint(20) unsigned NOT NULL DEFAULT 0,
        `requeued` tinyint(1) unsigned NOT NULL DEFAULT 0,
        `added` int(11) unsigned NOT NULL DEFAULT 0,
        `timestamp` int(11) unsigned NOT NULL DEFAULT 0,
        `sent` int(11) unsigned NOT NULL DEFAULT 0,
        `priority` tinyint(1) unsigned NOT NULL DEFAULT 0,
        `count` tinyint(1) unsigned NOT NULL DEFAULT 0,
        `error` tinyint(1) unsigned NOT NULL DEFAULT 0,
        `ignore_status` tinyint(1) unsigned NOT NULL DEFAULT 0,
        `options` varchar(191) NOT NULL DEFAULT '',
        `tags` longtext NOT NULL,
        UNIQUE KEY `id` (`subscriber_id`,`campaign_id`,`requeued`,`options`),
        KEY `subscriber_id` (`subscriber_id`),
        KEY `campaign_id` (`campaign_id`),
        KEY `requeued` (`requeued`),
        KEY `timestamp` (`timestamp`),
        KEY `priority` (`priority`),
        KEY `count` (`count`),
        KEY `error` (`error`),
        KEY `ignore_status` (`ignore_status`)
    ) $collate;",

	"CREATE TABLE {$wpdb->prefix}mailster_actions (
        `subscriber_id` bigint(20) unsigned NULL DEFAULT NULL,
        `campaign_id` bigint(20) unsigned NULL DEFAULT NULL,
        `timestamp` int(11) unsigned NOT NULL DEFAULT 0,
        `count` int(11) unsigned NOT NULL DEFAULT 0,
        `type` tinyint(1) NOT NULL DEFAULT 0,
        `link_id` bigint(20) unsigned NOT NULL DEFAULT 0,
        UNIQUE KEY `id` (`subscriber_id`,`campaign_id`,`type`,`link_id`),
        KEY `subscriber_id` (`subscriber_id`),
        KEY `campaign_id` (`campaign_id`),
        KEY `type` (`type`)
    ) $collate;",

	"CREATE TABLE {$wpdb->prefix}mailster_links (
        `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `link` varchar(2083) NOT NULL,
        `i` tinyint(1) unsigned NOT NULL,
        PRIMARY KEY  (`ID`)
    ) $collate;",

	"CREATE TABLE {$wpdb->prefix}mailster_lists (
        `ID` bigint(20) NOT NULL AUTO_INCREMENT,
        `parent_id` bigint(20) unsigned NOT NULL,
        `name` varchar(191) NOT NULL,
        `slug` varchar(191) NOT NULL,
        `description` longtext NOT NULL,
        `added` int(11) unsigned NOT NULL,
        `updated` int(11) unsigned NOT NULL,
        PRIMARY KEY  (`ID`),
        UNIQUE KEY `name` (`name`),
        UNIQUE KEY `slug` (`slug`)
    ) $collate;",

	"CREATE TABLE {$wpdb->prefix}mailster_lists_subscribers (
        `list_id` bigint(20) unsigned NOT NULL,
        `subscriber_id` bigint(20) unsigned NOT NULL,
        `added` int(11) unsigned NOT NULL,
        UNIQUE KEY `id` (`list_id`,`subscriber_id`),
        KEY `list_id` (`list_id`),
        KEY `subscriber_id` (`subscriber_id`)
    ) $collate;",

	"CREATE TABLE {$wpdb->prefix}mailster_forms (
        `ID` bigint(20) NOT NULL AUTO_INCREMENT,
        `name` varchar(191) NOT NULL DEFAULT '',
        `submit` varchar(191) NOT NULL DEFAULT '',
        `asterisk` tinyint(1) DEFAULT 1,
        `userschoice` tinyint(1) DEFAULT 0,
        `precheck` tinyint(1) DEFAULT 0,
        `dropdown` tinyint(1) DEFAULT 0,
        `prefill` tinyint(1) DEFAULT 0,
        `inline` tinyint(1) DEFAULT 0,
        `overwrite` tinyint(1) DEFAULT 0,
        `addlists` tinyint(1) DEFAULT 0,
        `style` longtext,
        `custom_style` longtext,
        `doubleoptin` tinyint(1) DEFAULT 1,
        `subject` longtext,
        `headline` longtext,
        `content` longtext,
        `link` longtext,
        `resend` tinyint(1) DEFAULT 0,
        `resend_count` int(11) DEFAULT 2,
        `resend_time` int(11) DEFAULT 48,
        `template` varchar(191) NOT NULL DEFAULT '',
        `vcard` tinyint(1) DEFAULT 0,
        `vcard_content` longtext,
        `confirmredirect` varchar(2083) DEFAULT NULL,
        `redirect` varchar(2083) DEFAULT NULL,
        `added` int(11) unsigned DEFAULT NULL,
        `updated` int(11) unsigned DEFAULT NULL,
        PRIMARY KEY  (`ID`)
    ) $collate;",

	"CREATE TABLE {$wpdb->prefix}mailster_form_fields (
        `form_id` bigint(20) unsigned NOT NULL,
        `field_id` varchar(191) NOT NULL,
        `name` longtext NOT NULL,
        `error_msg` longtext NOT NULL,
        `required` tinyint(1) unsigned NOT NULL,
        `position` int(11) unsigned NOT NULL,
        UNIQUE KEY `id` (`form_id`,`field_id`)
    ) $collate;",

	"CREATE TABLE {$wpdb->prefix}mailster_forms_lists (
        `form_id` bigint(20) unsigned NOT NULL,
        `list_id` bigint(20) unsigned NOT NULL,
        `added` int(11) unsigned NOT NULL,
        UNIQUE KEY `id` (`form_id`,`list_id`),
        KEY `form_id` (`form_id`),
        KEY `list_id` (`list_id`)
    ) $collate;",

), $collate);
