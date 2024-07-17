<?php
class ASP_Public {
    public function init() {
        // Hook to enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        // Register shortcode for the search form
        add_shortcode('advanced_search', array($this, 'render_search_form'));
        // Hook AJAX actions for logged-in and non-logged-in users
        add_action('wp_ajax_asp_search', array($this, 'perform_search'));
        add_action('wp_ajax_nopriv_asp_search', array($this, 'perform_search'));
    }

    public function enqueue_scripts() {
        // Enqueue Algolia and InstantSearch scripts from CDN
        wp_enqueue_script('algoliasearch', 'https://cdn.jsdelivr.net/npm/algoliasearch@4/dist/algoliasearch-lite.umd.js', array(), '4.0.0', true);
        wp_enqueue_script('instantsearch', 'https://cdn.jsdelivr.net/npm/instantsearch.js@4', array(), '4.0.0', true);
        // Enqueue InstantSearch CSS
        wp_enqueue_style('instantsearch', 'https://cdn.jsdelivr.net/npm/instantsearch.css@7/themes/algolia-min.css', array(), '7.0.0');
        // Enqueue custom search script and style
        wp_enqueue_script('algolia-search', ASP_PLUGIN_URL . 'public/js/algolia-search.js', array('jquery', 'algoliasearch', 'instantsearch'), ASP_VERSION, true);
        wp_enqueue_style('algolia-search-style', ASP_PLUGIN_URL . 'public/css/algolia-search.css', array(), ASP_VERSION);

        // Localize script with necessary variables
        wp_localize_script('algolia-search', 'asp_vars', array(
            'application_id' => get_option('asp_algolia_application_id'),
            'search_api_key' => get_option('asp_algolia_admin_api_key'),
            'index_name' => get_option('asp_algolia_index_name'),
            'display_style' => get_option('asp_display_style', 'list')
        ));
    }
    
    // Search query logging method
    public function log_search_query($search_term, $results_count) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asp_search_log';
        
        $wpdb->insert(
            $table_name,
            array(
                'search_term' => $search_term,
                'user_id' => get_current_user_id(),
                'results_count' => $results_count
            ),
            array('%s', '%d', '%d')
        );
    }

    public function perform_search() {
        // Sanitize the search query
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        // Perform the search using Algolia integration
        $algolia_integration = new ASP_Algolia_Integration();
        $results = $algolia_integration->search($query);
        
        // Log the search query
        $this->log_search_query($query, count($results));
        
        // Format the search results
        $formatted_results = array();
        foreach ($results as $hit) {
            $formatted_results[] = array(
                'title' => isset($hit['post_title']) ? $hit['post_title'] : '',
                'content' => isset($hit['post_content']) ? wp_trim_words($hit['post_content'], 20) : '',
                'url' => isset($hit['permalink']) ? $hit['permalink'] : ''
            );
        }
        // Send the formatted results as JSON response
        wp_send_json_success($formatted_results);
    }

    public function render_search_form() {
        // Start output buffering
        ob_start();
        ?>
        <div id="asp-search-container">
            <div id="searchbox"></div>
            <div id="asp-search-results" style="display:none;">
                <div id="hits-per-page"></div>
                <div id="hits"></div>
                <div id="pagination"></div>
            </div>
        </div>
        <?php
        // Return the buffered content
        return ob_get_clean();
    }
}