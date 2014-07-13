jQuery(document).ready(function($) {
  $('form.answer-survey label.control-label > span.required').each(function() {
    var next = $(this).parent().next();
    $('input[type="checkbox"]', next).each(function() {
      $(this).prop('required', true);
    });
  });

  $('form.answer-survey input[type="submit"]').prop('disabled', false);

  $('form.answer-survey').each(function() {
    $(this).validate({
      errorPlacement: function(error, element) {
        var scope = element.closest($('.control-group'));
        var firstChild = $('> label', scope);
        error.insertBefore(firstChild);
      },
      submitHandler: function(form) {
        var overlay = $('.overlay', form);
        overlay.show();
        $.post(wwm_awesome_surveys.ajaxurl, $(form).serializeArray(), function(data) {
          if (null == data) {
            $(form).empty().append('<p class="error">An unknown error occured (data object is null)</p>');
            return null;
          }
          msg = ('undefined' != typeof data.data.thank_you) ? data.data.thank_you : '<span class="error">' + data.data + '</span>';
          $(form).empty().append('<p>' + msg + '</p>');
          if (null != data.data.url) {
            window.location = data.data.url;
          }
        }, 'json').fail(function(xhr) {
          $(form).empty().append('<p class="error">There was an error. The error status code is: ' + xhr.status + ' The error message is: ' + xhr.statusText + '</p>');
        }).always(function() {
          overlay.hide();
        });
      }
    });
  });
});