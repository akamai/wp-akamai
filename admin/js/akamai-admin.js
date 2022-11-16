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

			$.post(ajaxurl, data, function(response) {
				var response = $.parseJSON(response);

				if ($('#verify-msg').length == 0) {
					$('#verify').before($('<div id="verify-msg" class="notice is-dismissible"><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'));
				}

				var msg = $('#verify-msg');

				//Make the dismiss notice button remove the notice
				var $button = $('#verify-msg button');
				$button.on( 'click', function( event ) {
					event.preventDefault();
					msg.fadeTo( 100, 0, function() {
						msg.slideUp( 100, function() {
							msg.remove();
						});
					});
				});

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
