(function ($) {
	'use strict';

	if (typeof acf === 'undefined' || typeof trimviaServiceHighlightProduct === 'undefined') {
		return;
	}

	function getSiblingField($productField, name) {
		var $row = $productField.closest('.acf-row');
		if (!$row.length) {
			$row = $productField.closest('.acf-fields');
		}
		return $row.find('.acf-field[data-name="' + name + '"]').first();
	}

	function setTextFieldValue($field, value) {
		if (!$field.length) {
			return;
		}
		var $input = $field.find('input[type="text"], input[type="url"]').first();
		if ($input.length) {
			$input.val(value).trigger('change');
		}
	}

	function fillHighlightRow($productField, productId) {
		if (!productId) {
			return;
		}

		$.post(trimviaServiceHighlightProduct.ajaxUrl, {
			action: 'trimvia_service_highlight_product_data',
			nonce: trimviaServiceHighlightProduct.nonce,
			product_id: productId
		}).done(function (response) {
			if (!response || !response.success || !response.data) {
				return;
			}

			var data = response.data;
			var $titleField = getSiblingField($productField, 'highlight_title');
			var $subtitleField = getSiblingField($productField, 'highlight_subtitle');
			var $urlField = getSiblingField($productField, 'highlight_url');

			if (!$titleField.find('input').val()) {
				setTextFieldValue($titleField, data.title || '');
			}
			if (!$subtitleField.find('input').val()) {
				setTextFieldValue($subtitleField, data.subtitle || '');
			}
			if (!$urlField.find('input').val()) {
				setTextFieldValue($urlField, data.url || '');
			}
		});
	}

	function bindProductField($field) {
		if (!$field || $field.data('trimvia-highlight-product-bound')) {
			return;
		}

		$field.data('trimvia-highlight-product-bound', true);

		$field.on('change', 'select, input', function () {
			var productId = parseInt($field.find('select').val() || $field.find('input[type="hidden"]').val() || '0', 10);
			fillHighlightRow($field, productId);
		});
	}

	acf.addAction('ready_field/name=highlight_product', function ($field) {
		bindProductField($field);
	});

	acf.addAction('append_field/name=highlight_product', function ($field) {
		bindProductField($field);
	});
})(jQuery);
