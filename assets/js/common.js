(() => {
  document.documentElement.classList.add("js");

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
    const actionSelector = '.practitioner-order-action[data-action_type="view-prescriptions"]';
    const modalId = "practitioner-order-modal";
    const closeSelector = [
      "#practitioner-order-modal .close",
      "#practitioner-order-modal button.close",
      "#practitioner-order-modal .close-me",
      "#practitioner-order-modal .close-my-popup",
      "#practitioner-order-modal [data-bs-dismiss='modal']",
    ].join(", ");

    const getModal = () => document.getElementById(modalId);

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

    document.addEventListener("click", (event) => {
      const actionButton = event.target.closest(actionSelector);
      if (actionButton) {
        window.requestAnimationFrame(makeModalInteractive);
        window.setTimeout(makeModalInteractive, 120);
      }
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
