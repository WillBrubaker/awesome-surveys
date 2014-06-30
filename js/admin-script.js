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
        $.post(ajaxurl, {
          action: 'get_survey_results'
        }, function(html) {
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
          attachDialog($);
        }, 'html')
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

  $('#surveys').on('click', 'a.edit-question', function(e) {
    e.preventDefault();
    $('#edit-question input[name="survey_id"]').val($(this).attr('data-survey_id'));
    $('#edit-question input[name="question_id"]').val($(this).attr('data-question_id'));
    $('#edit-question input[name="_nonce"]').val($(this).attr('data-nonce'));
    $('#edit-question input[name="question"]').val($(this).text());
    $('#edit-question').dialog('open');
  });

  $('#surveys').on('click', 'a.edit-answer-option', function(e) {
    e.preventDefault();
    $('#edit-answer input[name="survey_id"]').val($(this).attr('data-survey_id'));
    $('#edit-answer input[name="question_id"]').val($(this).attr('data-question_id'));
    $('#edit-answer input[name="answer_id"]').val($(this).attr('data-answer_id'));
    $('#edit-answer input[name="_nonce"]').val($(this).attr('data-nonce'));
    $('#edit-answer input[name="answer"]').val($(this).text());
    $('#edit-answer').dialog('open');
  });

  $('#surveys').on('click', 'a.edit-survey-name', function(e) {
    e.preventDefault();
    $('#edit-survey-name input[name="survey_id"]').val($(this).attr('data-survey_id'));
    $('#edit-survey-name input[name="_nonce"]').val($(this).attr('data-nonce'));
    $('#edit-survey-name input[name="name"]').val($(this).text());
    $('#edit-survey-name').dialog('open');
  });

  $('#surveys').on('submit', 'form.delete-survey', function(e) {
    e.preventDefault();
    result = confirm('Really delete this survey? This action is permanent and not reversible!');
    if (true == result) {
      $.post(ajaxurl, $(this).serializeArray(), function(data) {
        if (data.success) {
          $.post(ajaxurl, {
            action: 'get_survey_results'
          }, function(html) {
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
            attachDialog($);
          }, 'html')
        }
      }).fail(function(xhr) {
        alert('error code: ' + xhr.status + ' message: ' + xhr.statusText);
      }).always(function() {

      });
    }
  });

  $('#styling-options').on('submit', function(e) {
    e.preventDefault();
    overlay = $('.overlay', $(this));
    overlay.show();
    $.post(ajaxurl, $(this).serializeArray(), function(data) {}).fail(function(xhr) {
      alert('error code: ' + xhr.status + ' error message ' + xhr.statusText);
    }).always(function() {
      overlay.hide();
    });
  })
  attachDialog($);
});

var activeDialog;

function attachDialog($) {
  $('#surveys #edit-question, #surveys #edit-answer, #surveys #edit-survey-name').dialog({
    autoOpen: false,
    title: 'Edit Survey',
    height: 'auto',
    width: 500,
    modal: true,
    buttons: [{
      text: 'Submit',
      id: 'button-ok',
      click: function() {
        $('div.wrap').css('cursor', 'progress');
        $('#button-ok').button('disable');
        $('#button-cancel').button('disable');
        submitVals = $($(this)).serializeArray();
        activeDialog = $(this);
        $.post(ajaxurl, submitVals, function(data) {
          if (data.success) {
            $.post(ajaxurl, {
              action: 'get_survey_results'
            }, function(html) {
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
              attachDialog($);
            }, 'html')
          } else if (false == data.success) {
            alert(data.data.message)
          }
        }, 'json').fail(function(xhr) {
          alert('error code: ' + xhr.status + ' error message ' + xhr.statusText);
        }).always(function() {
          activeDialog.dialog('close');
          $('div.wrap').css('cursor', 'default');
          $('#button-ok').button('enable');
          $('#button-cancel').button('enable');
        });
      }
    }, {
      text: 'Cancel',
      id: 'button-cancel',
      click: function() {
        $(this).dialog('close');
        $('div.wrap').css('cursor', 'default');
      }
    }],
    open: function() {
      $(this).keypress(function(e) {
        if (13 == e.keyCode) {
          e.preventDefault();
          $('#button-ok', $(this).parent()).trigger('click');
        }
      })
    }
  });
}