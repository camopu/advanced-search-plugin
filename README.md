# Advanced Search Plugin
The Advanced Search Plugin is a WordPress plugin that enhances search functionality using Algolia integration. It provides a more powerful and efficient search experience compared to the default WordPress search.

<h2>Installing and using:</h2>
<a href="https://www.youtube.com/watch?v=er-TDbLktUY&feature=youtu.be"><img width="573" alt="Screenshot_5" src="https://github.com/user-attachments/assets/4a34d9c5-28cf-4c89-ac61-65c5b8ded34a"><br><strong>Click here to see the video</strong></a>

<h3>Plugin Installation</h3>
<ol>
<li><a href="https://github.com/user-attachments/files/16279941/advanced-search-plugin.zip" target="_blank">Download the ZIP archive</a> of the Advanced Search Plugin.</li>
<li>Log in to your WordPress admin panel.</li>
<li>Navigate to "Plugins" -> "Add New".</li>
<li>Click the "Upload Plugin" button.</li>
<li>Select the downloaded ZIP file and click "Install Now".</li>
<li>After installation, click "Activate Plugin".</li>
</ol>

<h3>Obtaining Data from Algolia Dashboard</h3>
<ol>
<li>Register or log in to your account on the Algolia website (https://www.algolia.com/)</li>
<li>After logging in, go to the "API Keys" section.</li>
<li>Here you will find the following necessary data:<br>

Application ID<br>
Admin API Key (Be careful, do not share this key publicly)<br>

<li>Create a new index for your website:<br>
Go to the "Indices" section<br>
Click "Create Index"<br>
Enter an index name (e.g., "wp_posts")</li>
</ol>

<h3>Plugin Configuration in WordPress</h3>
<ol>
<li>In the WordPress admin panel, go to the "Advanced Search" section.</li>
<li>On the "Algolia Settings" tab, enter the obtained data:<br>

Application ID<br>
Admin API Key<br>
Index Name (the name of the created index)
</li>
<li>Click "Save Changes".</li>
<li>Click the "Test Connection" button to verify the connection with Algolia.</li>
</ol>
<h3>Content Indexing</h3> 
<ol>
<li>After successfully connecting to Algolia, go to the "General Settings" tab.</li>
<li>Configure indexing parameters (e.g., number of words in the snippet, number of posts per page).</li>
<li>Click the "Index All Posts" button for initial indexing of all content.</li>
</ol>
<h3>Placing the Search Form</h3>
<ol>
<li>Use the shortcode [advanced_search] to place the search form on desired pages or posts.</li>
<li>Or add the search form to a sidebar via a widget if your theme supports widgets.</li>
</ol>
<h3>Testing</h3>
<ol>
<li>Go to the frontend of your website and check the search form functionality.</li>
<li>Perform several test search queries to ensure results are displayed correctly.</li>
</ol>
<h3>Additional Settings</h3>
<ol>
<li>Return to the plugin settings and adjust additional parameters if needed, such as the display style of results (list or grid).</li>
<li>Check search query logs for analysis and search optimization.</li>
</ol>
<h3>Important Nuances</h3>
<ul>
<li>Keep the Admin API Key secure. Do not publish it or share it with third parties.</li>
<li>Regularly check the connection with Algolia and the indexing status.</li>
<li>Remember that Algolia has limitations on the free plan. Monitor usage and consider upgrading to a paid plan if necessary.</li>
<li>Ensure your server meets the minimum requirements for the plugin (PHP version, WordPress version, etc.).</li>
<li>If you encounter any issues during installation or configuration, check the plugin's documentation or reach out to the plugin's support.</li>
</ul>
After completing these steps, the Advanced Search Plugin should be successfully installed and configured on your WordPress site, providing users with enhanced search functionality powered by Algolia.

<h2>Technologies Used:</h2>
<ul>
<li><strong>PHP:</strong> The core plugin logic is written in PHP, following WordPress plugin development standards.</li>
<li><strong>Algolia:</strong> The plugin integrates with Algolia, a hosted search engine, to provide fast and relevant search results.</li>
<li><strong>AJAX:</strong> Asynchronous JavaScript and XML (AJAX) is used to fetch search results dynamically without page reloads.</li>
<li><strong>jQuery:</strong> The jQuery library is used for DOM manipulation and AJAX requests on the client-side.</li>
<li><strong>InstantSearch.js:</strong> This JavaScript library from Algolia is used to create the interactive search interface.</li>
<li><strong>WordPress Settings API:</strong> Used to create the plugin's admin settings page.</li>
<li><strong>WordPress Shortcodes:</strong> A shortcode is provided to easily embed the search form in posts or pages.</li>
</ul>
<h2>Optimization Methods:</h2>
<h3>Caching</h3>
Search results are cached in a custom database table to reduce API calls to Algolia and improve performance.
The cache is automatically cleared after a specified time period (e.g., 1 hour).

<h3>Indexing</h3>
Posts are indexed in Algolia when they are created, updated, or deleted, ensuring the search index is always up-to-date.
Bulk indexing is available to index all existing posts.

<h3>Search Query Logging</h3>
Search queries are logged in a custom database table, which can be used for analytics and optimization of search functionality.

<h3>Snippet Optimization</h3>
The plugin allows customization of snippet word count to optimize the display of search results.

<h3>Pagination</h3>
Search results are paginated to improve performance and user experience when dealing with large result sets.

<h3>Customizable Display</h3>
The plugin offers options to display search results in either a list or grid format, allowing for better integration with different website designs.

<h3>Security</h3>
Input sanitization and nonce checks are implemented to enhance security.
Algolia API keys are stored securely in WordPress options.

<h3>Performance</h3>
JavaScript and CSS files are enqueued properly and only loaded when necessary.
External libraries (Algolia, InstantSearch.js) are loaded from CDNs to potentially improve load times.

<h3>Error Handling</h3>
The plugin includes error logging and displays admin notices for critical issues, such as connection problems with Algolia.

<h3>Extensibility</h3>
The plugin uses WordPress filters and actions, allowing developers to extend or modify its functionality.
