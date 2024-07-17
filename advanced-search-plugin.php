<?php
/**
 * Plugin Name: Advanced Search Plugin
 * Plugin URI: https://github.com/camopu
 * Description: Enhances search functionality using Algolia integration
 * Version: 1.0
 * Author: Akimchenko A
 * Author URI: https://github.com/camopu
 * Text Domain: advanced-search-plugin
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('ASP_VERSION', '1.0');
define('ASP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ASP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load Composer autoloader
if (file_exists(ASP_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once ASP_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Display a notice to the admin if the autoload file is missing
    add_action('admin_notices', function() {
        echo '<div class="error"><p>' . __('Advanced Search Plugin requires Composer autoload file. Please run composer install in the plugin directory.', 'advanced-search-plugin') . '</p></div>';
    });
    return; // Stop plugin execution
}

// Include required files
require_once ASP_PLUGIN_DIR . 'includes/class-algolia-integration.php';
require_once ASP_PLUGIN_DIR . 'includes/class-search-form.php';
require_once ASP_PLUGIN_DIR . 'admin/class-admin-settings.php';
require_once ASP_PLUGIN_DIR . 'public/class-public.php';

/**
 * The code that runs during plugin activation.
 */
function activate_advanced_search() {
    create_custom_tables();
    create_search_log_table();
    create_search_cache_table();
    
    // Set default options
    add_option('asp_algolia_app_id', '');
    add_option('asp_algolia_api_key', '');
    add_option('asp_algolia_index_name', '');
    
    // Schedule cron jobs if needed
    if (!wp_next_scheduled('asp_daily_index_update')) {
        wp_schedule_event(time(), 'daily', 'asp_daily_index_update');
    }
    
    // Flush rewrite rules if the plugin adds custom rewrite rules
    flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_advanced_search() {
    // Remove scheduled cron jobs
    wp_clear_scheduled_hook('asp_daily_index_update');
    
    // Flush rewrite rules to remove any custom rules added by the plugin
    flush_rewrite_rules();
    
    // Optionally, remove plugin options if you want to clean up completely
    // delete_option('asp_algolia_app_id');
    // delete_option('asp_algolia_api_key');
    // delete_option('asp_algolia_index_name');
    
    // Note: We usually don't remove custom tables here to prevent data loss.
    // If you want to remove them, uncomment the next line
    // remove_custom_tables();
}

function create_custom_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'asp_search_log';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        search_term text NOT NULL,
        results_count int NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Helper function to remove custom tables
function remove_custom_tables() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'asp_search_log';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

// Login of search queries
function create_search_log_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'asp_search_log';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        search_term varchar(255) NOT NULL,
        user_id bigint(20) unsigned,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        results_count int unsigned,
        PRIMARY KEY  (id),
        KEY search_term (search_term),
        KEY user_id (user_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Caching of search results
function create_search_cache_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'asp_search_cache';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        search_term varchar(255) NOT NULL,
        results longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY search_term (search_term)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Wiping the cache
function clean_old_cache() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'asp_search_cache';
    
    $wpdb->query(
        "DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)"
    );
}

// Schedule cache cleanup
if (!wp_next_scheduled('asp_clean_old_cache')) {
    wp_schedule_event(time(), 'daily', 'asp_clean_old_cache');
}
add_action('asp_clean_old_cache', 'clean_old_cache');
// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'activate_advanced_search');
register_deactivation_hook(__FILE__, 'deactivate_advanced_search');

/**
 * Begins execution of the plugin.
 */
function run_advanced_search() {
    // Initialize Algolia Integration
    if (class_exists('ASP_Algolia_Integration')) {
        $algolia_integration = new ASP_Algolia_Integration();
        if (method_exists($algolia_integration, 'init')) {
            $algolia_integration->init();
        }
    }

    // Initialize Search Form
    if (class_exists('ASP_Search_Form')) {
        $search_form = new ASP_Search_Form();
        if (method_exists($search_form, 'init')) {
            $search_form->init();
        }
    }

    // Initialize Admin Settings
    if (is_admin() && class_exists('ASP_Admin_Settings')) {
        $admin_settings = new ASP_Admin_Settings();
        if (method_exists($admin_settings, 'init')) {
            $admin_settings->init();
        }
        // Add AJAX action for testing Algolia connection
        add_action('wp_ajax_asp_test_algolia_connection', array($admin_settings, 'test_algolia_connection'));
    }

    // Initialize Public Functionality
    if (class_exists('ASP_Public')) {
        $public = new ASP_Public();
        if (method_exists($public, 'init')) {
            $public->init();
        }
    }
}

// Run the plugin
run_advanced_search();

// Add settings link on plugin page
function asp_settings_link($links) {
    $settings_link = '<a href="admin.php?page=advanced-search-settings">' . __('Settings', 'advanced-search-plugin') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'asp_settings_link');