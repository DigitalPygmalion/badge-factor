jQuery(function($){
  $('body').on('click', '.badgefactor-update-status-button', function(e) {
    e.preventDefault();
    var postid = $(this).data('postid'),
        userid = $(this).data('userid'),
        nonce = AdminAjax.nonce,
        posttype = $(this).data('posttype'),
        update_action = $(this).data('action'),
        $this = $(this);
    $.post(
      AdminAjax.url,
      {
        'action': 'bf-update-status',
        'postid' : postid,
        'userid': userid,
        'posttype': posttype,
        'update_action': update_action,
        'nonce': nonce
      },
      function(response) {
        $this.closest('tr').find('.column-status').html(response);
      }
    );
  });
});
