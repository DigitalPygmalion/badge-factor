(function($) {
    $(document).ready(function() {
        $('body').on('click', '.private-status', function(e) {
            var button = $(this);
            $.post( MyAjax.url, {
                    action: 'toggle-private-status',
                    achievement_id: button.data('achievement-id'),
                    nonce: MyAjax.nonce },
                function(response) {
                    if (response.success) {
                        button.removeClass('public').removeClass('private').addClass(response.status);
                        if (response.status == 'private') {
                            button.find('.glyphicon').removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
                        } else {
                            button.find('.glyphicon').removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open')
                        }
                    }
                }
            );
        })
    });
})(jQuery);