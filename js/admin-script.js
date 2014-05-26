jQuery(document).ready(function($) {

  $('#tabs').tabs();

  $('#tabs').on('submit', '#survey-manager', function(e) {
    e.preventDefault();
    overlay = $('.overlay', $(this));
    overlay.show();
    $.post(ajaxurl, $(this).serializeArray(), function(data) {
      if (data.error) {
        alert(data.error)
      } else {
        var buttonHtml = $('.create_holder').html();
        $('#survey-manager').remove();
        var selectHtml = data.form;
        $('#add-element').empty().append(data.form);
        $('#new-elements').show();
        var surveyName = $('input[type="hidden"][name="survey_name"]').val();
        $('#preview h4.survey-name').text(surveyName);
      }
      overlay.hide();
    }, 'json');
  });

  $('#new-elements').on('change', '.type-selector', function() {
    $.post(ajaxurl, {
      'action': 'get_element_form',
      'type': $(':selected', this).val(),
      'text': $(':selected', this).text()
    }, function(data) {
      $(data.form).insertAfter('#add-element');
      var i = 0;
      $('#new-elements input[name*="type"]').each(function() {
        $(this).attr('name', 'type[' + i + ']');
        i++;
      });
    }, 'json');
  });
});