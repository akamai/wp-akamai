(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function() {
        $('#verify').click(function(e) {
            e.stopPropagation();

			var data = {
				'action': 'akamai_verify_credentials',
				'edgerc': $('#akamai-edgerc').val(),
				'section': $('#akamai-section').val()
			};

			// We can also pass the url value separately from ajaxurl for front end AJAX implementations
			$.post(ajaxurl, data, function(response) {
				var response = $.parseJSON(response);
				var timeout = false;

				if ($('#verify-msg').length == 0) {
					$('#verify').before($('<div id="verify-msg" class="notice"></div>'));
				} else {
					$('#verify-msg').empty();
				}

				if (timeout) {
					clearInterval(timeout);
				}

				var msg = $('#verify-msg');

				timeout = setTimeout(function() {
					msg.fadeOut();
					msg.remove();
				}, 5000);

				if (response.success) {
					msg.removeClass('notice-error');
					msg.addClass('notice-success');
					msg.append($('<p>Credentials found successfully.</p>'));
				} else if (response.error) {
					msg.removeClass('notice-success');
					msg.addClass('notice-error');
					msg.append($('<p>' + response.error + '</p>'));
				} else {
					msg.removeClass('notice-success');
					msg.addClass('notice-error');
					msg.append($('<p>An unknown error occured</p>'));
				}

				$('#verify').before(msg);
			});
		});
    });

})( jQuery );
