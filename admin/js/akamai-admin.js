(function( $ ) {
    'use strict';

    function getCredentials() {
        return {
            'host': $('#akamai-credentials-host').val(),
            'access-token': $('#akamai-credentials-access-token').val(),
            'client-token': $('#akamai-credentials-client-token').val(),
            'client-secret': $('#akamai-credentials-client-secret').val(),
        };
    }

    function setVerifyButtonDisabled() {
        var creds = getCredentials();
        var vals = Object.keys(creds).map(function(key) {
            return creds[key];
        });
        $('#verify').prop('disabled', vals.includes(''));
    }

    $(function() {
        setVerifyButtonDisabled();
        $("form :input").keyup(setVerifyButtonDisabled);
        $("form :input").change(setVerifyButtonDisabled);
        $('#verify').click(function(e) {
            e.stopPropagation();

            var data = {
                'action': 'akamai_verify_credentials',
                'credentials': getCredentials(),
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
                    msg.append($('<p>Credentials verified successfully.</p>'));
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
