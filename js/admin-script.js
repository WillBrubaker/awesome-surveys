jQuery(document).ready(function($) {

  var newElementForm = $('#new-elements').html();
  $('#tabs').tabs();

  $('#tabs #create #survey-manager').validate({
    submitHandler: function(form) {
      overlay = $('.overlay', $(form));
      overlay.show();
      $.post(ajaxurl, $(form).serializeArray(), function(data) {
        if (data.error) {
          alert(data.error)
        } else {
          var buttonHtml = $('.create_holder').html();
          $('#survey-manager').hide();
          var selectHtml = data.form;
          $(data.form).insertBefore('#add-element');
          $('#new-elements input[type="submit"]').prop('disabled', true);
          $('#new-elements').show();
          var surveyName = $('input[type="hidden"][name="survey_name"]').val();
          $('#preview h4.survey-name').text('Preview of Survey: ' + surveyName);
        }
        $('.accordion').accordion({
          collapsible: true,
          active: 0,
          header: 'h5',
          heightStyle: 'content',
        });
        overlay.hide();
      }, 'json');
    }
  });

  $('#new-elements').on('change', '.type-selector', function() {
    var overlay = $('#create > .overlay');
    var type = $(':selected', this).val();
    $('#new-elements input[type="submit"]').prop('disabled', true);
    overlay.show();
    if (type != '') {
      $.post(ajaxurl, {
        'action': 'get_element_form',
        'type': $(':selected', this).val(),
        'text': $(':selected', this).text()
      }, function(data) {
        $('#add-element').empty().append(data.form);
        $('#slider').slider({
          range: false,
          max: 10,
          min: 1,
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

        $('#new-elements').tooltip({
          position: {
            my: "left+15 center",
            at: 'right top'
          },
          show: {
            effect: 'blind',
            duration: 800
          }
        });
        //$('#tabs #new-elements input[type="number"]').each(function() {
        $('#tabs #new-elements').validate({
          submitHandler: function(form) {
            var overlay = $('#create > .overlay');
            overlay.show();
            var existingData = $('#save-survey input[type="hidden"][name="existing_elements"]').val();
            var input;
            if (typeof existingData != 'undefined') {
              input = $("<input>", {
                type: "hidden",
                name: "existing_elements",
                value: existingData
              });
              $('#new-elements').append($(input));
            }
            $.post(ajaxurl, $(form).serializeArray(), function(data) {
              $('#preview .survey-preview').empty().append(data);
            });
            $('#add-element').empty();
            $('#new-elements input[type="submit"]').prop('disabled', true);
            $('.accordion').accordion('option', 'active', false);
            $('#new-element-selector .type-selector option[value=""]').prop('selected', true);
            overlay.hide();
          }
        });
        //});
        $('#new-elements input[type="submit"]').prop('disabled', false);
      }, 'json');
    } else {
      $('#add-element').empty();
    }
    overlay.hide();
  });


  $('#preview').on('click', '.survey-preview form input[type="submit"]', function() {
    $('input[type="submit"]', $(this).parents('form')).removeAttr('clicked');
    $(this).attr('clicked', 'true');
  });

  $('#preview .survey-preview').on('submit', 'form#save-survey', function(e) {
    e.preventDefault();
    var overlay = $('#create > .overlay');
    overlay.show();
    var form = $(this);
    var buttonName = $('input[type="submit"][clicked="true"]').attr('name');
    if ('save' == buttonName) {
      $.post(ajaxurl, $(this).serializeArray(), function(data) {
        $.post(ajaxurl, {action: 'get_survey_results'}, function(html){
          $('#existing-surveys').empty().append(html);
          $('#survey-responses').accordion({
    header: 'h5',
    heightStyle: 'content',
    collapsible: true,
    active: false,
  });
  $('.answer-accordion').accordion({
    header: 'h4.answers',
    collapsible: true,
    active: false,
    heightStyle: 'content'
  });
        },'html')
      });
    }
    $('#preview h4.survey-name').empty();
    $('.survey-preview').empty();
    $('#add-element').empty();
    $('#new-element-selector .type-selector option[value=""]').prop('selected', true);
    $('#new-elements').empty().append(newElementForm).hide();
    $('#survey-manager').show();
    overlay.hide();
  });
  $('button.delete').on('click', function() {
    $.post(ajaxurl, {
      'action': 'delete_surveys'
    });
  });
  $('#survey-responses').accordion({
    header: 'h5',
    heightStyle: 'content',
    collapsible: true,
    active: false,
  });
  $('.answer-accordion').accordion({
    header: 'h4.answers',
    collapsible: true,
    active: false,
    heightStyle: 'content'
  });
});