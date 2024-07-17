<?php
use Algolia\AlgoliaSearch\SearchClient;

class ASP_Algolia_Integration {
    private $algolia_client;
    private $algolia_index;

    public function init() {
        // Initialize Algolia integration on admin init
        add_action('admin_init', array($this, 'init_algolia'));
        // Hook into post save and delete actions
        add_action('save_post', array($this, 'index_post'), 10, 3);
        add_action('delete_post', array($this, 'remove_post_from_index'));
    }

    public function init_algolia() {
        // Retrieve Algolia credentials from WordPress options
        $application_id = get_option('asp_algolia_application_id');
        $admin_api_key = get_option('asp_algolia_admin_api_key');
        $index_name = get_option('asp_algolia_index_name');
    
        if (!$application_id || !$admin_api_key || !$index_name) {
            return;
        }
    
        try {
            // Initialize Algolia client and index
            $this->algolia_client = \Algolia\AlgoliaSearch\SearchClient::create($application_id, $admin_api_key);
            $this->algolia_index = $this->algolia_client->initIndex($index_name);

            // Get snippet word count and posts per page from settings
            $snippet_word_count = intval(get_option('asp_snippet_word_count', 20));
            $posts_per_page = intval(get_option('asp_posts_per_page', 10));
            
            // Set Algolia index settings
            $this->algolia_index->setSettings([
                'attributesToSnippet' => [
                    'post_content:' . $snippet_word_count,
                ],
                'hitsPerPage' => $posts_per_page
            ]);
    
            // Optionally index all posts on initialization
            $this->index_all_posts();
        } catch (\Algolia\AlgoliaSearch\Exceptions\AlgoliaException $e) {
            // Display admin notice on Algolia connection error
            add_action('admin_notices', function() use ($e) {
                echo '<div class="error"><p>' . __('Algolia connection error: ', 'advanced-search-plugin') . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }

    public function index_post($post_id, $post, $update) {
        // Index a single post when it's saved or updated
        if (!$this->algolia_index) {
            return;
        }

        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        $post_type = get_post_type($post_id);
        $allowed_post_types = apply_filters('asp_allowed_post_types', array('post', 'page'));

        if (!in_array($post_type, $allowed_post_types)) {
            return;
        }

        $record = $this->get_post_record($post);
        $this->algolia_index->saveObject($record);
    }

    public function remove_post_from_index($post_id) {
        // Remove a post from the Algolia index when it's deleted
        if (!$this->algolia_index) {
            return;
        }

        $this->algolia_index->deleteObject($post_id);
    }

    private function get_post_record($post) {
        // Prepare a post record for Algolia indexing
        $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full');
        return array(
            'objectID' => $post->ID,
            'post_title' => $post->post_title,
            'post_content' => wp_strip_all_tags($post->post_content),
            'post_type' => $post->post_type,
            'post_date' => $post->post_date,
            'permalink' => get_permalink($post->ID),
            'thumbnail_url' => $thumbnail_url ? $thumbnail_url : ''
        );
    }

    public function fetch_all_posts() {
        // Fetch all published posts for indexing
        $args = array(
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);
        $posts = $query->posts;

        $records = array();
        foreach ($posts as $post) {
            $records[] = $this->get_post_record($post);
        }

        return $records;
    }

    public function index_all_posts() {
        // Index all posts in the Algolia index
        if (!$this->algolia_index) {
            return;
        }

        $records = $this->fetch_all_posts();
        $this->algolia_index->saveObjects($records);
    }

    // New methods for caching search results:
    private function cache_search_results($search_term, $results) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asp_search_cache';
        
        $serialized_results = maybe_serialize($results);
        
        $result = $wpdb->replace(
            $table_name,
            array(
                'search_term' => $search_term,
                'results' => $serialized_results
            ),
            array('%s', '%s')
        );
        
        if ($result === false) {
            error_log("Failed to cache search results. Error: " . $wpdb->last_error);
        } else {
            error_log("Successfully cached search results for term: " . $search_term);
        }
    }

    private function get_cached_search_results($search_term) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asp_search_cache';
            
        $cached = $wpdb->get_var($wpdb->prepare(
            "SELECT results FROM $table_name WHERE search_term = %s AND created_at > %s",
            $search_term,
            date('Y-m-d H:i:s', strtotime('-1 hour')) // Cache is valid for 1 hour
        ));
            
        return $cached ? maybe_unserialize($cached) : false;
    }

    public function search($query) {
        if (!$this->algolia_index) {
            return array();
        }

        // Check cache first
        $cached_results = $this->get_cached_search_results($query);
        if ($cached_results !== false) {
            error_log("Returning cached results for query: " . $query);
            return $cached_results;
        }

        try {
            $results = $this->algolia_index->search($query);
            $hits = $results['hits'];
            
            error_log("Caching results for query: " . $query);
            // Cache the results
            $this->cache_search_results($query, $hits);
            
            return $hits;
        } catch (\Algolia\AlgoliaSearch\Exceptions\AlgoliaException $e) {
                error_log('Algolia search error: ' . $e->getMessage());
                return array();
        }
    }
}