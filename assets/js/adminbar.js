/* File: /assets/js/adminbar.js */
jQuery(function ($) {

	const $node = $('#wp-admin-bar-mtz-todo-adminbar');
	const $count = $node.find('.mtz-todo-count');

	if (!$count.length) return;

	$.post(MTZTodoAdminBar.ajaxUrl, {
		action: 'mtz_todo_adminbar_count',
		nonce: MTZTodoAdminBar.nonce
	}, function (resp) {

		if (!resp.success || !resp.data) return;

		const count = parseInt(resp.data.count, 10);
		if (!count) return;

		$count
			.text(count)
			.show();
	});
});