(function () {
	'use strict';

	function onReady(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	onReady(function () {
		var cfg = window.trimviaConditionSearch;
		if (!cfg || !cfg.termId) {
			return;
		}

		var grid = document.querySelector('#trimvia-treatment-products .shop-grid');
		if (!grid) {
			return;
		}

		var inputs = document.querySelectorAll('.trimvia-condition-search-input');
		if (!inputs.length) {
			return;
		}

		var originalHtml = grid.innerHTML;
		var debounceTimer = null;
		var currentController = null;

		function setLoading(on) {
			grid.classList.toggle('trimvia-shop-grid--search-loading', on);
		}

		function applyHtml(html) {
			grid.innerHTML = html;
			// Reveal animation uses .rv + .vis (see common.js). Injected cards are never observed by
			// IntersectionObserver, so they would stay opacity:0 without .vis.
			grid.querySelectorAll('.product-card.rv').forEach(function (el) {
				el.classList.add('vis');
			});
		}

		function syncInputs(value, activeEl) {
			inputs.forEach(function (el) {
				if (el !== activeEl) {
					el.value = value;
				}
			});
		}

		function runSearch(value) {
			var q = (value || '').trim();

			if (!q) {
				if (currentController) {
					currentController.abort();
					currentController = null;
				}
				setLoading(false);
				grid.classList.remove('trimvia-shop-grid--no-results');
				applyHtml(originalHtml);
				return;
			}

			if (currentController) {
				currentController.abort();
			}
			currentController = new AbortController();
			setLoading(true);

			var body = new URLSearchParams();
			body.set('action', 'trimvia_condition_treatments_search');
			body.set('nonce', cfg.nonce);
			body.set('term_id', String(cfg.termId));
			body.set('s', q);

			fetch(cfg.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString(),
				signal: currentController.signal,
				credentials: 'same-origin',
			})
				.then(function (r) {
					return r.json();
				})
				.then(function (data) {
					setLoading(false);
					if (!data || !data.success || !data.data) {
						return;
					}
					var p = data.data;
					var count = parseInt(p.count, 10);
					var html = p.html || '';

					if (count < 1 || html === '') {
						grid.classList.add('trimvia-shop-grid--no-results');
						var msg = (cfg.i18n && cfg.i18n.noResults) ? cfg.i18n.noResults : '';
						applyHtml(
							'<p class="trimvia-condition-search-empty">' + msg + '</p>'
						);
					} else {
						grid.classList.remove('trimvia-shop-grid--no-results');
						applyHtml(html);
					}
				})
				.catch(function (err) {
					if (err.name === 'AbortError') {
						return;
					}
					setLoading(false);
				});
		}

		function schedule(activeEl) {
			var value = activeEl.value;
			syncInputs(value, activeEl);
			if (debounceTimer) {
				clearTimeout(debounceTimer);
			}
			debounceTimer = setTimeout(function () {
				debounceTimer = null;
				runSearch(value);
			}, 320);
		}

		inputs.forEach(function (el) {
			el.addEventListener('input', function () {
				schedule(el);
			});
		});
	});
})();
