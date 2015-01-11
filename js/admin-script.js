jQuery('document').ready(function($) {
  $('#survey-elements-buttons button').button().click(function(e) {
    e.preventDefault()
    $.post(ajaxurl, {
      action: 'add-form-element',
      element: $(this).attr('name')
    }, function(html) {
      $('#current-element').empty().append(html)
      $('#current-element button').button().click(function(e) {
        e.preventDefault()
      })
    })
  })
})