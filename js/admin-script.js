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
        $(data.form).insertBefore('#add-element');
        $('#new-elements').show();
        var surveyName = $('input[type="hidden"][name="survey_name"]').val();
        $('#preview h4.survey-name').text('Preview of Survey: ' + surveyName);
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
      $('#add-element').empty().append(data.form);
      $('#slider').slider({
        range: false,
        max: 10,
        min: 0,
        step: 1,
        change: function(event, ui) {
          var numOptions = ui.value;
          $.post(ajaxurl, {
            'action': 'options_fields',
            'num_options': numOptions
          }, function(data) {
            $('#new-elements #options-holder').empty().append(data);
          });
        }
      }).each(function() {
        var opt = $(this).data().uiSlider.options;
        var vals = opt.max - opt.min;
        for (var i = 0; i <= vals; i++) {
          var el = $('<label>' + (i + opt.min) + '</label>').css('left', (i / vals * 100) + '%');
          $("#slider").append(el);
        }
      });
      $('#new-elements').tooltip();
    }, 'json');
  });
  $('#new-elements').on('change', 'input[type="number"]', function() {
    var numOptions = $(this).val();
    $.post(ajaxurl, {
      'action': 'options_fields',
      'num_options': numOptions
    }, function(data) {
      $('#new-elements #options-holder').empty().append(data);
    });
  });
  $('#tabs').on('submit', '#new-elements', function(e) {
    e.preventDefault();
    var previewForm = $('input[type="hidden"][name="survey_name"]', this).attr('data-id');
    $('#new-elements input[type="hidden"][name="existing_elements"]').each( function(){
      $(this).remove();
    });
    var existingData = $('#' + previewForm + ' input[type="hidden"][name="existing_elements"]').val();
    var input;
    if ( typeof existingData != 'undefined' ) {
      input = $("<input>", { type: "hidden", name: "existing_elements", value: existingData});
      $('#new-elements').append($(input));
    }
    $.post(ajaxurl, $('#new-elements').serializeArray(), function(data) {
      $('#preview .survey-preview').empty().append(data);
    });
  });
  $('#preview').on('submit', 'form', function(e){
    e.preventDefault();
    var form = $(this);
    var button = $('input[type="submit"]:focus').attr('name');
    if ( 'reset' == button ) {
      $('#preview h4.survey-name').empty();
      form.remove();
      location.reload();
    } else {
      $.post(ajaxurl, $(this).serializeArray(), function(data){

      });
    }
  });
});