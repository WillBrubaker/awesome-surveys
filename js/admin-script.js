jQuery('document').ready(function($) {

  $('#general-survey-options').addClass('pure-form pure-form-stacked')
  $('#survey-elements-buttons button').button().click(function(e) {
    e.preventDefault()
    $.post(ajaxurl, {
      action: 'add-form-element',
      element: $(this).attr('name')
    }, function(html) {
      $('#current-element').empty().append(html)
      $('#slider').slider({
        range: false,
        max: (wwm_as_admin_script.num_answers) ? wwm_as_admin_script.num_answers : 10,
        min: 1,
        step: 1,
        change: function(event, ui) {
          var numOptions = ui.value;
          var radios = $('[type="radio"]', $('#options-holder'))
          var selectedIndex = radios.index($('[type="radio"]:checked', $('#options-holder')))
          var values = []
          $('[name^="options[label]["]', $('#options-holder')).each(function() {
            values.push($(this).val())
          })
          $.post(ajaxurl, {
            'action': 'options-fields',
            'num_options': numOptions
          }, function(data) {
            $('#current-element #options-holder').empty().append(data.data);
            $('[name^="options[label]["]', $('#options-holder')).each(function(index) {
              $(this).val(values[index])
            })
            if (selectedIndex > -1) {
              $('[type="radio"]:eq(' + selectedIndex + ')', $('#options-holder')).prop('checked', true)
            }
          });
        }
      }).each(function() {
        var opt = $(this).data().uiSlider.options;
        var vals = opt.max - opt.min;
        for (var i = 0; i <= vals; i++) {
          var el = $('<label>' + (i + opt.min) + '</label>').css('left', (i / vals * 100) + '%');
          $("#slider").append(el);
        }
      })
      $('#current-element button').button().click(function(e) {
        e.preventDefault()
      })
    })
  })
})