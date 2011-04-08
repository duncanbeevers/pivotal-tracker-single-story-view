(function() {
  $.ajaxPrefilter(function(options, localOptions, jqXHR) {
    var deferred = $.Deferred();

    jqXHR.done(function(json) {
      deferred[json.success ? 'resolve' : 'reject'](json);
    });

    jqXHR = deferred.promise(jqXHR);
    jqXHR.success = jqXHR.done;
    jqXHR.error = jqXHR.fail;
  });

  $('#retrieve_token').submit(function() {
    $('.login button').attr('disabled', true);
    $('.login .errors').remove();

    var req = $.ajax('', {
      data: {
        username: $('#username').val(),
        password: $('#password').val()
      },
      dataType: 'json',
      beforeSend: function() { $('.login button').attr('disabled', true); }
    });

    req.success(function(json) {
      $.cookie('token', json.token, { expires: 365 * 20, path: '/' });
      document.location.reload();
    });

    req.fail(function() {
      $('.login button').attr('disabled', false);
      $('.login').append($('#tmpl-token-errors').tmpl());
    });

    return false;
  });

})();

