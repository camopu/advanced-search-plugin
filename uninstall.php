<?php

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options from the database
delete_option('asp_algolia_application_id');
delete_option('asp_algolia_admin_api_key');
delete_option('asp_algolia_search_api_key');
delete_option('asp_algolia_index_name');

// If you've created any custom tables, you should drop them here
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}asp_custom_table");

// Remove any scheduled cron jobs
wp_clear_scheduled_hook('asp_daily_index_update');

// If you've stored any files, you should delete them here
$upload_dir = wp_upload_dir();
$asp_directory = $upload_dir['basedir'] . '/advanced-search-plugin';
if (is_dir($asp_directory)) {
    $files = glob($asp_directory . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    rmdir($asp_directory);
}

// If you've added any user meta, you should delete it here
$users = get_users();
foreach ($users as $user) {
    delete_user_meta($user->ID, 'asp_user_preference');
}

// If you've added any post meta, you should delete it here
$posts = get_posts(array('post_type' => 'any', 'numberposts' => -1));
foreach ($posts as $post) {
    delete_post_meta($post->ID, 'asp_post_meta');
}

// Flush rewrite rules
flush_rewrite_rules();