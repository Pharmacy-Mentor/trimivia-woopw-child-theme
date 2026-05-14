/**
 * Sync Trimvia consultation chrome (step strip, progress bar, sidebar list) with CFLP multi-group forms.
 */
(function () {
	'use strict';

	function qs(sel, ctx) {
		return (ctx || document).querySelector(sel);
	}

	function qsa(sel, ctx) {
		return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
	}

	function getMount() {
		return qs('.trimvia-consult-woo-form');
	}

	function getCflpForm(mount) {
		return mount ? mount.querySelector('form.cflp-form') : null;
	}

	function stepLabelsFromGroups(form) {
		var wrappers = qsa('.form-group-wrapper', form);
		return wrappers.map(function (wrap) {
			var titleEl = wrap.querySelector('.form-group-title');
			if (!titleEl) {
				return '';
			}
			var clone = titleEl.cloneNode(true);
			qsa('.count-group', clone).forEach(function (n) {
				n.parentNode.removeChild(n);
			});
			return (clone.textContent || '').replace(/\s+/g, ' ').trim();
		});
	}

	function activeGroupIndex(form) {
		var wrappers = qsa('.form-group-wrapper', form);
		var idx = wrappers.findIndex(function (w) {
			return !w.classList.contains('deactive-group');
		});
		return idx >= 0 ? idx : 0;
	}

	function renderStepIndicator(container, labels, activeIdx) {
		if (!container) {
			return;
		}
		container.innerHTML = '';
		labels.forEach(function (label, i) {
			var step = document.createElement('div');
			step.className = 'step-ind';
			step.setAttribute('role', 'listitem');
			if (i === activeIdx) {
				step.classList.add('active');
			} else if (i < activeIdx) {
				step.classList.add('completed');
			}
			step.innerHTML =
				'<div class="step-ind-num">' +
				String(i + 1) +
				'</div><div class="step-ind-label">' +
				escapeHtml(label || 'Step ' + (i + 1)) +
				'</div>';
			container.appendChild(step);
		});
	}

	function escapeHtml(text) {
		var div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	function renderProgressList(listEl, labels, activeIdx) {
		if (!listEl || !labels.length) {
			return;
		}
		listEl.innerHTML = '';
		labels.forEach(function (label, i) {
			var row = document.createElement('div');
			row.className = 'progress-step';
			if (i === activeIdx) {
				row.classList.add('active');
			} else if (i < activeIdx) {
				row.classList.add('completed');
			}
			row.innerHTML =
				'<span class="progress-step-num" aria-hidden="true">' +
				String(i + 1) +
				'</span>' +
				'<span class="progress-step-label">' +
				escapeHtml(label || 'Step ' + (i + 1)) +
				'</span>';
			listEl.appendChild(row);
		});
	}

	function updateProgressBar(activeIdx, total) {
		var pct = total ? Math.round(((activeIdx + 1) / total) * 100) : 100;
		var fill = document.getElementById('trimvia-consult-progress-fill');
		var pctEl = document.getElementById('trimvia-consult-progress-pct');
		var curEl = document.getElementById('trimvia-consult-current-step');
		if (fill) {
			fill.style.width = pct + '%';
		}
		if (pctEl) {
			pctEl.textContent = pct + '%';
		}
		if (curEl) {
			curEl.textContent = String(activeIdx + 1);
		}
		var stepTotalEl = document.getElementById('trimvia-consult-step-total');
		if (stepTotalEl) {
			stepTotalEl.textContent = total ? String(total) : '1';
		}
		var stepOfEl = document.getElementById('trimvia-consult-step-of');
		if (stepOfEl) {
			stepOfEl.style.display = total <= 1 ? 'none' : '';
		}
	}

	function syncChrome(form) {
		var labels = stepLabelsFromGroups(form);
		if (!labels.length) {
			return;
		}
		var activeIdx = activeGroupIndex(form);
		var indicator = document.getElementById('trimvia-consult-step-indicator');
		var list = document.getElementById('trimvia-consult-progress-list');
		renderStepIndicator(indicator, labels, activeIdx);
		renderProgressList(list, labels, activeIdx);
		updateProgressBar(activeIdx, labels.length);
	}

	function boot() {
		var mount = getMount();
		var form = getCflpForm(mount);
		if (!form) {
			return;
		}

		syncChrome(form);

		var observer = new MutationObserver(function () {
			syncChrome(form);
		});
		observer.observe(form, {
			subtree: true,
			attributes: true,
			attributeFilter: ['class'],
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
