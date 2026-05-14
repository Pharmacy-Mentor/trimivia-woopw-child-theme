/**
 * Sync Trimvia consultation sidebar with Gravity Forms multi-page progress.
 */
(function () {
	function getFormWrapper(context) {
		var wrap =
			(context && context.querySelector && context.querySelector('.gform_wrapper')) ||
			document.querySelector('.trimvia-consult-woo-form .gform_wrapper');
		return wrap || null;
	}

	function countPages(wrapper) {
		if (!wrapper) {
			return 1;
		}
		var pages = wrapper.querySelectorAll('.gform_page');
		return pages.length > 0 ? pages.length : 1;
	}

	function updateSidebar(currentPage, totalPages) {
		var list = document.getElementById('trimvia-consult-progress-list');
		if (!list) {
			return;
		}

		var steps = list.querySelectorAll('.progress-step');
		if (!steps.length) {
			return;
		}

		totalPages = Math.max(1, parseInt(totalPages, 10) || 1);
		currentPage = Math.max(1, parseInt(currentPage, 10) || 1);

		var activeIdx = Math.min(currentPage, steps.length) - 1;

		steps.forEach(function (el, i) {
			if (i >= totalPages) {
				el.hidden = true;
				el.classList.remove('active', 'completed');
				return;
			}
			el.hidden = false;
			el.classList.toggle('active', i === activeIdx);
			el.classList.toggle('completed', i < activeIdx);
		});

		var pct = Math.round((currentPage / totalPages) * 100);
		var fill = document.getElementById('trimvia-consult-progress-fill');
		var pctEl = document.getElementById('trimvia-consult-progress-pct');
		var curEl = document.getElementById('trimvia-consult-current-step');
		var totalEl = document.getElementById('trimvia-consult-step-total');
		var stepOfEl = document.getElementById('trimvia-consult-step-of');
		if (fill) {
			fill.style.width = pct + '%';
		}
		if (pctEl) {
			pctEl.textContent = pct + '%';
		}
		if (curEl) {
			curEl.textContent = String(currentPage);
		}
		if (totalEl) {
			totalEl.textContent = String(totalPages);
		}
		if (stepOfEl) {
			stepOfEl.style.display = totalPages <= 1 ? 'none' : '';
		}
	}

	function initialPageFromDom(wrapper) {
		if (!wrapper) {
			return 1;
		}
		var visible = null;
		var pages = wrapper.querySelectorAll('.gform_page');
		pages.forEach(function (p) {
			if (p.offsetParent !== null && window.getComputedStyle(p).display !== 'none') {
				visible = p;
			}
		});
		if (visible && visible.id) {
			var m = visible.id.match(/gform_page_(\d+)_(\d+)/);
			if (m) {
				return parseInt(m[2], 10) || 1;
			}
		}
		var stepActive = wrapper.querySelector('.gf_step.gf_step_active');
		if (stepActive) {
			var n = parseInt(stepActive.getAttribute('data-step') || stepActive.textContent, 10);
			return isNaN(n) ? 1 : n;
		}
		return 1;
	}

	function boot() {
		var mount = document.querySelector('.trimvia-consult-woo-form');
		if (!mount) {
			return;
		}

		if (mount.querySelector('form.cflp-form')) {
			return;
		}

		var wrapper = getFormWrapper(mount);
		var total = countPages(wrapper);
		var initial = initialPageFromDom(wrapper);

		var sidebar = document.getElementById('trimvia-assessment-progress');
		var card = document.querySelector('#consultationform .trimvia-consult-woo-card');
		if (sidebar && total <= 1) {
			sidebar.style.display = 'none';
			if (card) {
				card.classList.add('trimvia-consult-woo-card--single');
			}
		}

		updateSidebar(initial, total);

		if (typeof jQuery !== 'undefined') {
			jQuery(document).on('gform_page_loaded', function (event, form_id, current_page) {
				var w = document.getElementById('gform_wrapper_' + form_id);
				var t = countPages(w);
				updateSidebar(current_page, t);
			});

			jQuery(document).on('gform_post_render', function (event, form_id) {
				var w = document.getElementById('gform_wrapper_' + form_id);
				var t = countPages(w);
				var cur = initialPageFromDom(w);
				updateSidebar(cur, t);
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
