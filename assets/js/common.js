(() => {
  document.documentElement.classList.add("js");

  const ensureLegacyAdminAjaxGlobal = () => {
    const defaultAjaxUrl = "/wp-admin/admin-ajax.php";
    const fromTrimvia =
      window.trimviaConsultation &&
      typeof window.trimviaConsultation.ajaxUrl === "string" &&
      window.trimviaConsultation.ajaxUrl
        ? window.trimviaConsultation.ajaxUrl
        : "";

    if (!window.admin || typeof window.admin !== "object") {
      window.admin = {};
    }

    if (!window.admin.ajax || typeof window.admin.ajax !== "string") {
      window.admin.ajax = fromTrimvia || defaultAjaxUrl;
    }
  };

  const ensureLegacyJqueryModalSupport = () => {
    const jq = window.jQuery;
    if (!jq || !jq.fn) return;
    if (typeof jq.fn.modal === "function") return;

    const getBackdrop = () => document.querySelector(".modal-backdrop");

    const showModal = (modal) => {
      if (!modal) return;
      modal.classList.add("show");
      modal.style.display = "block";
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("modal-open");

      if (!getBackdrop()) {
        const backdrop = document.createElement("div");
        backdrop.className = "modal-backdrop fade show";
        document.body.appendChild(backdrop);
      }
    };

    const hideModal = (modal) => {
      if (!modal) return;
      modal.classList.remove("show");
      modal.style.display = "none";
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("modal-open");
      const backdrop = getBackdrop();
      if (backdrop) {
        backdrop.remove();
      }
    };

    jq.fn.modal = function modalCompat(action) {
      return this.each(function eachModal() {
        const node = this;
        if (action === "hide") {
          hideModal(node);
          return;
        }
        showModal(node);
      });
    };
  };

  const ensureGetOrderPrescriptionDataFallback = () => {
    if (typeof window.get_order_prescription_data === "function") return;

    const jq = window.jQuery;
    if (!jq) {
      window.get_order_prescription_data = () => {
        if (typeof window.orderModal === "function") window.orderModal("show");
      };
      return;
    }

    const resolveAjaxUrl = () => {
      if (window.admin && window.admin.ajax) return window.admin.ajax;
      if (window.trimviaConsultation && window.trimviaConsultation.ajaxUrl) return window.trimviaConsultation.ajaxUrl;
      return "/wp-admin/admin-ajax.php";
    };

    const resolveModal = () =>
      document.getElementById("practitioner-order-modal") ||
      document.getElementById("practitioner-order-prescription-modal") ||
      document.getElementById("prescription-extra-content-modal") ||
      document.getElementById("prescriber-order-modal");

    const renderIntoModal = (html) => {
      if (!html || typeof html !== "string") return;
      const modal = resolveModal();
      if (!modal) return;

      const target =
        modal.querySelector(".template-wrapper") ||
        modal.querySelector(".practitioner-order-under-review-wrapper") ||
        modal.querySelector(".practitioner-prescription-wrapper") ||
        modal.querySelector(".modal-body");

      if (target) target.innerHTML = html;
    };

    window.get_order_prescription_data = function getOrderPrescriptionDataFallback(orderId, actionType, eventOrElement) {
      const ajaxUrl = resolveAjaxUrl();
      const payload = {
        order_id: orderId || 0,
        action_type: actionType || "view-prescriptions",
      };

      const openModal = () => {
        if (typeof window.orderModal === "function") {
          window.orderModal("show");
        }
      };

      // Try the most likely AJAX action names used by parent/custom plugins.
      const actions = [
        "get_order_prescription_data",
        "get_prescription_data",
        "practitioner_order_prescription_data",
      ];

      let requested = false;
      actions.forEach((actionName) => {
        if (requested) return;
        requested = true;
        jq
          .ajax({
            type: "POST",
            url: ajaxUrl,
            dataType: "json",
            data: { ...payload, action: actionName },
          })
          .done((response) => {
            if (response && typeof response === "object") {
              if (typeof response.html === "string") renderIntoModal(response.html);
              if (typeof response.data === "string") renderIntoModal(response.data);
            } else if (typeof response === "string") {
              renderIntoModal(response);
            }
          })
          .always(() => {
            openModal();
          });
      });

      if (eventOrElement && typeof eventOrElement.preventDefault === "function") {
        eventOrElement.preventDefault();
      }

      // Open quickly even if endpoint differs.
      window.setTimeout(openModal, 60);
    };
  };

  // Global compatibility for legacy practitioner scripts that call orderModal()
  // before/without child initializers.
  if (typeof window.orderModal !== "function") {
    window.orderModal = (state) => {
      const modal =
        document.getElementById("practitioner-order-modal") ||
        document.getElementById("practitioner-order-prescription-modal") ||
        document.getElementById("prescription-extra-content-modal") ||
        document.getElementById("prescriber-order-modal");
      if (!modal) return;

      const shouldShow = state !== "hide";

      // Prefer Bootstrap modal API when present.
      if (window.bootstrap && typeof window.bootstrap.Modal === "function") {
        const instance = window.bootstrap.Modal.getOrCreateInstance(modal);
        if (shouldShow) {
          instance.show();
        } else {
          instance.hide();
        }
        return;
      }

      // Fallback behavior (no bootstrap JS available).
      modal.classList.toggle("show", shouldShow);
      modal.style.display = shouldShow ? "block" : "none";
      modal.setAttribute("aria-hidden", shouldShow ? "false" : "true");
      document.body.classList.toggle("modal-open", shouldShow);
      if (!shouldShow) {
        document.querySelectorAll(".modal-backdrop").forEach((node) => node.remove());
      }
    };
  }

  const initHeaderScroll = () => {
    const header = document.getElementById("header");
    if (!header) return;
    const onScroll = () => header.classList.toggle("scrolled", window.scrollY > 20);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  };

  const initRevealOnScroll = () => {
    const revealEls = document.querySelectorAll(".rv");
    if (!revealEls.length) return;
    if (typeof IntersectionObserver === "undefined") {
      revealEls.forEach((el) => el.classList.add("vis"));
      return;
    }
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("vis");
          observer.unobserve(entry.target);
        });
      },
      { threshold: 0.08, rootMargin: "0px 0px -60px 0px" }
    );
    revealEls.forEach((el) => observer.observe(el));
  };

  const initFaqAccordion = () => {
    const buttons = document.querySelectorAll(".fq-btn");
    if (!buttons.length) return;
    buttons.forEach((button) => {
      button.addEventListener("click", () => {
        const item = button.parentElement;
        if (!item) return;
        const wasActive = item.classList.contains("active");
        document.querySelectorAll(".fq").forEach((faq) => faq.classList.remove("active"));
        if (!wasActive) item.classList.add("active");
      });
    });
  };

  const initMobileMenu = () => {
    const header = document.querySelector(".header");
    const nav = document.querySelector(".nav");
    const actions = document.querySelector(".header-actions");
    const trigger = document.querySelector(".mobile-menu");
    if (!header || !nav || !actions || !trigger) return;

    const backdrop = document.createElement("div");
    backdrop.className = "mobile-nav-backdrop";

    const panel = document.createElement("aside");
    panel.className = "mobile-nav-panel";
    panel.setAttribute("aria-label", "Mobile navigation");

    const panelTop = document.createElement("div");
    panelTop.className = "mobile-nav-top";

    const panelTitle = document.createElement("h3");
    panelTitle.className = "mobile-nav-title";
    panelTitle.textContent = "Menu";

    const panelClose = document.createElement("button");
    panelClose.className = "mobile-nav-close";
    panelClose.type = "button";
    panelClose.setAttribute("aria-label", "Close menu");
    panelClose.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

    panelTop.append(panelTitle, panelClose);

    const navClone = nav.cloneNode(true);
    navClone.className = "mobile-nav-links";

    const actionsClone = actions.cloneNode(true);
    actionsClone.className = "mobile-nav-actions";

    panel.append(panelTop, navClone, actionsClone);
    document.body.append(backdrop, panel);

    const openClass = "mobile-nav-open";
    const subOpenClass = "trimvia-sub-open";

    trigger.setAttribute("aria-expanded", "false");
    trigger.setAttribute("aria-controls", "mobile-nav-panel");
    panel.id = "mobile-nav-panel";

    const close = () => {
      document.body.classList.remove(openClass);
      trigger.setAttribute("aria-expanded", "false");
      panel.querySelectorAll(`.menu-item-has-children.${subOpenClass}`).forEach((li) => li.classList.remove(subOpenClass));
    };

    const open = () => {
      document.body.classList.add(openClass);
      trigger.setAttribute("aria-expanded", "true");
    };

    trigger.addEventListener("click", () => {
      if (document.body.classList.contains(openClass)) {
        close();
        return;
      }
      open();
    });

    backdrop.addEventListener("click", close);
    panelClose.addEventListener("click", close);

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") close();
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth > 1024) close();
    });

    /** Namespaced: parent theme script.js removes `.open` on document clicks outside `.navigation-holder`; panel is on body. */
    panel.querySelectorAll(".menu-item-has-children > a").forEach((toggle) => {
      toggle.addEventListener("click", (event) => {
        const parent = toggle.parentElement;
        if (!parent || !parent.classList.contains("menu-item-has-children")) return;
        const submenu = parent.querySelector(":scope > .sub-menu, :scope > .mega-menu");
        if (!submenu) return;
        event.preventDefault();
        event.stopPropagation();
        const willOpen = !parent.classList.contains(subOpenClass);
        panel.querySelectorAll(`.menu-item-has-children.${subOpenClass}`).forEach((li) => {
          if (li !== parent) li.classList.remove(subOpenClass);
        });
        parent.classList.toggle(subOpenClass, willOpen);
      });
    });

    panel.querySelectorAll("a, button").forEach((el) => {
      if (el.closest(".nav-item") && el.matches(".nav-item > a")) return;
      if (el.closest(".menu-item-has-children") && el.matches(".menu-item-has-children > a")) {
        const p = el.parentElement;
        if (p && p.querySelector(":scope > .sub-menu, :scope > .mega-menu")) return;
      }
      el.addEventListener("click", close);
    });
  };

  const initSingleProductTabs = () => {
    const tablist = document.querySelector(".trimvia-single-product-tabs");
    if (!tablist) return;

    const tabs = tablist.querySelectorAll(".single-product-tab");
    const panels = document.querySelectorAll(".trimvia-single-product-panel");

    const syncPanels = () => {
      panels.forEach((panel) => {
        const on = panel.classList.contains("is-active");
        panel.toggleAttribute("hidden", !on);
        panel.setAttribute("aria-hidden", on ? "false" : "true");
      });
    };

    syncPanels();

    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const panelId = tab.getAttribute("data-panel");
        tabs.forEach((t) => {
          const on = t === tab;
          t.classList.toggle("is-active", on);
          t.setAttribute("aria-selected", on ? "true" : "false");
        });
        panels.forEach((p) => {
          const match = panelId && p.id === panelId;
          p.classList.toggle("is-active", !!match);
        });
        syncPanels();
      });
    });
  };

  const initCartQuantityUpdates = () => {
    const form = document.querySelector(".trimvia-cart-form");
    if (!form) return;

    const updateButton = form.querySelector('button[name="update_cart"]');
    const quantityInputs = form.querySelectorAll(".cart-item-quantity .qty");
    if (!updateButton || !quantityInputs.length) return;

    let updateTimer;
    const defaultButtonText = updateButton.textContent.trim() || "Update basket";

    const formatMoney = (amount, priceBox) => {
      const decimals = Number.parseInt(priceBox.dataset.priceDecimals || "2", 10);
      const decimalSeparator = priceBox.dataset.decimalSeparator || ".";
      const thousandSeparator = priceBox.dataset.thousandSeparator || ",";
      const symbol = priceBox.dataset.currencySymbol || "";
      const position = priceBox.dataset.currencyPosition || "left";
      const fixed = Number.isFinite(amount) ? amount.toFixed(Number.isFinite(decimals) ? decimals : 2) : "0.00";
      const parts = fixed.split(".");
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
      const value = parts.length > 1 ? `${parts[0]}${decimalSeparator}${parts[1]}` : parts[0];

      if (position === "right") return `${value}${symbol}`;
      if (position === "right_space") return `${value} ${symbol}`;
      if (position === "left_space") return `${symbol} ${value}`;
      return `${symbol}${value}`;
    };

    const previewLineSubtotal = (input) => {
      const cartItem = input.closest(".cart-item");
      const priceBox = cartItem ? cartItem.querySelector(".cart-item-price") : null;
      const total = priceBox ? priceBox.querySelector(".cart-item-price-total") : null;
      if (!priceBox || !total) return;

      const unitPrice = Number.parseFloat(priceBox.dataset.unitPrice || "0");
      const quantity = Number.parseFloat(input.value || "0");
      if (!Number.isFinite(unitPrice) || !Number.isFinite(quantity)) return;

      total.textContent = formatMoney(unitPrice * quantity, priceBox);
      priceBox.classList.add("is-previewing");
    };

    const markCartDirty = () => {
      updateButton.disabled = false;
      updateButton.removeAttribute("aria-disabled");
      updateButton.classList.add("is-active");
      updateButton.textContent = defaultButtonText;
    };

    const submitUpdatedCart = () => {
      markCartDirty();
      updateButton.textContent = "Updating...";
      updateButton.click();
    };

    quantityInputs.forEach((input) => {
      input.addEventListener("input", () => {
        window.clearTimeout(updateTimer);
        previewLineSubtotal(input);
        markCartDirty();
        updateTimer = window.setTimeout(submitUpdatedCart, 800);
      });

      input.addEventListener("change", () => {
        window.clearTimeout(updateTimer);
        previewLineSubtotal(input);
        submitUpdatedCart();
      });
    });
  };

  const initPractitionerOrderModalFix = () => {
    const actionSelector = '.practitioner-order-action, a[data-action_type="view-prescriptions"]';
    const modalIds = [
      "practitioner-order-modal",
      "practitioner-order-prescription-modal",
      "prescription-extra-content-modal",
      "prescriber-order-modal",
    ];
    const closeSelector = [
      "#practitioner-order-modal .close",
      "#practitioner-order-modal button.close",
      "#practitioner-order-modal .close-me",
      "#practitioner-order-modal .close-my-popup",
      "#practitioner-order-modal [data-bs-dismiss='modal']",
    ].join(", ");

    const getModal = () => {
      for (const id of modalIds) {
        const node = document.getElementById(id);
        if (node) return node;
      }
      return null;
    };

    const syncBackdrops = () => {
      const backdrops = Array.from(document.querySelectorAll(".modal-backdrop"));
      backdrops.forEach((backdrop, index) => {
        if (index < backdrops.length - 1) {
          backdrop.remove();
          return;
        }
        backdrop.style.zIndex = "1040";
      });
    };

    const makeModalInteractive = () => {
      const modal = getModal();
      if (!modal) return;

      modal.classList.add("show");
      modal.style.display = "block";
      modal.style.opacity = "1";
      modal.style.pointerEvents = "auto";
      modal.style.zIndex = "1062";
      modal.setAttribute("aria-hidden", "false");
      modal.setAttribute("aria-modal", "true");

      const dialog = modal.querySelector(".modal-dialog");
      if (dialog) {
        dialog.style.pointerEvents = "auto";
        dialog.style.zIndex = "1064";
      }

      const content = modal.querySelector(".modal-content, .popup-content-wrapper");
      if (content) {
        content.style.pointerEvents = "auto";
        content.style.opacity = "1";
        content.style.zIndex = "1065";
      }

      document.body.classList.add("modal-open");
      document.body.style.paddingRight = "0px";
      syncBackdrops();
    };

    const hideModal = () => {
      const modal = getModal();
      if (!modal) return;

      modal.classList.remove("show");
      modal.style.display = "none";
      modal.setAttribute("aria-hidden", "true");

      document.body.classList.remove("modal-open");
      Array.from(document.querySelectorAll(".modal-backdrop")).forEach((backdrop) => backdrop.remove());
    };

    // Keep behavior aligned with legacy flow.
    window.orderModal = (state) => {
      if ("hide" === state) {
        hideModal();
        return;
      }
      makeModalInteractive();
    };

    document.addEventListener("click", (event) => {
      const actionButton = event.target.closest(actionSelector);
      if (!actionButton) return;

      // Let parent flow run first (it usually injects modal content),
      // then recover only if modal did not open.
      window.setTimeout(() => {
        const modal = getModal();
        if (!modal) return;
        const isVisible =
          modal.classList.contains("show") ||
          modal.style.display === "block" ||
          modal.getAttribute("aria-hidden") === "false";
        if (!isVisible) {
          window.orderModal("show");
        }
      }, 120);
    });

    document.addEventListener("click", (event) => {
      const closeButton = event.target.closest(closeSelector);
      if (!closeButton) return;
      event.preventDefault();
      hideModal();
    });

    document.addEventListener("click", (event) => {
      const modal = getModal();
      if (!modal || modal.style.display === "none") return;
      if (event.target === modal) {
        hideModal();
      }
    });

    document.addEventListener("keydown", (event) => {
      if (event.key !== "Escape") return;
      const modal = getModal();
      if (!modal || modal.style.display === "none") return;
      hideModal();
    });
  };

  const initCommon = () => {
    ensureLegacyAdminAjaxGlobal();
    ensureLegacyJqueryModalSupport();
    ensureGetOrderPrescriptionDataFallback();
    initHeaderScroll();
    initMobileMenu();
    initRevealOnScroll();
    initFaqAccordion();
    initSingleProductTabs();
    initCartQuantityUpdates();
    initPractitionerOrderModalFix();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initCommon);
  } else {
    initCommon();
  }
})();
