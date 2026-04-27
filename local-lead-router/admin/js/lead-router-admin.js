(function () {
	'use strict';

	function createRouteRow() {
		var labels = window.LLRAdmin || {};
		var servicePlaceholder = labels.servicePlaceholder || '';
		var emailPlaceholder = labels.emailPlaceholder || '';
		var removeRoute = labels.removeRoute || 'Remove';
		var row = document.createElement('tr');

		row.setAttribute('data-llr-route-row', '');
		row.innerHTML = [
			'<td><input class="regular-text" type="text" name="route_label[]" placeholder="' + escapeAttribute(servicePlaceholder) + '"></td>',
			'<td><input class="regular-text" type="email" name="route_email[]" placeholder="' + escapeAttribute(emailPlaceholder) + '"></td>',
			'<td class="llr-route-actions"><button type="button" class="button" data-llr-remove-route>' + escapeHtml(removeRoute) + '</button></td>'
		].join('');
		return row;
	}

	function escapeAttribute(value) {
		return String(value).replace(/[&<>"']/g, function (character) {
			return {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			}[character];
		});
	}

	function escapeHtml(value) {
		return escapeAttribute(value);
	}

	document.addEventListener('click', function (event) {
		var addButton = event.target.closest('[data-llr-add-route]');
		var removeButton = event.target.closest('[data-llr-remove-route]');
		var tableBody = document.querySelector('[data-llr-routes]');

		if (addButton && tableBody) {
			event.preventDefault();
			tableBody.appendChild(createRouteRow());
		}

		if (removeButton) {
			event.preventDefault();

			var row = removeButton.closest('[data-llr-route-row]');
			var rows = document.querySelectorAll('[data-llr-route-row]');

			if (row && rows.length > 1) {
				row.remove();
			}
		}
	});
}());
