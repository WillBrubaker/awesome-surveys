jQuery(document).ready(function($) {
  $('form.answer-survey input[type="submit"]').prop('disabled', true);
  $('form.answer-survey label.control-label > span.required').each(function() {
    var next = $(this).parent().next();
    $('input[type="checkbox"]', next).each(function() {
      $(this).prop('required', true);
    });
  });
  $('form.answer-survey input[type="submit"]').prop('disabled', false);
  $('form.answer-survey').validate({
    errorPlacement: function(error, element) {
      var scope = element.closest($('.control-group'));
      var firstChild = $('> label', scope);
      error.insertBefore(firstChild);
    },
    submitHandler: function(form) {
      var overlay = $('.overlay', form);
      overlay.show();
      $.post(wwm_awesome_surveys.ajaxurl, $(form).serializeArray(), function(data) {
        $(form).empty().append('<p>' + data.data.thank_you + '</p>');
        overlay.hide();
      }, 'json');
    }
  });
});