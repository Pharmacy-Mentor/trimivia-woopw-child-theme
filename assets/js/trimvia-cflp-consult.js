/**
 * Trimvia consultation helpers for WooPW CFLP forms.
 *
 * WooPW 1.8 `frontend-script.js` owns v2 step navigation and `.cflp-v2-progress-head`.
 * This file only mirrors WooPW sidebar state into the Trimvia aside and styles radio pills.
 */
(function () {
	'use strict';

	function qs(sel, ctx) {
		return (ctx || document).querySelector(sel);
	}

	function qsa(sel, ctx) {
		return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
	}

	function escapeHtml(text) {
		var div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	function getCflpForm() {
		var mount = qs('.trimvia-consult-woo-form');
		return mount ? mount.querySelector('form.cflp-form') : null;
	}

	function isVisible(el) {
		return el && el.style.display !== 'none' && !el.classList.contains('cflp-v2-step-hidden');
	}

	/**
	 * Authoritative active step — form group visibility, not sidebar item classes.
	 * Sidebar classes are toggled during field validation and can briefly desync.
	 */
	function getActiveStepIndex(form) {
		var activeGroup = form.querySelector('.form-group-wrapper.cflp-v2-step-active');
		if (!activeGroup) {
			return null;
		}

		var stepIndex = parseInt(activeGroup.getAttribute('data-step-index') || '', 10);
		if (!Number.isNaN(stepIndex)) {
			return stepIndex;
		}

		var groups = qsa('.form-group-wrapper', form);
		var pos = groups.indexOf(activeGroup);
		return pos >= 0 ? pos : null;
	}

	function getSidebarItemStepIndex(item, fallbackIndex) {
		var stepIndex = parseInt(item.getAttribute('data-step-index') || '', 10);
		if (!Number.isNaN(stepIndex)) {
			return stepIndex;
		}
		stepIndex = parseInt(item.getAttribute('data-group-index') || '', 10);
		if (!Number.isNaN(stepIndex)) {
			return stepIndex;
		}
		return fallbackIndex;
	}

	function syncTrimviaSidebar(form) {
		var list = document.getElementById('trimvia-consult-progress-list');
		var woopwItems = form.querySelectorAll('#formSidebar .sidebar-item');
		if (!list || !woopwItems.length) {
			return;
		}

		var activeStepIndex = getActiveStepIndex(form);
		var rows = [];
		var stepNum = 0;

		woopwItems.forEach(function (item, itemIndex) {
			if (!isVisible(item)) {
				return;
			}
			stepNum++;
			var titleEl = item.querySelector('.sidebar-title');
			var label = titleEl ? titleEl.textContent.replace(/\s+/g, ' ').trim() : 'Step ' + stepNum;
			var itemStepIndex = getSidebarItemStepIndex(item, itemIndex);
			var isActive = activeStepIndex !== null && itemStepIndex === activeStepIndex;
			var rowClass = 'progress-step';
			if (isActive) {
				rowClass += ' active';
			} else if (item.classList.contains('cflp-v2-complete-step')) {
				rowClass += ' completed';
			}
			rows.push(
				'<div class="' +
					rowClass +
					'">' +
					'<span class="progress-step-num" aria-hidden="true">' +
					stepNum +
					'</span>' +
					'<span class="progress-step-label">' +
					escapeHtml(label || 'Step ' + stepNum) +
					'</span>' +
					'</div>'
			);
		});

		var nextHtml = rows.join('');
		if (list.innerHTML !== nextHtml) {
			list.innerHTML = nextHtml;
		}
	}

	/**
	 * Parent theme adds .active-button when #submitBtn enables; child footer overrides that script.
	 * Mirror it here so "Proceed to treatment" pulses once the form is complete.
	 */
	function initSubmitPulseWatcher(form) {
		var submitSelectors =
			'#submitBtn, .cflp-v2-submit, .cflp-v2-injected-submit, .sidebar-button.cflp_submit_form';

		function syncSubmitPulse(btn) {
			if (!btn) {
				return;
			}
			btn.classList.toggle('active-button', !btn.disabled);
		}

		function watchSubmitButton(btn) {
			if (!btn || btn.dataset.trimviaPulseWatch === '1') {
				return;
			}
			btn.dataset.trimviaPulseWatch = '1';
			syncSubmitPulse(btn);
			var observer = new MutationObserver(function () {
				syncSubmitPulse(btn);
			});
			observer.observe(btn, {
				attributes: true,
				attributeFilter: ['disabled'],
			});
		}

		qsa(submitSelectors, form).forEach(watchSubmitButton);

		if (form.dataset.trimviaPulseNavWatch === '1') {
			return;
		}
		form.dataset.trimviaPulseNavWatch = '1';

		var navObserver = new MutationObserver(function () {
			qsa(submitSelectors, form).forEach(watchSubmitButton);
		});
		qsa('.step-nav', form).forEach(function (nav) {
			navObserver.observe(nav, {
				childList: true,
				subtree: true,
			});
		});
	}

	function syncRadioPills(form) {
		qsa('.form-check.radio', form).forEach(function (wrap) {
			var input = wrap.querySelector('input[type="radio"]');
			var label = wrap.querySelector('label.form-check-label');
			if (!input || !label) {
				return;
			}
			var isChecked = input.checked;
			wrap.classList.toggle('trimvia-radio-selected', isChecked);
			label.classList.toggle('selected', isChecked);
		});
	}

	/**
	 * WooPW v2 enables `.cflp-v2-next` when the active step has a checked radio.
	 * Re-dispatch change so frontend-script.js syncState runs after pill clicks.
	 */
	function syncActiveStepNavigation(form) {
		if (!document.body.classList.contains('cflp-multistep-v2')) {
			return;
		}

		var activeGroup = form.querySelector('.form-group-wrapper.cflp-v2-step-active');
		if (!activeGroup) {
			return;
		}

		if (window.jQuery) {
			window.jQuery(activeGroup).find('input[type="radio"]:checked').first().trigger('change');
			return;
		}

		var nextBtn = activeGroup.querySelector('.cflp-v2-next');
		if (!nextBtn) {
			return;
		}

		var radioNames = {};
		var pendingRadio = false;
		qsa('.check-radio-group input[type="radio"]', activeGroup).forEach(function (input) {
			if (input.disabled || input.type === 'hidden') {
				return;
			}
			var question = input.closest('.form-input-group');
			if (question && question.style.display === 'none') {
				return;
			}
			if (!input.name || radioNames[input.name]) {
				return;
			}
			radioNames[input.name] = true;
			if (!form.querySelector('input[type="radio"][name="' + CSS.escape(input.name) + '"]:checked')) {
				pendingRadio = true;
			}
		});

		nextBtn.disabled = pendingRadio;
	}

	function wrapCheckboxLabelText(form) {
		qsa('.checkbox-group label.form-check-label', form).forEach(function (label) {
			if (label.querySelector('.trimvia-checkbox-label-text')) {
				return;
			}

			var input = label.querySelector('input[type="checkbox"]');
			if (!input) {
				return;
			}

			var textParts = [];
			Array.prototype.slice.call(label.childNodes).forEach(function (node) {
				if (node.nodeType === Node.TEXT_NODE && node.textContent.trim()) {
					textParts.push(node.textContent.trim());
					label.removeChild(node);
				}
			});

			if (!textParts.length) {
				return;
			}

			var span = document.createElement('span');
			span.className = 'trimvia-checkbox-label-text';
			span.textContent = textParts.join(' ');
			label.appendChild(span);
		});
	}

	function resetProgressStepScroll(form) {
		var indicator = form.querySelector('.cflp-v2-progress-head .step-indicator');
		if (!indicator || !indicator.classList.contains('step-indicator--few')) {
			return;
		}
		indicator.scrollLeft = 0;
	}

	var sidebarSyncTimer = null;

	function scheduleSidebarSync(form) {
		if (sidebarSyncTimer) {
			window.clearTimeout(sidebarSyncTimer);
		}
		sidebarSyncTimer = window.setTimeout(function () {
			sidebarSyncTimer = null;
			syncTrimviaSidebar(form);
		}, 120);
	}

	function syncAll(form) {
		if (document.body.classList.contains('cflp-multistep-v2')) {
			syncTrimviaSidebar(form);
			resetProgressStepScroll(form);
			syncActiveStepNavigation(form);
		}
		syncRadioPills(form);
		wrapCheckboxLabelText(form);
	}

	function boot() {
		var form = getCflpForm();
		if (!form) {
			return;
		}

		syncAll(form);
		initSubmitPulseWatcher(form);

		form.addEventListener('change', function (event) {
			if (event.target && event.target.matches('input[type="radio"]')) {
				syncRadioPills(form);
				syncActiveStepNavigation(form);
			}
		});

		form.addEventListener(
			'click',
			function (event) {
				var label = event.target.closest('.form-check.radio label.form-check-label');
				if (!label) {
					return;
				}
				window.setTimeout(function () {
					syncRadioPills(form);
					syncActiveStepNavigation(form);
				}, 0);
			},
			true
		);

		var groupObserver = new MutationObserver(function (mutations) {
			var stepChanged = false;

			mutations.forEach(function (mutation) {
				if (mutation.type !== 'attributes' || mutation.attributeName !== 'class') {
					return;
				}
				var target = mutation.target;
				if (!target.classList || !target.classList.contains('form-group-wrapper')) {
					return;
				}
				var wasActive =
					mutation.oldValue && mutation.oldValue.indexOf('cflp-v2-step-active') !== -1;
				var isActive = target.classList.contains('cflp-v2-step-active');
				if (wasActive !== isActive) {
					stepChanged = true;
				}
			});

			if (stepChanged) {
				syncTrimviaSidebar(form);
				return;
			}

			syncRadioPills(form);
		});

		qsa('.form-group-wrapper', form).forEach(function (wrap) {
			groupObserver.observe(wrap, {
				attributes: true,
				attributeFilter: ['class'],
				attributeOldValue: true,
			});
		});

		var sidebar = form.querySelector('#formSidebar');
		if (sidebar) {
			var sidebarObserver = new MutationObserver(function () {
				scheduleSidebarSync(form);
			});

			sidebarObserver.observe(sidebar, {
				attributes: true,
				childList: true,
				subtree: true,
				attributeFilter: ['class', 'style'],
			});
		}

		qsa('.cflp-v2-next, .cflp-v2-prev, .step-nav button', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				window.setTimeout(function () {
					syncAll(form);
				}, 50);
			});
		});

		// WooPW v2 init runs after DOMContentLoaded — resync once it has applied state.
		window.setTimeout(function () {
			syncAll(form);
			initSubmitPulseWatcher(form);
			resetProgressStepScroll(form);
		}, 350);
		window.setTimeout(function () {
			resetProgressStepScroll(form);
		}, 700);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
