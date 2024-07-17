// Wait for the DOM to be fully loaded before executing the script
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Algolia client using variables from WordPress
    const searchClient = algoliasearch(asp_vars.application_id, asp_vars.search_api_key);

    // Create an instance of InstantSearch
    const search = instantsearch({
        indexName: asp_vars.index_name,
        searchClient,
    });

    // Add widgets to the search instance
    search.addWidgets([
        // Search box widget
        instantsearch.widgets.searchBox({
            container: '#searchbox',
            placeholder: 'Search for content',
        }),

        // Hits widget to display search results
        instantsearch.widgets.hits({
            container: '#hits',
            // Dynamically apply class based on display style
            cssClasses: {
                list: asp_vars.display_style === 'grid' ? 'grid' : 'list'
            },
            // Template for rendering each hit
            templates: {
                item: function(hit) {
                    // Display thumbnail only for 'grid' style
                    const thumbnailHtml = asp_vars.display_style === 'grid' && hit.thumbnail_url
                        ? `<img src="${hit.thumbnail_url}" alt="${hit.post_title}" class="asp-hit-thumbnail">`
                        : '';
                    // Generate HTML for each hit
                    return `
                        <div class="asp-hit-item">
                            <div class="asp-hit-content">
                                ${thumbnailHtml}
                                <h2><a href="${hit.permalink}">${instantsearch.highlight({ attribute: 'post_title', hit })}</a></h2>
                                <p>${instantsearch.snippet({ attribute: 'post_content', hit })}</p>
                            </div>
                        </div>
                    `;
                },
                // Message for no results
                empty: 'No results found.',
            },
        }),

        // Pagination widget
        instantsearch.widgets.pagination({
            container: '#pagination',
            padding: 2,
            showFirst: false,
            showLast: false,
            scrollTo: '#searchbox',
        }),
    ]);

    // Custom widget to manage visibility of results
    const customWidget = {
        render({ results }) {
            const resultsContainer = document.querySelector('#asp-search-results');
            const paginationContainer = document.querySelector('#pagination');
            
            // Show/hide results and pagination based on query presence and results
            if (results.query && results.query.length > 0) {
                resultsContainer.style.display = 'block';
                if (results.nbHits > 0) {
                    paginationContainer.style.display = 'block';
                } else {
                    paginationContainer.style.display = 'none';
                }
            } else {
                resultsContainer.style.display = 'none';
            }
        }
    };

    // Add the custom widget
    search.addWidgets([customWidget]);

    // Start the search
    search.start();
});