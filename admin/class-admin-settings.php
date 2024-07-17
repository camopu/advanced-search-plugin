<?php

class ASP_Admin_Settings {
    public function init() {
        // Add actions for admin menu, settings registration, AJAX handling, and script enqueuing
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_asp_test_algolia_connection', array($this, 'test_algolia_connection'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_asp_clear_search_cache', array($this, 'clear_search_cache'));

        // Set default values for snippet word count and posts per page if not already set
        if (false === get_option('asp_snippet_word_count')) {
            add_option('asp_snippet_word_count', 20);
        }
        // Set default value for pages if not set
        if (false === get_option('asp_posts_per_page')) {
            add_option('asp_posts_per_page', 20);
        }
    }

    public function add_admin_menu() {
        // Add the plugin's settings page to the WordPress admin menu
        add_menu_page(
            __('Advanced Search Settings', 'advanced-search-plugin'),
            __('Advanced Search', 'advanced-search-plugin'),
            'manage_options',
            'advanced-search-settings',
            array($this, 'render_settings_page'),
            'dashicons-search',
            100
        );
    }

    public function register_settings() {
        // Register settings for Algolia and general plugin options
        register_setting('asp_algolia_settings_group', 'asp_algolia_application_id');
        register_setting('asp_algolia_settings_group', 'asp_algolia_admin_api_key');
        register_setting('asp_algolia_settings_group', 'asp_algolia_index_name');
        register_setting('asp_general_settings_group', 'asp_posts_per_page');
        register_setting('asp_general_settings_group', 'asp_display_style');

        // General Settings
        register_setting('asp_general_settings_group', 'asp_snippet_word_count');

        // Algolia Settings Section
        add_settings_section(
            'asp_algolia_settings',
            __('Algolia Connection Settings', 'advanced-search-plugin'),
            array($this, 'render_algolia_settings_section'),
            'asp_algolia_settings'
        );

        add_settings_field(
            'asp_algolia_application_id',
            __('Application ID', 'advanced-search-plugin'),
            array($this, 'render_text_field'),
            'asp_algolia_settings',
            'asp_algolia_settings',
            array('label_for' => 'asp_algolia_application_id')
        );

        add_settings_field(
            'asp_algolia_admin_api_key',
            __('Admin API Key', 'advanced-search-plugin'),
            array($this, 'render_text_field'),
            'asp_algolia_settings',
            'asp_algolia_settings',
            array('label_for' => 'asp_algolia_admin_api_key')
        );

        add_settings_field(
            'asp_algolia_index_name',
            __('Index Name', 'advanced-search-plugin'),
            array($this, 'render_text_field'),
            'asp_algolia_settings',
            'asp_algolia_settings',
            array('label_for' => 'asp_algolia_index_name')
        );
        
        add_settings_field(
            'asp_algolia_test_connection',
            __('', 'advanced-search-plugin'),
            array($this, 'render_test_connection_button'),
            'asp_algolia_settings',
            'asp_algolia_settings'
        );

        // General Settings Section
         add_settings_field(
            'asp_display_style',
            __('Display Style', 'advanced-search-plugin'),
            array($this, 'render_display_style_field'),
            'asp_general_settings',
            'asp_general_settings'
        );

        add_settings_section(
            'asp_general_settings',
            __('General Settings', 'advanced-search-plugin'),
            array($this, 'render_general_settings_section'),
            'asp_general_settings'
        );

        add_settings_field(
            'asp_snippet_word_count',
            __('Snippet Word Count', 'advanced-search-plugin'),
            array($this, 'render_number_field'),
            'asp_general_settings',
            'asp_general_settings',
            array('label_for' => 'asp_snippet_word_count')
        );

        add_settings_field(
            'asp_posts_per_page',
            __('Posts per page', 'advanced-search-plugin'),
            array($this, 'render_number_field'),
            'asp_general_settings',
            'asp_general_settings',
            array('label_for' => 'asp_posts_per_page')
        );

        add_settings_field(
            'asp_clear_cache',
            __('Clear Search Cache', 'advanced-search-plugin'),
            array($this, 'render_clear_cache_button'),
            'asp_general_settings',
            'asp_general_settings'
        );
    }

    public function render_settings_page() {
        // Render the settings page with tabs for general and Algolia settings
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'algolia_settings';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=advanced-search-settings&tab=general_settings" class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>"><?php _e('General Settings', 'advanced-search-plugin'); ?></a>
                <a href="?page=advanced-search-settings&tab=algolia_settings" class="nav-tab <?php echo $active_tab == 'algolia_settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Algolia Settings', 'advanced-search-plugin'); ?></a>
            </h2>
            <form method="post" action="options.php">
                <?php
                if ($active_tab == 'algolia_settings') {
                    settings_fields('asp_algolia_settings_group');
                    do_settings_sections('asp_algolia_settings');
                } else {
                    settings_fields('asp_general_settings_group');
                    do_settings_sections('asp_general_settings');
                }
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_algolia_settings_section() {
        echo '<p>' . esc_html__('Enter your Algolia credentials below:', 'advanced-search-plugin') . '</p>';
    }

    public function render_general_settings_section() {
        echo '<p>' . esc_html__('Configure general plugin settings:', 'advanced-search-plugin') . '</p>';
    }

    public function render_display_style_field() {
        $display_style = get_option('asp_display_style', 'list');
        ?>
        <fieldset>
            <label>
                <input type="radio" name="asp_display_style" value="list" <?php checked('list', $display_style); ?>>
                <?php _e('List', 'advanced-search-plugin'); ?>
            </label>
            <br>
            <label>
                <input type="radio" name="asp_display_style" value="grid" <?php checked('grid', $display_style); ?>>
                <?php _e('Grid', 'advanced-search-plugin'); ?>
            </label>
        </fieldset>
        <p class="description"><?php _e('Choose how to display search results on the front-end.', 'advanced-search-plugin'); ?></p>
        <?php
    }

    public function render_text_field($args) {
        $option_name = $args['label_for'];
        $value = get_option($option_name);
        $input_type = $option_name === 'asp_algolia_admin_api_key' ? 'password' : 'text';
        echo '<input type="' . $input_type . '" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" class="regular-text">';
        if ($input_type === 'password') {
            echo '<button type="button" class="button asp-toggle-visibility" data-target="' . esc_attr($option_name) . '">' . __('Show', 'advanced-search-plugin') . '</button>';
        }
    }

    public function render_number_field($args) {
        $option_name = $args['label_for'];
        $value = get_option($option_name, $option_name === 'asp_posts_per_page' ? 10 : 20);
        echo '<input type="number" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" class="small-text">';
        
        if ($option_name === 'asp_snippet_word_count') {
            echo '<p class="description">' . __('Number of words to include in the content snippet.', 'advanced-search-plugin') . '</p>';
        } elseif ($option_name === 'asp_posts_per_page') {
            echo '<p class="description">' . __('Number of search results to display per page.', 'advanced-search-plugin') . '</p>';
        }
    }

    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_advanced-search-settings' !== $hook) {
            return;
        }
        wp_enqueue_style('asp-admin-styles', plugins_url('admin/css/admin-styles.css', dirname(__FILE__)), array(), '1.0');
        wp_enqueue_script('asp-admin-settings', plugins_url('admin/js/admin-script.js', dirname(__FILE__)), array('jquery'), '1.0', true);
        wp_localize_script('asp-admin-settings', 'aspSettings', array(
            'nonce' => wp_create_nonce('asp_test_algolia_connection'),
            'clearCacheNonce' => wp_create_nonce('asp_clear_search_cache')
        ));
    }

    public function render_clear_cache_button() {
        // Render the "Clear cache" button
        echo '<button type="button" id="asp-clear-cache" class="button">';
        echo '<span class="button-text">' . __('Clear Cache', 'advanced-search-plugin') . '</span>';
        echo '</button>';
        echo '<span class="spinner"></span>';
        echo '<span id="asp-cache-clear-result"></span>';
    }

    public function clear_search_cache() {
        // Add a new method to process the AJAX cache cleanup request:
        check_ajax_referer('asp_clear_search_cache', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions to perform this action.', 'advanced-search-plugin'));
        }
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'asp_search_cache';
        
        $result = $wpdb->query("TRUNCATE TABLE $table_name");
    
        if ($result !== false) {
            wp_send_json_success(__('Search cache cleared successfully.', 'advanced-search-plugin'));
        } else {
            wp_send_json_error(__('Failed to clear search cache. Error: ', 'advanced-search-plugin') . $wpdb->last_error);
        }
    }

    public function render_test_connection_button() {
        // Render the "Test Connection" button for Algolia
        echo '<button type="button" id="asp-test-algolia-connection" class="button">';
        echo '<span class="button-text">' . __('Test Connection', 'advanced-search-plugin') . '</span>';
        echo '<span class="spinner"></span>';
        echo '</button>';
        echo '<span id="asp-connection-result"></span>';
    }

    public function test_algolia_connection() {
        // AJAX handler for testing Algolia connection
        error_log('test_algolia_connection function called');
        error_log('POST data: ' . print_r($_POST, true));
        check_ajax_referer('asp_test_algolia_connection', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions to access this page.', 'advanced-search-plugin'));
        }
    
        $application_id = get_option('asp_algolia_application_id');
        $admin_api_key = get_option('asp_algolia_admin_api_key');
        $index_name = get_option('asp_algolia_index_name');
    
        if (empty($application_id) || empty($admin_api_key) || empty($index_name)) {
            wp_send_json_error(__('Please fill in all Algolia settings before testing the connection.', 'advanced-search-plugin'));
        }
    
        try {
            // Attempt to connect to Algolia and retrieve index settings
            if (!class_exists('\Algolia\AlgoliaSearch\SearchClient')) {
                require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
            }
    
            $client = \Algolia\AlgoliaSearch\SearchClient::create($application_id, $admin_api_key);
            $index = $client->initIndex($index_name);
            $indexSettings = $index->getSettings();
    
            wp_send_json_success(__('Connection successful! Index settings retrieved.', 'advanced-search-plugin'));
        } catch (\Algolia\AlgoliaSearch\Exceptions\UnreachableException $e) {
            wp_send_json_error(__('Connection failed: Unable to reach Algolia. Please check your Application ID.', 'advanced-search-plugin') . ' Error: ' . $e->getMessage());
        } catch (\Algolia\AlgoliaSearch\Exceptions\AlgoliaException $e) {
            wp_send_json_error(__('Connection failed: ', 'advanced-search-plugin') . $e->getMessage());
        } catch (Exception $e) {
            wp_send_json_error(__('An error occurred: ', 'advanced-search-plugin') . $e->getMessage());
        }
    }
}