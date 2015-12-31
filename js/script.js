function hideRecaptchaError(input) {
	jQuery('span#recaptcha-error').hide()
}
jQuery(document).ready(function($) {
	$('form.answer-survey input[type="submit"]').prop('disabled', false);
	$('form.answer-survey label.control-label > span.required').each(function() {
		var next = $(this).parent().next();
		$('input[type="checkbox"]', next).each(function() {
			$(this).prop('required', true);
		});
	});

	var currentPosition = $('form.answer-survey').position()
	$('form.answer-survey').each(function() {
		$(this).validate({
			errorPlacement: function(error, element) {
				var scope = element.closest($('.control-group'));
				var firstChild = $('> label', scope);
				error.insertBefore(firstChild);
			},
			submitHandler: function(form) {
				var overlay = $('.overlay', form);
				//don't submit if there is a captcha and it is not filled out
				if ($('div.g-recaptcha').length <= 0 || '' != $('#g-recaptcha-response').val()) {
					overlay.show();
					$.post(wwm_awesome_surveys.ajaxurl, $(form).serializeArray(), function(data) {
						if (null == data) {
							$(form).empty().append('<p class="error">An unknown error occured (data object is null)</p>');
							return null;
						}
						if (!data.success) {
							alert(data.data.error)
						} else {
							msg = ('undefined' != typeof data.data.thank_you) ? data.data.thank_you : '<span class="error">' +
								data.data + '</span>';
							$(form).empty().append('<p class="success">' + msg + '</p>');
							if (undefined != typeof data.data.url && data.data.url) {
								if (undefined != typeof data.data.urltimeout && data.data.urltimeout > 0) {
									setTimeout(function() {
										window.location.href = data.data.url;
									}, data.data.urltimeout * 1000);
								} else {
									window.location.href = data.data.url;
								}
							}
						}
					}, 'json').fail(function(xhr) {
						$(form).empty().append('<p class="error">There was an error. The error status code is: ' + xhr.status +
							' The error message is: ' + xhr.statusText + '</p>');
					}).always(function() {
						window.scroll(currentPosition.left, -currentPosition.top)
						overlay.hide();
					});
				} else if ($('span#recaptcha-error').length > 0) {
					$('span#recaptcha-error').show()
				}
			},
		});
	});
	$('form.answer-survey textarea[data-rule-maxlength][data-add_countdown]').each(function() {
		maxLength = $(this).data('rule-maxlength')
		if ('undefined' != typeof maxLength) {
			$('<div class="countdown"><span class="remaining-chars">' + maxLength + '</span> ' + wwm_awesome_surveys
				.countDownMessage +
				'</div>').insertAfter($(this))
		}
	}).keyup(function() {
		maxLength = $(this).data('rule-maxlength')
		curLength = $(this).val().length
		remaining = maxLength - curLength
		$('.remaining-chars', $(this).parent()).empty().append(remaining)
	})
});

jQuery('document').ready(function($) {
	$('[data-rule-conditional_on]').each(function() {
		$(this).closest('.control-group').hide()
	})
	$('form#pfbc select, form#pfbc input[type="radio"], form#pfbc input[type="checkbox"]').on('change', function() {
		var re = /(question)|(\[|\])/g
		if ('checkbox' === $(this).attr('type')) {
			$(this).parent().each(function() {
				var toggle = false
				loopqIndex = $(':first-child', this).attr('name').replace(re, '')
				loopselectedValue = $(':first-child', this).val()
				toggle = $(':first-child', this).is(':checked')
				$('[data-rule-conditional_on="[' + loopqIndex + '[' + loopselectedValue + ']]"').each(function() {
					$(this).closest('.control-group').each(function() {
						$('input', $(this)).each(function() {
							$(this).prop('checked', false).trigger('change')
							if ('text' === $(this).attr('type') || 'number' === $(this).attr('type') || 'email' === $(this)
								.attr('type')) {
								$(this).val('')
							}
						})
						$('textarea', $(this)).each(function() {
							$(this).val('')
						})
					})
					$(this).closest('.control-group').toggle(toggle)
				})
			})
		} else {
			var qIndex = $(this).attr('name').replace(re, '')
			var selectedValue = $(this).val()
			var show = false
			$('[data-rule-conditional_on^="[' + qIndex).each(function() {
				$(this).closest('.control-group').each(function() {
					$('input', $(this)).each(function() {
						$(this).prop('checked', false).trigger('change')
						if ('text' === $(this).attr('type') || 'number' === $(this).attr('type') || 'email' === $(this)
							.attr('type')) {
							$(this).val('')
						}
					})
					$('textarea', $(this)).each(function() {
						$(this).val('')
					})
				})
				$(this).closest('.control-group').hide()
			})
			$('[data-rule-conditional_on="[' + qIndex + '[' + selectedValue + ']]"').each(function() {
				show = false
				$('[name="question[' + qIndex + ']"]').each(function() {
					if ($(this).is(':checked')) {
						show = true
					}
				})
				if ($('[name="question[' + qIndex + ']"]').val() == selectedValue) {
					show = true
				}
				$(this).closest('.control-group').toggle(show)
			})
		}
	})
})
