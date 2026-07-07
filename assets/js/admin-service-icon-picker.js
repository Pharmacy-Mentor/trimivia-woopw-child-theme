(function ($) {
	'use strict';

	var config = window.trimviaServiceIconPicker || {};
	var iconCache = null;
	var modal = null;

	function stylePrefix(style) {
		if (style === 'brands') {
			return 'fa-brands';
		}
		if (style === 'regular') {
			return 'fa-regular';
		}
		return 'fa-solid';
	}

	function buildIconList(metadata) {
		var icons = [];

		Object.keys(metadata || {}).forEach(function (name) {
			var entry = metadata[name];
			if (!entry || !Array.isArray(entry.styles)) {
				return;
			}

			entry.styles.forEach(function (style) {
				var prefix = stylePrefix(style);
				var className = prefix + ' fa-' + name;
				var label = entry.label || name;

				icons.push({
					name: name,
					style: style,
					className: className,
					label: label,
					search: (label + ' ' + name + ' ' + className).toLowerCase()
				});
			});
		});

		icons.sort(function (a, b) {
			return a.label.localeCompare(b.label);
		});

		return icons;
	}

	function loadIcons() {
		if (iconCache) {
			return $.Deferred().resolve(iconCache).promise();
		}

		return $.getJSON(config.metadataUrl).then(function (metadata) {
			iconCache = buildIconList(metadata);
			return iconCache;
		});
	}

	function ensureModal() {
		if (modal) {
			return modal;
		}

		modal = $(
			'<div class="trimvia-fa-icon-picker-modal" aria-hidden="true">' +
				'<div class="trimvia-fa-icon-picker-backdrop"></div>' +
				'<div class="trimvia-fa-icon-picker-panel" role="dialog" aria-modal="true">' +
					'<div class="trimvia-fa-icon-picker-header">' +
						'<input type="search" class="trimvia-fa-icon-picker-search" placeholder="' + (config.labels.search || 'Search icons') + '">' +
						'<button type="button" class="button trimvia-fa-icon-picker-close">' + (config.labels.close || 'Close') + '</button>' +
					'</div>' +
					'<div class="trimvia-fa-icon-picker-body">' +
						'<p class="trimvia-fa-icon-picker-status">' + (config.labels.loading || 'Loading icons…') + '</p>' +
						'<div class="trimvia-fa-icon-picker-grid"></div>' +
					'</div>' +
				'</div>' +
			'</div>'
		).appendTo('body');

		modal.on('click', '.trimvia-fa-icon-picker-backdrop, .trimvia-fa-icon-picker-close', closeModal);
		modal.on('input', '.trimvia-fa-icon-picker-search', function () {
			renderGrid(modal.data('icons') || [], $(this).val());
		});

		return modal;
	}

	function closeModal() {
		if (!modal) {
			return;
		}
		modal.removeClass('is-open').attr('aria-hidden', 'true');
		modal.removeData('targetInput');
	}

	function renderGrid(icons, query) {
		var grid = modal.find('.trimvia-fa-icon-picker-grid');
		var status = modal.find('.trimvia-fa-icon-picker-status');
		var term = String(query || '').toLowerCase().trim();
		var filtered = icons;

		if (term) {
			filtered = icons.filter(function (icon) {
				return icon.search.indexOf(term) !== -1;
			});
		}

		grid.empty();

		if (!filtered.length) {
			status.text(config.labels.empty || 'No icons found.');
			return;
		}

		status.text(filtered.length + ' icons');

		filtered.forEach(function (icon) {
			var button = $(
				'<button type="button" class="trimvia-fa-icon-picker-item">' +
					'<i class="' + icon.className + '" aria-hidden="true"></i>' +
					'<span>' + icon.label + '</span>' +
				'</button>'
			);

			button.on('click', function () {
				var input = modal.data('targetInput');
				if (input && input.length) {
					input.val(icon.className).trigger('input').trigger('change');
					updatePreview(input);
				}
				closeModal();
			});

			grid.append(button);
		});
	}

	function openModal(input) {
		var pickerModal = ensureModal();

		pickerModal.data('targetInput', input);
		pickerModal.addClass('is-open').attr('aria-hidden', 'false');
		pickerModal.find('.trimvia-fa-icon-picker-search').val('').trigger('focus');

		loadIcons()
			.done(function (icons) {
				pickerModal.data('icons', icons);
				renderGrid(icons, '');
			})
			.fail(function () {
				pickerModal.find('.trimvia-fa-icon-picker-status').text('Unable to load Font Awesome icons.');
			});
	}

	function updatePreview(input) {
		var wrap = input.closest('.trimvia-fa-icon-field');
		var preview = wrap.find('.trimvia-fa-icon-preview');
		var value = String(input.val() || '').trim();

		preview.empty();
		if (value) {
			preview.append($('<i aria-hidden="true"></i>').attr('class', value));
		}
	}

	function enhanceField($field) {
		var inputWrap = $field.find('.acf-input');
		var input = inputWrap.find('input[type="text"]').first();

		if (!input.length || inputWrap.find('.trimvia-fa-icon-field').length) {
			return;
		}

		var shell = $('<div class="trimvia-fa-icon-field"></div>');
		var preview = $('<span class="trimvia-fa-icon-preview" aria-hidden="true"></span>');
		var browse = $('<button type="button" class="button trimvia-fa-icon-browse"></button>').text(config.labels.browse || 'Browse icons');

		input.detach();
		shell.append(preview, input, browse);
		inputWrap.prepend(shell);

		browse.on('click', function (event) {
			event.preventDefault();
			openModal(input);
		});

		input.on('input change', function () {
			updatePreview(input);
		});

		updatePreview(input);
	}

	function scanFields() {
		$('.acf-field[data-name="feature_icon_fa"], .acf-field[data-name="highlight_icon_fa"]').each(function () {
			enhanceField($(this));
		});
	}

	$(document).ready(scanFields);

	if (typeof acf !== 'undefined') {
		acf.addAction('ready', scanFields);
		acf.addAction('append', scanFields);
	}
})(jQuery);
