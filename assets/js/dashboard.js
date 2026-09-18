/* File: /assets/js/dashboard.js */
jQuery(function ($) {

	const tasksById = {};
	let tasksList = [];

	let sortState = { key: null, dir: 'asc' };
	let includeCompleted = false;

	function escapeHtml(str) {
		return $('<div>').text(str == null ? '' : String(str)).html();
	}

	/* ---------- SORTING ---------- */

	function compareDefault(a, b) {
		const pa = parseInt(a.priority || 0, 10);
		const pb = parseInt(b.priority || 0, 10);
		if (pa !== pb) return pa - pb;

		const da = (a.due_date || '').trim();
		const db = (b.due_date || '').trim();
		const ta = da ? Date.parse(da + 'T00:00:00') : Number.MAX_SAFE_INTEGER;
		const tb = db ? Date.parse(db + 'T00:00:00') : Number.MAX_SAFE_INTEGER;
		if (ta !== tb) return ta - tb;

		const tla = (a.title || '').toLowerCase();
		const tlb = (b.title || '').toLowerCase();
		if (tla < tlb) return -1;
		if (tla > tlb) return 1;
		return 0;
	}

	function compareByKey(a, b, key) {
		if (!key) return compareDefault(a, b);

		if (key === 'priority') {
			const pa = parseInt(a.priority || 0, 10);
			const pb = parseInt(b.priority || 0, 10);
			if (pa !== pb) return pa - pb;
			return compareDefault(a, b);
		}

		if (key === 'due_date') {
			const da = (a.due_date || '').trim();
			const db = (b.due_date || '').trim();
			const ta = da ? Date.parse(da + 'T00:00:00') : Number.MAX_SAFE_INTEGER;
			const tb = db ? Date.parse(db + 'T00:00:00') : Number.MAX_SAFE_INTEGER;
			if (ta !== tb) return ta - tb;
			return compareDefault(a, b);
		}

		if (key === 'title') {
			const ta = (a.title || '').toLowerCase();
			const tb = (b.title || '').toLowerCase();
			if (ta < tb) return -1;
			if (ta > tb) return 1;
			return compareDefault(a, b);
		}

		if (key === 'assigned_name') {
			const aa = (a.assigned_name || '').toLowerCase();
			const ab = (b.assigned_name || '').toLowerCase();
			if (aa < ab) return -1;
			if (aa > ab) return 1;
			return compareDefault(a, b);
		}

		return compareDefault(a, b);
	}

	function applySort(list) {
		const arr = list.slice();
		const key = sortState.key;
		const dir = sortState.dir;

		arr.sort((a, b) => {
			const cmp = compareByKey(a, b, key);
			return dir === 'desc' ? -cmp : cmp;
		});

		return arr;
	}

	function updateSortIcons() {
		$('#mtz-todo-table thead th.mtz-sort').each(function () {
			const th = $(this);
			const key = th.data('sort');
			const icon = th.find('.mtz-sort-icon');

			if (!icon.length) return;

			if (sortState.key === key) {
				icon.text(sortState.dir === 'desc' ? '↓' : '↑');
			} else {
				icon.text('↕');
			}
		});
	}

	/* ---------- RENDER ---------- */

	function renderTasks() {
		const body = $('#mtz-todo-body').empty();

		if (!tasksList.length) {
			body.append('<tr><td colspan="4">No to-dos found.</td></tr>');
			updateSortIcons();
			return;
		}

		const sorted = applySort(tasksList);

		sorted.forEach(t => {
			body.append(`
				<tr data-id="${t.id}" class="${t.completed ? 'mtz-completed-row' : ''}">
					<td>
						<a href="#" class="mtz-view">${escapeHtml(t.title)}</a>
					</td>
					<td>${escapeHtml(t.assigned_name)}</td>
					<td>${escapeHtml(t.due_date_display || '—')}</td>
					<td>${parseInt(t.priority || 0, 10) || ''}</td>
				</tr>
			`);
		});

		updateSortIcons();
	}

	/* ---------- MODALS ---------- */

	function openEditModal(task = null) {
		const isEdit = task && task.id;

		$('#mtz-task-id').val(isEdit ? task.id : 0);
		$('#mtz-title').val(isEdit ? task.title : '');
		$('#mtz-description').val(isEdit ? task.description : '');
		$('#mtz-related-search').val(isEdit ? (task.related_title || '') : '');
		$('#mtz-related-id').val(isEdit ? (task.related_id || '') : '');
		$('#mtz-assigned-user').val(isEdit ? task.assigned_user_id : MTZTodo.currentUserId);
		$('#mtz-due-date').val(isEdit ? (task.due_date || '') : '');
		$('#mtz-priority').val(isEdit ? task.priority : 3);
		$('#mtz-completed').prop('checked', isEdit ? !!task.completed : false);

		const buttons = {};
		buttons[isEdit ? 'Update To-Do' : 'Add To-Do'] = saveTask;

		if (isEdit) {
			buttons['Delete To-Do'] = function () {
				if (!confirm('Delete this to-do permanently?')) return;
				deleteTask(task.id);
			};
		}

		buttons.Cancel = function () {
			$(this).dialog('close');
		};

		$('#mtz-todo-modal').dialog({
			modal: true,
			width: 620,
			title: isEdit ? 'Edit To-Do' : 'Add To-Do',
			buttons: buttons
		});
	}

	function openViewModal(task) {
		if (!task || !task.id) return;

		$('#mtz-view-task-id').val(task.id);
		$('#mtz-view-title').text(task.title || '');
		$('#mtz-view-description').text(task.description || '');
		$('#mtz-view-assigned').text(task.assigned_name || '');
		$('#mtz-view-due').text(task.due_date_display || '—');
		$('#mtz-view-priority').text(task.priority || '');
		$('#mtz-view-completed-text').text(task.completed ? 'Yes' : 'No');

		if (task.related_edit_link && task.related_title) {
			$('#mtz-view-related-link')
				.attr('href', task.related_edit_link)
				.text(task.related_title);
			$('#mtz-view-related-row').show();
		} else {
			$('#mtz-view-related-row').hide();
		}

		$('#mtz-todo-view-modal').dialog({
			modal: true,
			width: 620,
			title: 'View To-Do',
			buttons: {
				'Edit To-Do': function () {
					$(this).dialog('close');
					openEditModal(task);
				},
				Close: function () {
					$(this).dialog('close');
				}
			}
		});
	}

	/* ---------- AJAX ---------- */

	function fetchTasks() {
		$.post(MTZTodo.ajaxUrl, {
			action: 'mtz_todo_list',
			nonce: MTZTodo.nonce,
			include_completed: includeCompleted ? 1 : 0
		}, function (resp) {

			tasksList = [];
			Object.keys(tasksById).forEach(k => delete tasksById[k]);

			if (!resp.success || !resp.data) {
				renderTasks();
				return;
			}

			resp.data.forEach(t => {
				tasksById[t.id] = t;
				tasksList.push(t);
			});

			renderTasks();
		});
	}

	function saveTask() {
		$.post(MTZTodo.ajaxUrl, {
			action: 'mtz_todo_save',
			nonce: MTZTodo.nonce,
			id: $('#mtz-task-id').val(),
			title: $('#mtz-title').val(),
			description: $('#mtz-description').val(),
			related_post_id: $('#mtz-related-id').val(),
			assigned_user_id: $('#mtz-assigned-user').val(),
			due_date: $('#mtz-due-date').val(),
			priority: $('#mtz-priority').val(),
			completed: $('#mtz-completed').is(':checked') ? 1 : 0
		}, function () {
			$('#mtz-todo-modal').dialog('close');
			fetchTasks();
		});
	}

	function deleteTask(id) {
		$.post(MTZTodo.ajaxUrl, {
			action: 'mtz_todo_delete',
			nonce: MTZTodo.nonce,
			id: id
		}, function () {
			$('#mtz-todo-modal').dialog('close');
			fetchTasks();
		});
	}

	/* ---------- EVENTS ---------- */

	$('#mtz-todo-add').on('click', () => openEditModal());

	$('#mtz-view-completed').on('change', function () {
		includeCompleted = $(this).is(':checked');
		fetchTasks();
	});

	$('#mtz-todo-body').on('click', '.mtz-view', function (e) {
		e.preventDefault();
		const id = $(this).closest('tr').data('id');
		openViewModal(tasksById[id]);
	});

	$('#mtz-todo-table thead').on('click', 'th.mtz-sort', function () {
		const key = $(this).data('sort');

		if (sortState.key === key) {
			sortState.dir = (sortState.dir === 'asc') ? 'desc' : 'asc';
		} else {
			sortState.key = key;
			sortState.dir = 'asc';
		}

		renderTasks();
	});

	$('#mtz-related-search').autocomplete({
		minLength: 2,
		appendTo: '#mtz-todo-modal',
		source(request, response) {
			$.post(MTZTodo.ajaxUrl, {
				action: 'mtz_todo_search_posts',
				nonce: MTZTodo.nonce,
				term: request.term
			}, function (resp) {
				response(resp.success ? resp.data : []);
			});
		},
		select(event, ui) {
			$('#mtz-related-id').val(ui.item.id);
		}
	});

	/* ---------- INIT ---------- */

	fetchTasks();
});