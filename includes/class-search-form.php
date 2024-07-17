<?php
class ASP_Search_Form {
    public function init() {
        // Register shortcode for the search form
        add_shortcode('advanced_search', array($this, 'render_search_form'));
        // Enqueue necessary scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        // Handle AJAX requests for search results (for both logged-in and non-logged-in users)
        add_action('wp_ajax_asp_get_search_results', array($this, 'get_search_results'));
        add_action('wp_ajax_nopriv_asp_get_search_results', array($this, 'get_search_results'));
    }

    public function render_search_form($atts) {
        // Parse shortcode attributes with defaults
        $atts = shortcode_atts(array(
            'placeholder' => __('Search...', 'advanced-search-plugin'),
            'submit_text' => __('Search', 'advanced-search-plugin'),
        ), $atts, 'advanced_search');

        // Start output buffering
        ob_start();
        ?>
        <div class="asp-search-container">
            <form id="asp-search-form" class="asp-search-form" action="" method="get">
                <input type="text" name="asp_query" id="asp-search-input" placeholder="<?php echo esc_attr($atts['placeholder']); ?>">
                <button type="submit"><?php echo esc_html($atts['submit_text']); ?></button>
            </form>
            <div id="asp-search-results"></div>
        </div>
        <?php
        // Return the buffered content
        return ob_get_clean();
    }

    public function enqueue_scripts() {
        // Enqueue the main search script
        wp_enqueue_script('asp-search', ASP_PLUGIN_URL . 'public/js/algolia-search.js', array('jquery'), ASP_VERSION, true);
        // Localize the script with necessary data
        wp_localize_script('asp-search', 'asp_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('asp_search_nonce'),
        ));
        // Enqueue the search styles
        wp_enqueue_style('asp-search', ASP_PLUGIN_URL . 'public/css/algolia-search.css', array(), ASP_VERSION);
    }

    public function get_search_results() {
        // Verify the nonce for security
        check_ajax_referer('asp_search_nonce', 'nonce');

        // Get and sanitize the search query
        $query = isset($_GET['query']) ? sanitize_text_field($_GET['query']) : '';
        if (empty($query)) {
            wp_send_json_error('No search query provided');
        }
        // Example of a placeholder result set:
        $results = array(
            array('title' => 'Result 1', 'url' => '#', 'excerpt' => 'This is a sample result'),
            array('title' => 'Result 2', 'url' => '#', 'excerpt' => 'This is another sample result'),
        );

        // Send the results back as a JSON response
        wp_send_json_success($results);
    }
}