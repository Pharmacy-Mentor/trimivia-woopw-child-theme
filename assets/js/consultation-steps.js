(() => {
  const totalSteps = 4;
  let currentStep = 1;

  const scrollToForm = () => {
    const form = document.querySelector('.consult-form');
    if (form) {
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  };

  window.goToStep = (step) => {
    if (step < 1 || step > totalSteps) {
      return;
    }

    currentStep = step;
    document.querySelectorAll('.form-section').forEach((section) => section.classList.remove('active'));
    const target = document.querySelector(`.form-section[data-step="${step}"]`);
    if (target) {
      target.classList.add('active');
    }

    document.querySelectorAll('.step-ind').forEach((indicator, index) => {
      indicator.classList.remove('active', 'completed');
      if (index + 1 === currentStep) {
        indicator.classList.add('active');
      } else if (index + 1 < currentStep) {
        indicator.classList.add('completed');
      }
    });

    document.querySelectorAll('.progress-step').forEach((stepEl, index) => {
      stepEl.classList.remove('active', 'completed');
      if (index === currentStep - 1) {
        stepEl.classList.add('active');
      } else if (index < currentStep - 1) {
        stepEl.classList.add('completed');
      }
    });

    const percent = Math.round((currentStep / totalSteps) * 100);
    const fill = document.getElementById('progressFill');
    const percentEl = document.getElementById('progressPct');
    const currentStepEl = document.getElementById('currentStepNum');

    if (fill) {
      fill.style.width = `${percent}%`;
    }
    if (percentEl) {
      percentEl.textContent = `${percent}%`;
    }
    if (currentStepEl) {
      currentStepEl.textContent = `${currentStep}`;
    }

    scrollToForm();
  };

  document.querySelectorAll('.radio-pill').forEach((pill) => {
    pill.addEventListener('click', () => {
      const group = pill.closest('.radio-group');
      if (!group) {
        return;
      }
      group.querySelectorAll('.radio-pill').forEach((item) => item.classList.remove('selected'));
      pill.classList.add('selected');
    });
  });

  document.querySelectorAll('.checkbox-item').forEach((item) => {
    const input = item.querySelector('input');
    if (!input) {
      return;
    }
    input.addEventListener('change', () => {
      item.classList.toggle('selected', input.checked);
    });
  });

  document.querySelectorAll('.unit-toggle').forEach((toggle) => {
    toggle.querySelectorAll('.unit-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        toggle.querySelectorAll('.unit-btn').forEach((entry) => entry.classList.remove('active'));
        btn.classList.add('active');
      });
    });
  });
})();
