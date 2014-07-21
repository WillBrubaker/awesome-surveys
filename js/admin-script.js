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
              $('button').button();
              elementsJSON = $.parseJSON($('form#save-survey [name="existing_elements"]').val())
              $(elementsJSON).each(function(index, value) {})
            });
            $('#add-element').empty();
            $('#new-elements input[type="submit"]').prop('disabled', true);
            $('.accordion').accordion('option', 'active', false);
            $('#new-element-selector .type-selector option[value=""]').prop('selected', true);
            overlay.hide();
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
            if ( validator.form() ) {
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
                var types = {Element_Radio:"radio", Element_Checkbox:"checkbox"}
                if ('undefined' != typeof elementsJSON[index].label ) {
                  var target
                  newHtml = ''
                  type = ( elementsJSON[index].type in types ) ? types[elementsJSON[index].type] : 'option'
                  if ( 'option' == type ) {
                    target = $('select', container)
                    for(key in elementsJSON[index].value) {
                      newHtml += '<option value="' + key + '"'
                      if ( key = elementsJSON[index].default ) {
                        newHtml += ' selected="selected"'
                      }
                      newHtml += '>' + elementsJSON[index].label[key] + '</option>'
                    }

                  } else {
                    target = $('.controls', container)
                    setName = $('[type="' + type + '"]:first', target).attr('name');
                    for(key in elementsJSON[index].value) {
                      newHtml += '<label class="' + type + '"><input type="' + type + '" name="' + setName + '" value="' + key + '"'
                      if ( key == elementsJSON[index].default ) {
                        newHtml += ' checked="checked"'
                      }
                      console.log(elementsJSON[index].label[key])
                      console.log(key)
                      console.log(elementsJSON[index].label)
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
          console.log($('[type="text"]', $(this)))
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
              var selectedIndex = radios.index( $('[type="radio"]:checked', dynamicDialog) )
              console.log(selectedIndex)
              $.post(ajaxurl, {
                'action': 'options_fields',
                'num_options': numOptions
              }, function(data) {
                $('#edit-answers-holder').empty().append(data);
                for (key in elementsJSON[index].label) {
                  $('[name="options[label][' + key + ']"]').val(elementsJSON[index].label[key])
                }
                if ( selectedIndex > -1) {
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
  html = '<div class="dyn-diag"><form class="pure-form pure-form-stacked form-horizontal" method="post" action=""><input name="options[name]" value="'
  html += obj.name.replace('\\','').replace('\\','').replace('\\','')
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
      var label = obj.label[key].replace(/\\/g,'')
      console.log(label)
      console.log(he.encode(label))
      var strippedLabel = he.encode(label)
      console.log(strippedLabel)
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
  return ( temp.length > 0 ) ? temp : null
}

function htmlentities(string, quote_style, charset, double_encode) {
  //  discuss at: http://phpjs.org/functions/htmlentities/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: nobbler
  // improved by: Jack
  // improved by: Rafał Kukawski (http://blog.kukawski.pl)
  // improved by: Dj (http://phpjs.org/functions/htmlentities:425#comment_134018)
  // bugfixed by: Onno Marsman
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //    input by: Ratheous
  //  depends on: get_html_translation_table
  //   example 1: htmlentities('Kevin & van Zonneveld');
  //   returns 1: 'Kevin &amp; van Zonneveld'
  //   example 2: htmlentities("foo'bar","ENT_QUOTES");
  //   returns 2: 'foo&#039;bar'

  var hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style),
    symbol = '';
  string = string == null ? '' : string + '';

  if (!hash_map) {
    return false;
  }

  if (quote_style && quote_style === 'ENT_QUOTES') {
    hash_map["'"] = '&#039;';
  }

  if ( !! double_encode || double_encode == null) {
    for (symbol in hash_map) {
      if (hash_map.hasOwnProperty(symbol)) {
        string = string.split(symbol)
          .join(hash_map[symbol]);
      }
    }
  } else {
    string = string.replace(/([\s\S]*?)(&(?:#\d+|#x[\da-f]+|[a-zA-Z][\da-z]*);|$)/g, function(ignore, text, entity) {
      for (symbol in hash_map) {
        if (hash_map.hasOwnProperty(symbol)) {
          text = text.split(symbol)
            .join(hash_map[symbol]);
        }
      }

      return text + entity;
    });
  }

  return string;
}

function get_html_translation_table(table, quote_style) {
  //  discuss at: http://phpjs.org/functions/get_html_translation_table/
  // original by: Philip Peterson
  //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: noname
  // bugfixed by: Alex
  // bugfixed by: Marco
  // bugfixed by: madipta
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: T.Wild
  // improved by: KELAN
  // improved by: Brett Zamir (http://brett-zamir.me)
  //    input by: Frank Forte
  //    input by: Ratheous
  //        note: It has been decided that we're not going to add global
  //        note: dependencies to php.js, meaning the constants are not
  //        note: real constants, but strings instead. Integers are also supported if someone
  //        note: chooses to create the constants themselves.
  //   example 1: get_html_translation_table('HTML_SPECIALCHARS');
  //   returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}

  var entities = {},
    hash_map = {},
    decimal;
  var constMappingTable = {},
    constMappingQuoteStyle = {};
  var useTable = {},
    useQuoteStyle = {};

  // Translate arguments
  constMappingTable[0] = 'HTML_SPECIALCHARS';
  constMappingTable[1] = 'HTML_ENTITIES';
  constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
  constMappingQuoteStyle[2] = 'ENT_COMPAT';
  constMappingQuoteStyle[3] = 'ENT_QUOTES';

  useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
  useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() :
    'ENT_COMPAT';

  if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
    throw new Error('Table: ' + useTable + ' not supported');
    // return false;
  }

  //entities['38'] = '&amp;';
  if (useTable === 'HTML_ENTITIES') {
    entities['160'] = '&nbsp;';
    entities['161'] = '&iexcl;';
    entities['162'] = '&cent;';
    entities['163'] = '&pound;';
    entities['164'] = '&curren;';
    entities['165'] = '&yen;';
    entities['166'] = '&brvbar;';
    entities['167'] = '&sect;';
    entities['168'] = '&uml;';
    entities['169'] = '&copy;';
    entities['170'] = '&ordf;';
    entities['171'] = '&laquo;';
    entities['172'] = '&not;';
    entities['173'] = '&shy;';
    entities['174'] = '&reg;';
    entities['175'] = '&macr;';
    entities['176'] = '&deg;';
    entities['177'] = '&plusmn;';
    entities['178'] = '&sup2;';
    entities['179'] = '&sup3;';
    entities['180'] = '&acute;';
    entities['181'] = '&micro;';
    entities['182'] = '&para;';
    entities['183'] = '&middot;';
    entities['184'] = '&cedil;';
    entities['185'] = '&sup1;';
    entities['186'] = '&ordm;';
    entities['187'] = '&raquo;';
    entities['188'] = '&frac14;';
    entities['189'] = '&frac12;';
    entities['190'] = '&frac34;';
    entities['191'] = '&iquest;';
    entities['192'] = '&Agrave;';
    entities['193'] = '&Aacute;';
    entities['194'] = '&Acirc;';
    entities['195'] = '&Atilde;';
    entities['196'] = '&Auml;';
    entities['197'] = '&Aring;';
    entities['198'] = '&AElig;';
    entities['199'] = '&Ccedil;';
    entities['200'] = '&Egrave;';
    entities['201'] = '&Eacute;';
    entities['202'] = '&Ecirc;';
    entities['203'] = '&Euml;';
    entities['204'] = '&Igrave;';
    entities['205'] = '&Iacute;';
    entities['206'] = '&Icirc;';
    entities['207'] = '&Iuml;';
    entities['208'] = '&ETH;';
    entities['209'] = '&Ntilde;';
    entities['210'] = '&Ograve;';
    entities['211'] = '&Oacute;';
    entities['212'] = '&Ocirc;';
    entities['213'] = '&Otilde;';
    entities['214'] = '&Ouml;';
    entities['215'] = '&times;';
    entities['216'] = '&Oslash;';
    entities['217'] = '&Ugrave;';
    entities['218'] = '&Uacute;';
    entities['219'] = '&Ucirc;';
    entities['220'] = '&Uuml;';
    entities['221'] = '&Yacute;';
    entities['222'] = '&THORN;';
    entities['223'] = '&szlig;';
    entities['224'] = '&agrave;';
    entities['225'] = '&aacute;';
    entities['226'] = '&acirc;';
    entities['227'] = '&atilde;';
    entities['228'] = '&auml;';
    entities['229'] = '&aring;';
    entities['230'] = '&aelig;';
    entities['231'] = '&ccedil;';
    entities['232'] = '&egrave;';
    entities['233'] = '&eacute;';
    entities['234'] = '&ecirc;';
    entities['235'] = '&euml;';
    entities['236'] = '&igrave;';
    entities['237'] = '&iacute;';
    entities['238'] = '&icirc;';
    entities['239'] = '&iuml;';
    entities['240'] = '&eth;';
    entities['241'] = '&ntilde;';
    entities['242'] = '&ograve;';
    entities['243'] = '&oacute;';
    entities['244'] = '&ocirc;';
    entities['245'] = '&otilde;';
    entities['246'] = '&ouml;';
    entities['247'] = '&divide;';
    entities['248'] = '&oslash;';
    entities['249'] = '&ugrave;';
    entities['250'] = '&uacute;';
    entities['251'] = '&ucirc;';
    entities['252'] = '&uuml;';
    entities['253'] = '&yacute;';
    entities['254'] = '&thorn;';
    entities['255'] = '&yuml;';
  }

  if (useQuoteStyle !== 'ENT_NOQUOTES') {
    entities['34'] = '&quot;';
  }
  if (useQuoteStyle === 'ENT_QUOTES') {
    entities['39'] = '&#39;';
  }
  entities['60'] = '&lt;';
  entities['62'] = '&gt;';

  // ascii decimals to real symbols
  for (decimal in entities) {
    if (entities.hasOwnProperty(decimal)) {
      hash_map[String.fromCharCode(decimal)] = entities[decimal];
    }
  }

  return hash_map;
}

