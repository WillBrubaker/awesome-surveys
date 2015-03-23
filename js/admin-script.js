jQuery('document').ready(function($) {

		if (typeof $('#form-preview').html() != 'undefined' && $('#form-preview').html().length > 0) {
				previewReady($)
		}

		$('#survey-elements-buttons button').button().click(function(e) {
				e.preventDefault()
				$.post(ajaxurl, {
						action: 'add-form-element',
						element: $(this).attr('name'),
						_as_nonce: $(this).data('nonce')
				}, function(html) {
						$('#current-element').empty().append(html)
						existingElements = $('#existing_elements')
						if ('undefined' != typeof existingElements && 'undefined' != typeof existingElements.val()) {
								$('#existing_elements').val(existingElements.val())
						}
						$('#current-element-wrapper').show()
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
						$('#current-element button').button()
						$('#current-element').on('click', 'button', function(e) {
								e.preventDefault()
								getPreview($)
						})
				}).fail(function(xhr) {
			alert('error status code: ' + xhr.status + ' error message: ' + xhr.statusText)
		})
		})

		$('#form-preview').on('click', 'button.element-edit', function(e) {
				e.preventDefault()
				action = $(this).attr('data-action')
				var index = $(this).data('index')
				var container = $(this).closest('.single-element-edit')
				var elementsJSON = $.parseJSON($('#existing_elements').val())
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
												elementsJSON = $.parseJSON($('#existing_elements').val())
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
																if ('undefined' != typeof elementsJSON[index].label) {
																		var target
																		newHtml = ''
																		type = elementsJSON[index].type
																		if ('dropdown' == type) {
																			target = $('select', container)
																			for (key in elementsJSON[index].value) {
																					newHtml += '<option value="' + key + '"'
																					if (key == elementsJSON[index]['default']) {
																							newHtml += ' selected="selected"'
																					}
																					newHtml += '>' + formValues.label[key] + '</option>'
																			}
																		} else {
																				target = $('.controls', container)
																				setName = $('[type="' + type + '"]:first', target).attr('name');
																				for (key in elementsJSON[index].value) {
																						newHtml += '<label class="' + type + '"><input type="' + type + '" name="' + setName + '" value="' + key + '"'
																						if (key == elementsJSON[index]['default']) {
																								newHtml += ' checked="checked"'
																						}
																						newHtml += '> ' + formValues.label[key] + '</label>'
																				}
																		}
																		target.empty().append(newHtml)
																}
																$('#existing_elements').val(JSON.stringify(elementsJSON)).trigger('change')
														}).fail(function(xhr) {
																alert('error status code: ' + xhr.status + ' error message: ' + xhr.statusText)
														}).always(function() {
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
										$('[name="options[name]"]', dynamicDialog).val(elementsJSON[index].name.replace(/\\/g, ''))
										textInputs = $('#edit-answers-holder [type="text"]', dynamicDialog)
										textInputs.each(function() {
												currentEl = textInputs.index($(this))
												$(this).val(elementsJSON[index].label[currentEl].replace(/\\/g, ''))
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
																'action': 'options-fields',
																'num_options': numOptions
														}, function(data) {
																$('#edit-answers-holder').empty().append(data.data);
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
				$('#existing_elements').val(JSON.stringify(elementsJSON)).trigger('change')
		})

		$('#form-preview').on('click', 'input[name="reset"]', function(e) {
				e.preventDefault()
				reset = confirm(commonL10n.warnDelete)
				if (true == reset) {
						$('#existing_elements').val('')
						$('#content').val('')
						$('#form-preview-wrapper').hide()
						$('#form-preview').empty()
				}
		})

		$('#current-element').on('keydown', function(e) {
				if (13 === e.keyCode) {
						e.preventDefault()
						$(this).trigger('blur')
						getPreview($)
				}
		})

		$('#existing_elements').on('change', function() {
				$.post(ajaxurl, {
						survey_id: $('#post_ID').val(),
						existing_elements: $('#existing_elements').val(),
						action: 'update-post-content'
				}, function(data) {
						$('#content').val(data.data)
				})
		})
})

function getPreview($) {
		$('#current-element :input').validate()
		if ($('#current-element :input').valid()) {
				$.post(ajaxurl, $('#awesome-survey :input').serializeArray(), function(data) {
						$('#form-preview').empty().append(data.data[0])
						$('#content').val(data.data[1])
						$('#existing_elements').val(data.data[2])
						previewReady($)
				})
		}
}

//added in 1.4.3 to properly reindex edit question and delete question buttons when questions re-ordered
function renumberButtons($) {
		var parent = $('.survey-preview form')
		$('.single-element-edit', parent).each(function() {
				$('.button-holder button[data-index]', $(this)).attr('data-index', $(this).index())
		})
		$('#existing_elements').trigger('change')
}

function previewReady($) {
		$('#form-preview button').button()
		$('#current-element').empty()
		$('#current-element-wrapper').hide()
		var sortables = $('#form-preview fieldset')
		sortables.sortable({
				start: function(event, ui) {
						surveyElements = $.parseJSON($('#existing_elements').val())
						startingIndex = ui.item.index()
				},
				stop: function(event, ui) {
						endingIndex = ui.item.index()
						if (startingIndex != endingIndex) {
								activeElement = surveyElements[startingIndex]
								surveyElements.splice(startingIndex, 1)
								surveyElements.splice(endingIndex, 0, activeElement)
								$('#existing_elements').val(JSON.stringify(surveyElements))
								renumberButtons($)
						}
				}
		})
		$('#form-preview input').each(function() {
						if ('button' != $(this).attr('type')) {
								$(this).prop('disabled', true)
						}
		})
		$('#form-preview-wrapper').show()
}

function generateDynamicDialog(obj) {
		html = '<div class="dyn-diag"><form class="pure-form pure-form-stacked form-horizontal" method="post" action=""><input type="text" name="options[name]" value="'
		html += '" required="required"><label for="required-checkbox">Required? </label><input id="required-checkbox" type="checkbox" name="options[validation][required]" value="1"'
		if (typeof obj.validation != 'undefined' && typeof obj.validation.required != 'undefined' && 1 == obj.validation.required) {
				html += ' checked="checked"'
		}
		html += '>'
		if ('undefined' != typeof obj.label) {
				display = ( 'undefined' != typeof obj.atts && 'undefined' != typeof obj.atts.can_add_options && 'yes' == obj.atts.can_add_options ) ? 'block' : 'none'
				html += '<div class="slider-wrapper" style="display:' + display + ';"><div id="edit-slider"></div><div class="slider-legend"></div></div>'
				html += '<p style="display:' + display + ';">answers:</p>'
				html += '<div id="edit-answers-holder" style="display:' + display + ';">'
				for (key in obj.label) {
						count = Number(key) + 1
						html += '<label for="options-answer-' + key + '">Answer ' + (Number(key) + 1) + '</label><input id="options-answer-' + key + '" type="text" name="options[label][' + key + ']" value="" required="required">'
						html += '<label for="options-default-' + key + '">default?<br></label><input id="options-default-' + key + '" type="radio" name="options[default]" value="' + key + '"'
						if (key == obj['default']) {
								html += ' checked="checked"'
						}
						html += '>'
				}
				html += '</div>'
		}
		if ('undefined' != typeof obj.validation && 'undefined' != typeof obj.validation.rules) {
				for (index in obj.validation.rules) {
						html += '<input type="hidden" name="options[validation][rules][' + index + ']" value="' + obj.validation.rules[index] + '">'
				}
		}
		html += '<input type="hidden" name="action" value="wwm-as-get-json">'
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