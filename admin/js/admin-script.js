(function($) {
    'use strict';
  
      $(document).ready(function() {
        // Function to test Algolia connection
        $('#asp-test-algolia-connection').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $buttonText = $button.find('.button-text');
            var $spinner = $button.find('.spinner');
            var originalText = $buttonText.text();

            // Disable button and show spinner
            $button.prop('disabled', true);
            $buttonText.text('Testing...');
            $spinner.addClass('is-active');

            var data = {
                'action': 'asp_test_algolia_connection',
                'nonce': aspSettings.nonce
            };

            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    $('#asp-connection-result').html('<span style="color: green; display:block; margin-top:1em;">' + response.data + '</span>');
                } else {
                    $('#asp-connection-result').html('<span style="color: red; display:block; margin-top:1em;">' + response.data + '</span>');
                }
            }).fail(function(xhr, status, error) {
                $('#asp-connection-result').html('<span style="color: red; display:block; margin-top:1em;">AJAX request failed: ' + error + '</span>');
            }).always(function() {
                // Re-enable button and hide spinner
                $button.prop('disabled', false);
                $buttonText.text(originalText);
                $spinner.removeClass('is-active');
            });
        });

        // Function to clear search cache
        $('#asp-clear-cache').on('click', function(e) {
          e.preventDefault();
          var $button = $(this);
          var $spinner = $button.next('.spinner');
          var originalText = $button.text();
      
          // Disable button and show spinner
          $button.prop('disabled', true);
          $button.text('Clearing...');
          $spinner.addClass('is-active');
      
          var data = {
              'action': 'asp_clear_search_cache',
              'nonce': aspSettings.clearCacheNonce
          };
      
          $.post(ajaxurl, data, function(response) {
              if (response.success) {
                  $('#asp-cache-clear-result').html('<span style="color: green; display:block; margin-top:1em;">' + response.data + '</span>');
              } else {
                  $('#asp-cache-clear-result').html('<span style="color: red; display:block; margin-top:1em;">' + response.data + '</span>');
              }
          }).fail(function(xhr, status, error) {
              $('#asp-cache-clear-result').html('<span style="color: red; display:block; margin-top:1em;">AJAX request failed: ' + error + '</span>');
          }).always(function() {
              // Re-enable button and hide spinner
              $button.prop('disabled', false);
              $button.text(originalText);
              $spinner.removeClass('is-active');
          });
      });      

      // Dynamic update by previewing the index
      $('#asp_algolia_index_name').on('input', function() {
        var indexName = $(this).val();
        $('#asp_index_preview').text(indexName ? indexName : 'your_index_name');
      });
  
      // Switch Key API Visibility
      $('.asp-toggle-visibility').on('click', function() {
        var $input = $('#' + $(this).data('target'));
        if ($input.attr('type') === 'password') {
          $input.attr('type', 'text');
          $(this).text('Hide');
        } else {
          $input.attr('type', 'password');
          $(this).text('Show');
        }
      });
    });
  })(jQuery);