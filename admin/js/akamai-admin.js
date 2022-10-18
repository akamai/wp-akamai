(function( $ ) {
	'use strict';

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
