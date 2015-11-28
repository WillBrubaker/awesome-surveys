jQuery(document).ready(function($) {
	$('a.delete-results').on('click', function(e) {
		e.preventDefault()
		yesToDelete = confirm(wwm_edit_screen.confirm)
		if (yesToDelete) {
			$.post(ajaxurl, {
				action: 'wwm_delete_responses',
				'post_id': $(this).data('postid'),
				'nonce': $(this).data('nonce')
			}, function(data) {
				console.log(data)
				if (data.success) {
					location.reload()
				} else {
					alert(wwm_edit_screen.failure_message);
				}
			}).fail(function(xhr) {
				alert("Error: " + xhr.statusText)
			})
		}
	})
})
