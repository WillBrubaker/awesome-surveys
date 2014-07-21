jQuery(document).ready(function($) {

  var newElementForm = $('#new-elements').html();
  $('#tabs').tabs();

  $('#tabs #create #survey-manager').validate({
    submitHandler: function(form) {
      overlay = $('.overlay', $(form));
      overlay.show();
      $.post(ajaxurl, $(form).serializeArray(), function(data) {
        if (false == data.success) {
          alert(data.data)
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
              $('#add-element').empty();
              $('#new-elements input[type="submit"]').prop('disabled', true);
              $('.accordion').accordion('option', 'active', false);
              $('#new-element-selector .type-selector option[value=""]').prop('selected', true);
              $('button').button();
              elementsJSON = $.parseJSON($('form#save-survey [name="existing_elements"]').val())
              $(elementsJSON).each(function(index, value) {})
            }).fail(function(xhr) {
              alert('error status code: ' + xhr.status + ' error message: ' + xhr.statusText)
            }).always(function(){
              overlay.hide();
            });
          }
        });
        $('#new-elements input[type="submit"]').prop('disabled', false);
      }, 'json');
    } else {
      $('#add-element').empty()
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
    var form = $(this);
    var buttonName = $('input[type="submit"][clicked="true"]').attr('name');
    overlay.show();
    if ('save' == buttonName) {
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
            $('#survey-manager [name="survey_name"]').val('');
            attachDialog($);
          }, 'html')
        } else {
          alert(data.data);
        }
      }).fail(function(xhr) {
        alert('error code: ' + xhr.status + ' error message ' + xhr.statusText);
      }).always(function() {
        $('#preview h4.survey-name').empty();
        $('.survey-preview').empty();
        $('#add-element').empty();
        $('#new-element-selector .type-selector option[value=""]').prop('selected', true);
        $('#new-elements').empty().append(newElementForm).hide();
        $('#survey-manager').show();
        overlay.hide();
      });
    } else {
      $('#survey-manager').trigger('reset')
      location.reload()
    }
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

  $('#surveys').on('click', 'a.edit-thanks', function(e) {
    e.preventDefault();
    $('#edit-survey-thanks input[name="survey_id"]').val($(this).attr('data-survey_id'));
    $('#edit-survey-thanks input[name="_nonce"]').val($(this).attr('data-nonce'));
    $('#edit-survey-thanks textarea[name="thank_you"]').val($(this).text());
    $('#edit-survey-thanks').dialog('open');
  });

  $('#surveys').on('click', 'a.edit-auth-method', function(e) {
    e.preventDefault();
    $.post(ajaxurl, {
      action: 'wwm_get_auth_method_edit_form',
      survey_id: $(this).attr('data-survey_id'),
      _nonce: $(this).attr('data-nonce')
    }, function(data) {
      $(data.data).dialog({
        modal: true,
        title: 'Edit Survey Auth Method',
        width: 500,
        buttons: [{
          text: 'Submit',
          id: 'button-ok',
          click: function() {
            $('div.wrap').css('cursor', 'progress');
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
              activeDialog.dialog('destroy');
              $('div.wrap').css('cursor', 'default');
            });
          }
        }, {
          text: 'Cancel',
          id: 'button-cancel',
          click: function() {
            $(this).dialog('destroy');
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
    })
  })

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
  });

  $('#tabs').on('change', 'form#save-survey [name="existing_elements"]', function() {
    target = $('#tabs #save-survey input[type="submit"][name="save"]')
    if ('null' == $(this).val()) {
      $(this).val('')
      target.prop('disabled', true)
    } else {
      target.prop('disabled', false)
    }
  })

  $('#tabs').on('click', '#preview button.element-edit', function(e) {
    e.preventDefault()
    action = $(this).attr('data-action')
    var index = $(this).attr('data-index')
    var container = $(this).closest('.single-element-edit')
    var elementsJSON = $.parseJSON($('form#save-survey [name="existing_elements"]').val())
    if ('delete' == action) {
      container.remove();
      delete elementsJSON[index]
    } else {
      label = $('label.control-label', $(this).closest('.single-element-edit'))
      var dynamicDialog = generateDynamicDialog(elementsJSON[index]);
      dynamicDialog.dialog({
        title: "Edit Survey Question",
        modal: true,
        buttons: [{
          text: "Cancel",
          click: function() {
            $(this).dialog('destroy')
            $('#edit-slider').slider('destroy')
          }
        }, {
          text: "Submit",
          click: function(e) {
            elementsJSON = $.parseJSON($('form#save-survey [name="existing_elements"]').val())
            e.preventDefault()
            var form = $('form', dynamicDialog);
            var formValues = $('form', dynamicDialog).serializeArray();
            var validator = form.validate()
            if (validator.form()) {
              $.post(ajaxurl, formValues, function(data) {
                formValues = data.data;
                formValues = $.parseJSON(formValues);
                elementType = elementsJSON[index].type
                elementsJSON[index] = formValues
                elementsJSON[index].type = elementType
                label.text(formValues.name)
                if (1 == elementsJSON[index].validation.required) {
                  label.prepend('<span class="required">* </span>')
                }
                elementsJSON = removeNulls(elementsJSON)
                var types = {
                  Element_Radio: "radio",
                  Element_Checkbox: "checkbox"
                }
                if ('undefined' != typeof elementsJSON[index].label) {
                  var target
                  newHtml = ''
                  type = (elementsJSON[index].type in types) ? types[elementsJSON[index].type] : 'option'
                  if ('option' == type) {
                    target = $('select', container)
                    for (key in elementsJSON[index].value) {
                      newHtml += '<option value="' + key + '"'
                      if (key = elementsJSON[index].default) {
                        newHtml += ' selected="selected"'
                      }
                      newHtml += '>' + elementsJSON[index].label[key] + '</option>'
                    }

                  } else {
                    target = $('.controls', container)
                    setName = $('[type="' + type + '"]:first', target).attr('name');
                    for (key in elementsJSON[index].value) {
                      newHtml += '<label class="' + type + '"><input type="' + type + '" name="' + setName + '" value="' + key + '"'
                      if (key == elementsJSON[index].default) {
                        newHtml += ' checked="checked"'
                      }
                      newHtml += '> ' + formValues.label[key] + '</label>'
                    }
                  }
                  target.empty().append(newHtml)
                }
                $('form#save-survey [name="existing_elements"]').val(JSON.stringify(elementsJSON)).trigger('change')
                dynamicDialog.dialog('destroy')
                $('#edit-slider').slider('destroy')
              });
            }
          }
        }],
        open: function() {
          $(this).keypress(function(e) {
            if (e.keyCode == $.ui.keyCode.ENTER) {
              e.preventDefault();
              $(this).parent().find('button:eq(2)').trigger('click')
            }
          })
          $('#edit-slider').slider({
            value: ('undefined' != typeof elementsJSON[index].label) ? elementsJSON[index].label.length : 0,
            range: false,
            max: 10,
            min: 1,
            step: 1,
            change: function(event, ui) {
              var numOptions = ui.value;
              var radios = $('[type="radio"]', dynamicDialog)
              var selectedIndex = radios.index($('[type="radio"]:checked', dynamicDialog))

              $.post(ajaxurl, {
                'action': 'options_fields',
                'num_options': numOptions
              }, function(data) {
                $('#edit-answers-holder').empty().append(data);
                for (key in elementsJSON[index].label) {
                  $('[name="options[label][' + key + ']"]').val(elementsJSON[index].label[key])
                }
                if (selectedIndex > -1) {
                  $('#edit-answers-holder input[type="radio"]:eq(' + selectedIndex + ')').prop('checked', true)
                }
              });
            }
          }).each(function() {
            var opt = $(this).data().uiSlider.options;
            var vals = opt.max - opt.min;
            for (var i = 0; i <= vals; i++) {
              var el = $('<label>' + (i + opt.min) + '</label>').css('left', (i / vals * 100) + '%');
              $("#edit-slider").append(el);
            }
          })
        }
      })
    }
    elementsJSON = removeNulls(elementsJSON)
    $('form#save-survey [name="existing_elements"]').val(JSON.stringify(elementsJSON)).trigger('change')
  })

  attachDialog($);
});

var activeDialog;

function generateDynamicDialog(obj) {
  html = '<div class="dyn-diag"><form class="pure-form pure-form-stacked form-horizontal" method="post" action=""><input type="text" name="options[name]" value="'
  html += he.encode(obj.name.replace(/\\/g, ''))
  html += '" required="required"><label for="required-checkbox">Required? </label><input id="required-checkbox" type="checkbox" name="options[validation][required]" value="1"'
  if (typeof obj.validation != 'undefined' && typeof obj.validation.required != 'undefined' && 1 == obj.validation.required) {
    html += ' checked="checked"'
  }
  html += '>'
  if ('undefined' != typeof obj.label) {
    html += '<div class="slider-wrapper"><div id="edit-slider"></div><div class="slider-legend"></div></div>'
    html += '<p>answers:</p>'
    html += '<div id="edit-answers-holder">'
    for (key in obj.label) {
      count = Number(key) + 1
      var strippedLabel = he.encode(obj.label[key].replace(/\\/g, ''))
      html += '<label for="options-answer-' + key + '">Answer ' + (Number(key) + 1) + '</label><input id="options-answer-' + key + '" type="text" name="options[label][' + key + ']" value="' + strippedLabel + '" required="required">'
      html += '<label for="options-default-' + key + '">default?<br></label><input id="options-default-' + key + '" type="radio" name="options[default]" value="' + key + '"'
      if (key == obj.default) {
        html += ' checked="checked"'
      }
      html += '>'
    }
    html += '</div>'
  }
  if ('undefined' != typeof obj.validation.rules) {
    for (index in obj.validation.rules) {
      html += '<input type="hidden" name="options[validation][rules][' + index + ']" value="' + obj.validation.rules[index] + '">'
    }
  }
  html += '<input type="hidden" name="action" value="wwm_as_get_json">'
  html += '</form></div>'
  return jQuery(html)
}

function attachDialog($) {
  $('#surveys #edit-question, #surveys #edit-answer, #surveys #edit-survey-name, #surveys #edit-survey-thanks').dialog({
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

function removeNulls(elementsJSON) {
  var temp = [];

  for (i = 0; i < elementsJSON.length; i++) {
    if (elementsJSON[i] != null && 'undefined' != typeof elementsJSON[i] && 'null' != typeof elementsJSON[i]) {
      temp.push(elementsJSON[i])
    }
  }
  return (temp.length > 0) ? temp : null
}