(() => {
  document.documentElement.classList.add("js");

  const PRESCRIBER_AJAX_ACTIONS = [
    "prescriber_sec_pin",
    "prescriber_signature",
    "prescriber_update_signature",
    "prescriber_auth_sess",
    "render_prescription_more_info_form",
    "process_prescription_more_info",
    "render_prescription_requested_info",
    "prescriber_verification",
    "prescriber_order_status_action",
    "prescriber_order_actions",
    "prescriber_view_prescription",
  ];

  const extractPrescriberAjaxAction = (data) => {
    if (typeof data === "string") {
      const match = data.match(/(?:^|&)action=([^&]+)/);
      return match ? decodeURIComponent(match[1].replace(/\+/g, " ")) : "";
    }
    if (data instanceof FormData) {
      return data.get("action") || "";
    }
    if (data && typeof data === "object") {
      return data.action || "";
    }
    return "";
  };

  const parsePrescriberAjaxResponse = (raw) => {
    if (raw == null || raw === "") {
      return null;
    }
    if (typeof raw === "object") {
      return raw;
    }

    const text = String(raw).trim();
    try {
      return JSON.parse(text);
    } catch (error) {
      const start = text.indexOf("{");
      const end = text.lastIndexOf("}");
      if (start !== -1 && end > start) {
        return JSON.parse(text.slice(start, end + 1));
      }
      throw error;
    }
  };

  const initPrescriberAjaxJsonCompat = () => {
    const jq = window.jQuery;
    if (!jq || typeof jq.ajaxPrefilter !== "function") {
      return;
    }

    jq.ajaxPrefilter((options, originalOptions) => {
      const action = extractPrescriberAjaxAction(originalOptions.data);
      if (!PRESCRIBER_AJAX_ACTIONS.includes(action)) {
        return;
      }

      options.dataType = "text";

      const originalSuccess = options.success;
      const originalError = options.error;

      options.success = function prescriberAjaxSuccess(data, textStatus, jqXHR) {
        let parsed;
        try {
          parsed = parsePrescriberAjaxResponse(data);
        } catch (error) {
          if (typeof originalError === "function") {
            originalError.call(this, jqXHR, "parsererror", error);
          }
          return;
        }

        jqXHR.responseJSON = parsed;
        if (typeof originalSuccess === "function") {
          originalSuccess.call(this, parsed, textStatus, jqXHR);
        }
      };

      options.error = function prescriberAjaxError(jqXHR, textStatus, errorThrown) {
        if (textStatus === "parsererror" && jqXHR && jqXHR.responseText) {
          try {
            const parsed = parsePrescriberAjaxResponse(jqXHR.responseText);
            jqXHR.responseJSON = parsed;
            if (typeof originalSuccess === "function") {
              originalSuccess.call(this, parsed, "success", jqXHR);
              return;
            }
          } catch (error) {
            // Fall through to the original error handler.
          }
        }

        if (typeof originalError === "function") {
          originalError.call(this, jqXHR, textStatus, errorThrown);
        }
      };
    });
  };

  initPrescriberAjaxJsonCompat();

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

  const PRACTITIONER_MODAL_IDS = [
    "practitioner-order-modal",
    "practitioner-order-prescription-modal",
    "prescription-extra-content-modal",
    "consultation-patient-modal",
  ];

  const movePractitionerModalsToBody = () => {
    PRACTITIONER_MODAL_IDS.forEach((id) => {
      const modal = document.getElementById(id);
      if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
      }
    });
  };

  const PRESCRIBER_ONBOARDING_POPUP_SELECTOR =
    ".prescriber-pin-gen-wrapper.make-popup, .prescriber-sign-gen-wrapper.make-popup";

  const movePrescriberOnboardingPopupsToBody = () => {
    const popups = document.querySelectorAll(PRESCRIBER_ONBOARDING_POPUP_SELECTOR);
    popups.forEach((popup) => {
      if (popup.parentElement !== document.body) {
        document.body.appendChild(popup);
      }
    });
  };

  const moveSignaturePopupToBody = () => {
    movePrescriberOnboardingPopupsToBody();
    const popup =
      document.querySelector(".prescriber-signature-modal .prescriber-sign-gen-wrapper.make-popup") ||
      document.querySelector("body > .prescriber-sign-gen-wrapper.make-popup:not(.d-none)") ||
      document.querySelector(".prescriber-sign-gen-wrapper.make-popup:not(.d-none)");
    if (popup && popup.parentElement !== document.body) {
      document.body.appendChild(popup);
    }
  };

  const closeSignaturePopup = () => {
    document.querySelectorAll(PRESCRIBER_ONBOARDING_POPUP_SELECTOR).forEach((popup) => {
      popup.remove();
    });
    const popup =
      document.querySelector("body > .prescriber-sign-gen-wrapper.make-popup") ||
      document.querySelector(".prescriber-sign-gen-wrapper.make-popup.presc-edit-popup") ||
      document.querySelector(".prescriber-signature-modal .prescriber-sign-gen-wrapper.make-popup");
    if (popup) {
      popup.remove();
    }
    const host = document.querySelector(".prescriber-signature-modal");
    if (host) {
      host.innerHTML = "";
    }
    document.body.classList.remove("presc-filter-page");
    document.querySelectorAll(".signature-modal.disabled").forEach((btn) => {
      btn.classList.remove("disabled");
      const loader = btn.querySelector(".loader");
      if (loader) {
        loader.style.display = "none";
      }
    });
  };

  const watchSignaturePopupMount = () => {
    if (typeof MutationObserver === "undefined") {
      return;
    }

    const observer = new MutationObserver(() => {
      movePrescriberOnboardingPopupsToBody();
      moveSignaturePopupToBody();
    });

    const host = document.querySelector(".prescriber-signature-modal");
    if (host) {
      observer.observe(host, { childList: true, subtree: true });
    }

    if (document.body) {
      observer.observe(document.body, { childList: true, subtree: false });
    }
  };

  const clearPrescriberModalSpinner = (modal) => {
    if (!modal) {
      return;
    }

    modal.classList.remove("spinner-added");
    modal.querySelectorAll(".fa-spin, .loader").forEach((loader) => {
      loader.style.display = "none";
    });
  };

  const syncPrescriberModalStacking = () => {
    movePractitionerModalsToBody();

    document.querySelectorAll(".modal-backdrop").forEach((backdrop) => {
      backdrop.style.zIndex = "100050";
    });

    PRACTITIONER_MODAL_IDS.forEach((id) => {
      const modal = document.getElementById(id);
      if (!modal || !modal.classList.contains("show")) {
        return;
      }

      modal.style.zIndex = "100060";
    });
  };

  const initPrescriberModalInteractionFix = () => {
    const onModalShown = (modal) => {
      if (!modal || !PRACTITIONER_MODAL_IDS.includes(modal.id)) {
        return;
      }

      movePractitionerModalsToBody();
      syncPrescriberModalStacking();
    };

    document.addEventListener("shown.bs.modal", (event) => {
      onModalShown(event.target);
    });

    const jq = window.jQuery;
    if (jq) {
      jq(document).on(
        "shown.bs.modal",
        PRACTITIONER_MODAL_IDS.map((id) => `#${id}`).join(", "),
        function onPrescriberModalShown() {
          onModalShown(this);
        }
      );

      const finishPrescriberAjaxLoading = (_event, _xhr, settings) => {
        const action = extractPrescriberAjaxAction(settings?.data);
        if (!PRESCRIBER_AJAX_ACTIONS.includes(action)) {
          return;
        }

        PRACTITIONER_MODAL_IDS.forEach((id) => {
          clearPrescriberModalSpinner(document.getElementById(id));
        });
        jq(".fa-spin").hide();
      };

      jq(document).ajaxComplete(finishPrescriberAjaxLoading);
      jq(document).ajaxError(finishPrescriberAjaxLoading);
    }
  };

  const ensureLegacyJqueryModalSupport = () => {
    const jq = window.jQuery;
    if (!jq || !jq.fn) return;
    if (typeof jq.fn.modal === "function") return;

    const getBackdrop = () => document.querySelector(".modal-backdrop");

    const showModal = (modal) => {
      if (!modal) return;
      movePractitionerModalsToBody();
      modal.classList.add("show");
      modal.style.display = "flex";
      modal.setAttribute("aria-hidden", "false");
      modal.setAttribute("aria-modal", "true");
      document.body.classList.add("modal-open");

      if (!getBackdrop()) {
        const backdrop = document.createElement("div");
        backdrop.className = "modal-backdrop fade show";
        document.body.appendChild(backdrop);
      }

      syncPrescriberModalStacking();
    };

    const hideModal = (modal) => {
      if (!modal) return;
      modal.classList.remove("show");
      modal.style.display = "none";
      modal.setAttribute("aria-hidden", "true");
      modal.removeAttribute("aria-modal");
      document.body.classList.remove("modal-open");
      document.querySelectorAll(".modal-backdrop").forEach((backdrop) => backdrop.remove());
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

  // Legacy orderModal helper — defer to WooPW jQuery/Bootstrap modal when available.
  if (typeof window.orderModal !== "function") {
    window.orderModal = (state) => {
      const modal =
        document.getElementById("practitioner-order-modal") ||
        document.getElementById("practitioner-order-prescription-modal") ||
        document.getElementById("prescription-extra-content-modal") ||
        document.getElementById("prescriber-order-modal");
      if (!modal) return;

      const jq = window.jQuery;
      if (jq && typeof jq.fn.modal === "function") {
        jq(modal).modal(state === "hide" ? "hide" : "show");
        return;
      }

      if (window.bootstrap && typeof window.bootstrap.Modal === "function") {
        const instance = window.bootstrap.Modal.getOrCreateInstance(modal);
        if (state === "hide") {
          instance.hide();
        } else {
          instance.show();
        }
        return;
      }

      const shouldShow = state !== "hide";
      modal.classList.toggle("show", shouldShow);
      modal.style.display = shouldShow ? "block" : "none";
      modal.setAttribute("aria-hidden", shouldShow ? "false" : "true");
      document.body.classList.toggle("modal-open", shouldShow);
      if (!shouldShow) {
        document.querySelectorAll(".modal-backdrop").forEach((node) => node.remove());
      }
    };
  }

  const initCommerceViewportLock = () => {
    const isCommercePage =
      document.body.classList.contains("trimvia-checkout-page") ||
      document.body.classList.contains("trimvia-cart-page");
    if (!isCommercePage) {
      return;
    }

    const resetHorizontalScroll = () => {
      if (window.scrollX !== 0) {
        window.scrollTo(0, window.scrollY);
      }
      document.documentElement.scrollLeft = 0;
      document.body.scrollLeft = 0;
    };

    resetHorizontalScroll();
    window.addEventListener("load", resetHorizontalScroll, { passive: true });
    window.addEventListener("pageshow", resetHorizontalScroll, { passive: true });
    window.addEventListener(
      "resize",
      () => {
        window.requestAnimationFrame(resetHorizontalScroll);
      },
      { passive: true }
    );

    const jq = window.jQuery;
    if (jq) {
      jq(document.body).on("updated_checkout updated_cart_totals", resetHorizontalScroll);
    }
  };

  const initHeaderScroll = () => {
    const header = document.getElementById("header");
    if (!header) return;
    const isSolidHeaderPage =
      document.body.classList.contains("consultation-page");

    const onScroll = () => {
      if (isSolidHeaderPage) {
        header.classList.add("scrolled");
        return;
      }
      header.classList.toggle("scrolled", window.scrollY > 20);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  };

  const initScrollToTop = () => {
    const button = document.getElementById("trimviaScrollTop");
    if (!button) return;

    const showThreshold = 400;

    const updateVisibility = () => {
      const visible = window.scrollY > showThreshold;
      button.hidden = !visible;
      button.classList.toggle("is-visible", visible);
    };

    button.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });

    updateVisibility();
    window.addEventListener("scroll", updateVisibility, { passive: true });
  };

  const initHomeStickyConsultCta = () => {
    const bar = document.getElementById("trimviaHomeStickyCta");
    if (!bar) return;

    const showThreshold = 280;
    const mobileQuery = window.matchMedia("(max-width: 1024px)");

    const updateVisibility = () => {
      if (!mobileQuery.matches) {
        bar.hidden = true;
        bar.classList.remove("is-visible");
        document.body.classList.remove("trimvia-home-sticky-cta-visible");
        return;
      }

      const visible = window.scrollY > showThreshold;
      bar.hidden = !visible;
      bar.classList.toggle("is-visible", visible);
      document.body.classList.toggle("trimvia-home-sticky-cta-visible", visible);
    };

    updateVisibility();
    window.addEventListener("scroll", updateVisibility, { passive: true });
    if (typeof mobileQuery.addEventListener === "function") {
      mobileQuery.addEventListener("change", updateVisibility);
    } else if (typeof mobileQuery.addListener === "function") {
      mobileQuery.addListener(updateVisibility);
    }
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

    // WooCommerce ajax (shipping method change, cart totals, checkout review,
    // coupons) replaces markup after page load; the replacement .rv nodes were
    // never observed, so they stay at opacity 0 and the panel looks removed.
    if (window.jQuery) {
      window.jQuery(document.body).on(
        "updated_shipping_method updated_cart_totals updated_wc_div updated_checkout applied_coupon removed_coupon",
        () => {
          document
            .querySelectorAll(".trimvia-cart-section .rv:not(.vis), .cart_totals .rv:not(.vis), form.checkout .rv:not(.vis)")
            .forEach((el) => el.classList.add("vis"));
        }
      );
    }
  };

  const initFaqAccordion = () => {
    const buttons = document.querySelectorAll(".fq-btn");
    if (!buttons.length) return;
    buttons.forEach((button) => {
      if (button.dataset.trimviaFaqBound === "1") {
        return;
      }
      button.dataset.trimviaFaqBound = "1";
      button.addEventListener("click", () => {
        const item = button.parentElement;
        if (!item) return;
        const wasActive = item.classList.contains("active");
        const scope = item.closest(".faq-list, .trimvia-service-content-faq-list") || document;
        scope.querySelectorAll(".fq").forEach((faq) => faq.classList.remove("active"));
        if (!wasActive) item.classList.add("active");
      });
    });
  };

  const initServiceContentFaqs = () => {
    const articles = document.querySelectorAll(".service-singular-section .article-content");
    articles.forEach((article) => {
      if (article.dataset.trimviaFaqConverted === "1") {
        return;
      }

      const faqTables = [...article.querySelectorAll("table")].filter((table) => table.querySelector("details"));
      if (!faqTables.length) {
        return;
      }

      faqTables.forEach((faqTable) => {
        const details = [...faqTable.querySelectorAll("details")];
        if (!details.length) {
          return;
        }

        const faqList = document.createElement("div");
        faqList.className = "faq-list trimvia-service-content-faq-list";

        details.forEach((detail, index) => {
          const summary = detail.querySelector("summary");
          const content = [...detail.children].find((child) => child.tagName !== "SUMMARY");
          const item = document.createElement("div");
          item.className = index === 0 ? "fq active" : "fq";

          const button = document.createElement("button");
          button.type = "button";
          button.className = "fq-btn";
          button.appendChild(document.createTextNode(summary ? summary.textContent.trim() : ""));

          const chevron = document.createElement("div");
          chevron.className = "fq-chev";
          chevron.innerHTML =
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"></path></svg>';
          button.appendChild(chevron);

          const answerWrap = document.createElement("div");
          answerWrap.className = "fq-a";
          const answerInner = document.createElement("div");
          answerInner.className = "fq-a-in";
          if (content) {
            answerInner.innerHTML = content.innerHTML;
          }
          answerWrap.appendChild(answerInner);

          item.appendChild(button);
          item.appendChild(answerWrap);
          faqList.appendChild(item);
        });

        const wrapper = document.createElement("div");
        wrapper.className = "trimvia-service-content-faq";

        const headerNodes = [];
        let previous = faqTable.previousElementSibling;
        while (previous) {
          const tagName = previous.tagName;
          if (tagName === "H2") {
            headerNodes.unshift(previous);
            break;
          }
          if (tagName === "P") {
            const text = previous.textContent.replace(/\s+/g, " ").trim();
            if (text) {
              headerNodes.unshift(previous);
            }
            previous = previous.previousElementSibling;
            continue;
          }
          break;
        }

        if (headerNodes.length) {
          const faqCenter = document.createElement("div");
          faqCenter.className = "faq-center";

          headerNodes.forEach((node) => {
            if (node.tagName === "P") {
              const label = document.createElement("div");
              label.className = "stag trimvia-service-content-faq-label";
              label.textContent = node.textContent.replace(/\s+/g, " ").trim();
              faqCenter.appendChild(label);
              node.remove();
              return;
            }

            if (node.tagName === "H2") {
              const title = document.createElement("h2");
              title.className = "stitle";
              title.textContent = node.textContent.replace(/\s+/g, " ").trim();
              faqCenter.appendChild(title);
              node.remove();
            }
          });

          wrapper.appendChild(faqCenter);
        }

        wrapper.appendChild(faqList);
        faqTable.replaceWith(wrapper);
      });

      article.dataset.trimviaFaqConverted = "1";
    });
  };

  const isLoggedInVisitor = () =>
    document.body.classList.contains("logged-in") ||
    /(?:^|;\s*)wordpress_logged_in_[^=]+=/.test(document.cookie || "");

  const syncHeaderAuthButtons = () => {
    document.querySelectorAll("[data-trimvia-auth-btn]").forEach((button) => {
      const loginText = button.getAttribute("data-login-text") || "Login";
      const accountText = button.getAttribute("data-account-text") || "My Account";
      const loginUrl = button.getAttribute("data-login-url") || button.getAttribute("href") || "";
      const accountUrl = button.getAttribute("data-account-url") || loginUrl;

      if (isLoggedInVisitor()) {
        button.textContent = accountText;
        if (accountUrl) {
          button.setAttribute("href", accountUrl);
        }
        return;
      }

      button.textContent = loginText;
      if (loginUrl) {
        button.setAttribute("href", loginUrl);
      }
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

    const headerLogo = document.querySelector(".header .logo");
    const panelBrand = headerLogo ? headerLogo.cloneNode(true) : null;
    if (panelBrand) {
      panelBrand.classList.add("mobile-nav-logo");
    } else {
      const panelTitle = document.createElement("h3");
      panelTitle.className = "mobile-nav-title";
      panelTitle.textContent = "Menu";
      panelTop.append(panelTitle);
    }

    const panelClose = document.createElement("button");
    panelClose.className = "mobile-nav-close";
    panelClose.type = "button";
    panelClose.setAttribute("aria-label", "Close menu");
    panelClose.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

    if (panelBrand) {
      panelTop.append(panelBrand, panelClose);
    } else {
      panelTop.append(panelClose);
    }

    const navClone = nav.cloneNode(true);
    navClone.className = "mobile-nav-links";

    const actionsClone = actions.cloneNode(true);
    actionsClone.className = "mobile-nav-actions";
    actionsClone.querySelectorAll(".btn-basket, .btn-ghost").forEach((el) => el.remove());

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
    if (panelBrand) {
      panelBrand.addEventListener("click", close);
    }

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") close();
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth > 1024) close();
    });

    /** Parent link navigates; chevron toggles submenu (desktop-like behaviour). */
    panel.querySelectorAll(".menu-item-has-children > a").forEach((link) => {
      const parent = link.parentElement;
      if (!parent || !parent.classList.contains("menu-item-has-children")) return;
      const submenu = parent.querySelector(":scope > .sub-menu, :scope > .mega-menu");
      if (!submenu) return;

      const chevron = link.querySelector(".chevron");
      if (chevron) {
        chevron.setAttribute("role", "button");
        chevron.setAttribute("aria-label", "Toggle submenu");
        chevron.setAttribute("tabindex", "0");
      }

      const toggleSubmenu = (event) => {
        if (event) {
          event.preventDefault();
          event.stopPropagation();
        }
        const willOpen = !parent.classList.contains(subOpenClass);
        panel.querySelectorAll(`.menu-item-has-children.${subOpenClass}`).forEach((li) => {
          if (li !== parent) li.classList.remove(subOpenClass);
        });
        parent.classList.toggle(subOpenClass, willOpen);
        if (chevron) {
          chevron.setAttribute("aria-expanded", willOpen ? "true" : "false");
        }
      };

      if (chevron) {
        chevron.setAttribute("aria-expanded", "false");
        chevron.addEventListener("click", toggleSubmenu);
        chevron.addEventListener("keydown", (event) => {
          if (event.key === "Enter" || event.key === " ") {
            toggleSubmenu(event);
          }
        });
      }

      link.addEventListener("click", (event) => {
        // Tap on chevron only expands/collapses — handled above.
        if (event.target.closest(".chevron")) {
          event.preventDefault();
          event.stopPropagation();
          return;
        }
        // Tap on label navigates to the parent page.
        close();
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

  const initCartNoticeAlignment = () => {
    const sync = () => {
      document
        .querySelectorAll(
          ".trimvia-cart-notices .woocommerce-message, .trimvia-cart-page .woocommerce-notices-wrapper .woocommerce-message"
        )
        .forEach((notice) => {
          notice.querySelectorAll(":scope > li").forEach((item) => {
            item.style.display = "flex";
            item.style.alignItems = "center";
            item.style.minHeight = "36px";
          });

          if (notice.querySelector(":scope > li")) {
            return;
          }

          notice.querySelectorAll("a.restore-item").forEach((link) => {
            if (link.nextSibling && link.nextSibling.nodeType === Node.TEXT_NODE) {
              const trailing = link.nextSibling.textContent || "";
              if (trailing.trim() === "?") {
                link.append("?");
                link.nextSibling.remove();
              }
            }
          });
        });
    };

    if (!document.querySelector(".trimvia-cart-notices, .trimvia-cart-page .woocommerce-notices-wrapper")) {
      return;
    }

    sync();

    const jq = window.jQuery;
    if (jq) {
      jq(document.body).on(
        "updated_wc_div removed_from_cart added_to_cart applied_coupon removed_coupon",
        () => window.setTimeout(sync, 0)
      );
    }
  };

  const initCartCouponMobilePlaceholder = () => {
    const input = document.querySelector("#coupon_code.cart-coupon-input");
    if (!input) return;

    const desktopPlaceholder =
      input.getAttribute("placeholder") || input.dataset.trimviaPlaceholderDesktop || "";
    const mobilePlaceholder = input.dataset.trimviaPlaceholderMobile || desktopPlaceholder;
    const mq = window.matchMedia("(max-width: 640px)");

    const syncPlaceholder = () => {
      input.setAttribute("placeholder", mq.matches ? mobilePlaceholder : desktopPlaceholder);
    };

    if (!input.dataset.trimviaPlaceholderDesktop && desktopPlaceholder) {
      input.dataset.trimviaPlaceholderDesktop = desktopPlaceholder;
    }

    mq.addEventListener("change", syncPlaceholder);
    syncPlaceholder();
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

  const initTestimonialCarousel = () => {
    const carousels = document.querySelectorAll(".testi-sec .tg, .tg-carousel");
    if (!carousels.length) return;

    const navIconPrev =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>';
    const navIconNext =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>';

    const getSlidesPerView = () => {
      if (window.matchMedia("(max-width: 640px)").matches) return 1;
      if (window.matchMedia("(max-width: 1024px)").matches) return 2;
      return 3;
    };

    carousels.forEach((grid) => {
      if (grid.dataset.carouselInit === "true") return;

      const cards = Array.from(grid.querySelectorAll(":scope > .tc"));
      if (!cards.length) return;

      grid.dataset.carouselInit = "true";
      grid.classList.add("tg-carousel");

      const viewport = document.createElement("div");
      viewport.className = "tg-carousel__viewport";
      const track = document.createElement("div");
      track.className = "tg-carousel__track";

      cards.forEach((card) => {
        const slide = document.createElement("div");
        slide.className = "tg-carousel__slide";
        slide.appendChild(card);
        track.appendChild(slide);
      });

      viewport.appendChild(track);

      const prevButton = document.createElement("button");
      prevButton.type = "button";
      prevButton.className = "tg-carousel__nav tg-carousel__nav--prev";
      prevButton.setAttribute("aria-label", "Previous review");
      prevButton.innerHTML = navIconPrev;

      const nextButton = document.createElement("button");
      nextButton.type = "button";
      nextButton.className = "tg-carousel__nav tg-carousel__nav--next";
      nextButton.setAttribute("aria-label", "Next review");
      nextButton.innerHTML = navIconNext;

      const dotsWrap = document.createElement("div");
      dotsWrap.className = "tg-carousel__dots";
      dotsWrap.setAttribute("role", "tablist");
      dotsWrap.setAttribute("aria-label", "Review slides");

      grid.replaceChildren(prevButton, viewport, nextButton, dotsWrap);

      let currentIndex = 0;
      let touchStartX = 0;
      let touchDeltaX = 0;

      const getGap = () => {
        const styles = window.getComputedStyle(track);
        return parseFloat(styles.columnGap || styles.gap || "0") || 0;
      };

      const getStep = () => {
        const slide = track.querySelector(".tg-carousel__slide");
        if (!slide) return 0;
        return slide.getBoundingClientRect().width + getGap();
      };

      const getMaxIndex = () => Math.max(0, cards.length - getSlidesPerView());

      const renderDots = () => {
        dotsWrap.replaceChildren();
        const pages = getMaxIndex() + 1;
        if (pages <= 1) return;

        for (let index = 0; index < pages; index += 1) {
          const dot = document.createElement("button");
          dot.type = "button";
          dot.className = "tg-carousel__dot";
          dot.setAttribute("aria-label", `Go to review slide ${index + 1}`);
          dot.setAttribute("aria-current", index === currentIndex ? "true" : "false");
          if (index === currentIndex) dot.classList.add("is-active");
          dot.addEventListener("click", () => {
            currentIndex = index;
            update();
          });
          dotsWrap.appendChild(dot);
        }
      };

      const update = () => {
        const slidesPerView = getSlidesPerView();
        const maxIndex = getMaxIndex();
        currentIndex = Math.min(currentIndex, maxIndex);

        track.style.transform = `translate3d(-${currentIndex * getStep()}px, 0, 0)`;

        const showControls = cards.length > slidesPerView;
        prevButton.hidden = !showControls;
        nextButton.hidden = !showControls;
        dotsWrap.hidden = !showControls;

        prevButton.disabled = currentIndex <= 0;
        nextButton.disabled = currentIndex >= maxIndex;

        dotsWrap.querySelectorAll(".tg-carousel__dot").forEach((dot, index) => {
          const isActive = index === currentIndex;
          dot.classList.toggle("is-active", isActive);
          dot.setAttribute("aria-current", isActive ? "true" : "false");
        });
      };

      prevButton.addEventListener("click", () => {
        currentIndex = Math.max(0, currentIndex - 1);
        update();
      });

      nextButton.addEventListener("click", () => {
        currentIndex = Math.min(getMaxIndex(), currentIndex + 1);
        update();
      });

      viewport.addEventListener(
        "touchstart",
        (event) => {
          if (!event.changedTouches.length) return;
          touchStartX = event.changedTouches[0].clientX;
          touchDeltaX = 0;
        },
        { passive: true }
      );

      viewport.addEventListener(
        "touchmove",
        (event) => {
          if (!event.changedTouches.length) return;
          touchDeltaX = event.changedTouches[0].clientX - touchStartX;
        },
        { passive: true }
      );

      viewport.addEventListener(
        "touchend",
        () => {
          if (Math.abs(touchDeltaX) < 40) return;
          if (touchDeltaX < 0) {
            currentIndex = Math.min(getMaxIndex(), currentIndex + 1);
          } else {
            currentIndex = Math.max(0, currentIndex - 1);
          }
          update();
        },
        { passive: true }
      );

      let resizeTimer;
      window.addEventListener("resize", () => {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(() => {
          renderDots();
          update();
        }, 120);
      });

      renderDots();
      update();
    });
  };

  const initSingleProductGallery = () => {
    if (!document.body.classList.contains("trimvia-single-product-page")) {
      return;
    }

    const galleryWrap = document.querySelector(".single-product-gallery");
    const main = document.querySelector(".single-product-main");
    const gallery = main ? main.querySelector(".woocommerce-product-gallery") : null;
    if (!galleryWrap || !main || !gallery) {
      return;
    }

    const refreshGalleryViewport = () => {
      const viewport = gallery.querySelector(".flex-viewport");
      if (!viewport) {
        return;
      }

      // CSS aspect-ratio owns the frame height so the image can stay full-bleed.
      viewport.style.height = "";
    };

    const syncGalleryLayout = () => {
      const thumbs = gallery.querySelector(".flex-control-thumbs");
      if (thumbs && thumbs.parentElement !== galleryWrap) {
        thumbs.classList.add("single-product-thumbs");
        galleryWrap.appendChild(thumbs);
      }

      gallery.querySelectorAll(".woocommerce-product-gallery__image img").forEach((slideImg) => {
        slideImg.loading = "eager";
      });

      refreshGalleryViewport();

      if (window.jQuery) {
        const slider = window.jQuery(gallery).data("flexslider");
        if (slider && typeof slider.resize === "function") {
          slider.resize();
        }
      }
    };

    syncGalleryLayout();

    galleryWrap.addEventListener("click", (event) => {
      if (!event.target.closest(".flex-control-thumbs li, .single-product-thumbs li")) {
        return;
      }
      window.setTimeout(refreshGalleryViewport, 40);
      window.setTimeout(refreshGalleryViewport, 280);
    });

    if (window.jQuery) {
      window.jQuery(document.body).on(
        "woocommerce_gallery_init_zoom wc-product-gallery-after-init found_variation reset_data flexslider-before flexslider-after",
        () => {
          syncGalleryLayout();
          refreshGalleryViewport();
        }
      );
      window.jQuery(gallery).on("flexslider-after", refreshGalleryViewport);
    }

    window.addEventListener("resize", refreshGalleryViewport);
    window.setTimeout(syncGalleryLayout, 120);
    window.setTimeout(refreshGalleryViewport, 160);
    window.setTimeout(syncGalleryLayout, 600);
    window.setTimeout(refreshGalleryViewport, 640);
  };

  const initCheckoutStickySummary = () => {
    if (!document.body.classList.contains("trimvia-checkout-page")) {
      return;
    }

    const mq = window.matchMedia("(min-width: 1025px)");
    const layout = document.querySelector("form.checkout.checkout-layout");
    const column = layout?.querySelector(".trimvia-checkout-summary-col");
    const sidebar = column?.querySelector(".trimvia-checkout-summary");
    if (!layout || !column || !sidebar) {
      return;
    }

    const sidebarWidth = 380;
    let raf = 0;

    const getTopOffset = () => {
      const header = document.getElementById("header");
      const adminBar = document.getElementById("wpadminbar");
      let top = header ? Math.round(header.getBoundingClientRect().height) + 16 : 88;
      if (adminBar) {
        top += Math.round(adminBar.getBoundingClientRect().height);
      }
      return top;
    };

    const getPinnedLeft = () => {
      const layoutRect = layout.getBoundingClientRect();
      return Math.round(layoutRect.right - sidebarWidth);
    };

    const clearPinnedStyles = () => {
      sidebar.classList.remove("is-checkout-summary-fixed", "is-checkout-summary-anchored");
      sidebar.style.position = "";
      sidebar.style.top = "";
      sidebar.style.left = "";
      sidebar.style.right = "";
      sidebar.style.width = "";
      sidebar.style.zIndex = "";
      column.style.minHeight = "";
    };

    const update = () => {
      if (!mq.matches) {
        clearPinnedStyles();
        return;
      }

      const top = getTopOffset();
      const layoutRect = layout.getBoundingClientRect();
      const sidebarHeight = sidebar.offsetHeight;

      if (layoutRect.top > top) {
        clearPinnedStyles();
        return;
      }

      column.style.minHeight = `${sidebarHeight}px`;
      const pinnedLeft = getPinnedLeft();

      if (layoutRect.bottom <= top + sidebarHeight) {
        column.style.minHeight = `${layout.offsetHeight}px`;
        sidebar.classList.remove("is-checkout-summary-fixed");
        sidebar.classList.add("is-checkout-summary-anchored");
        sidebar.style.position = "absolute";
        sidebar.style.top = `${Math.max(layout.offsetHeight - sidebarHeight, 0)}px`;
        sidebar.style.right = "0";
        sidebar.style.left = "auto";
        sidebar.style.width = `${sidebarWidth}px`;
        sidebar.style.zIndex = "5";
        return;
      }

      sidebar.classList.remove("is-checkout-summary-anchored");
      sidebar.classList.add("is-checkout-summary-fixed");
      sidebar.style.position = "fixed";
      sidebar.style.top = `${top}px`;
      sidebar.style.left = `${pinnedLeft}px`;
      sidebar.style.width = `${sidebarWidth}px`;
      sidebar.style.zIndex = "5";
    };

    const schedule = () => {
      cancelAnimationFrame(raf);
      raf = requestAnimationFrame(update);
    };

    mq.addEventListener("change", schedule);
    window.addEventListener("resize", schedule);
    window.addEventListener("scroll", schedule, { passive: true });
    window.addEventListener("load", schedule);
    if (window.jQuery) {
      window.jQuery(document.body).on("updated_checkout", schedule);
    }

    schedule();
  };

  const initCheckoutDeliveryPanel = () => {
    if (
      !document.body.classList.contains("trimvia-checkout-page") ||
      document.body.classList.contains("trimvia-order-pay-page") ||
      !window.jQuery
    ) {
      return;
    }

    const $ = window.jQuery;
    // Key renamed when checkbox semantics flipped (checked = different address),
    // so states saved under the old meaning are not read back inverted.
    const sameAsBillingStorageKey = "trimvia_checkout_ship_different";

    const getDeliveryRefs = () => ({
      panel: document.querySelector(".trimvia-checkout-panel--shipping"),
      shipDiffInput: document.getElementById("trimvia_ship_to_different_address"),
      sameAsBillingInput: document.getElementById("trimvia_same_as_billing"),
      shippingAddress: document.querySelector(".trimvia-checkout-shipping-address"),
    });

    const billingToShippingMap = [
      ["billing_first_name", "shipping_first_name"],
      ["billing_last_name", "shipping_last_name"],
      ["billing_company", "shipping_company"],
      ["billing_address_1", "shipping_address_1"],
      ["billing_address_2", "shipping_address_2"],
      ["billing_city", "shipping_city"],
      ["billing_state", "shipping_state"],
      ["billing_postcode", "shipping_postcode"],
      ["billing_country", "shipping_country"],
    ];

    const isLocalPickup = (methodId) => String(methodId || "").indexOf("local_pickup") === 0;

    const getSelectedShippingMethod = () => {
      const checked = document.querySelector('#shipping_method input[type="radio"]:checked');
      return checked ? checked.value : "";
    };

    const copyBillingToShipping = (overwriteEmptyOnly) => {
      billingToShippingMap.forEach(([billingId, shippingId]) => {
        const billingField = document.getElementById(billingId);
        const shippingField = document.getElementById(shippingId);

        if (!billingField || !shippingField) {
          return;
        }

        const billingValue = billingField.value || "";
        if (!overwriteEmptyOnly || !String(shippingField.value || "").trim()) {
          shippingField.value = billingValue;
        }
      });
    };

    const syncDeliveryPanel = () => {
      const { panel, shipDiffInput, sameAsBillingInput, shippingAddress } = getDeliveryRefs();
      if (!panel) {
        return;
      }

      const localPickup = isLocalPickup(getSelectedShippingMethod());
      let storedSameAsBilling = null;
      try {
        storedSameAsBilling = window.sessionStorage?.getItem(sameAsBillingStorageKey);
      } catch (error) {
        storedSameAsBilling = null;
      }

      if (sameAsBillingInput && !localPickup && storedSameAsBilling !== null) {
        sameAsBillingInput.checked = storedSameAsBilling === "1";
      }

      // Checkbox semantics: checked = deliver to a DIFFERENT address (fields expand).
      const shipDifferent = !!sameAsBillingInput?.checked;
      const sameAsBilling = !shipDifferent;
      const hideShippingFields = localPickup || sameAsBilling;

      panel.classList.toggle("is-collapsed", localPickup);
      panel.hidden = localPickup;
      panel.classList.toggle("is-same-as-billing", !localPickup && sameAsBilling);

      if (shippingAddress) {
        shippingAddress.hidden = hideShippingFields;
        shippingAddress.style.display = hideShippingFields ? "none" : "";
      }

      if (shipDiffInput) {
        shipDiffInput.value = hideShippingFields ? "0" : "1";
        shipDiffInput.disabled = localPickup;
      }

      if (sameAsBillingInput) {
        sameAsBillingInput.disabled = localPickup;
        if (localPickup) {
          sameAsBillingInput.checked = false;
        }
      }

      if (!localPickup) {
        copyBillingToShipping(hideShippingFields ? false : true);
      }
    };

    $(document.body).on("change", '#shipping_method input[type="radio"]', syncDeliveryPanel);
    $(document.body).on("change", "#trimvia_same_as_billing", function onSameAsBillingChange() {
      try {
        window.sessionStorage?.setItem(sameAsBillingStorageKey, this.checked ? "1" : "0");
      } catch (error) {
        // Ignore storage failures; the hidden WooCommerce field still updates immediately.
      }
      syncDeliveryPanel();
    });
    $(document.body).on(
      "input change",
      ".woocommerce-billing-fields input, .woocommerce-billing-fields select",
      () => {
        const { panel, sameAsBillingInput } = getDeliveryRefs();
        if (panel && !panel.hidden && sameAsBillingInput && !sameAsBillingInput.checked) {
          copyBillingToShipping(false);
        }
      }
    );
    $(document.body).on("updated_checkout", syncDeliveryPanel);

    syncDeliveryPanel();
  };

  const removeSidebarGpDuplicates = () => {
    document.querySelectorAll(".trimvia-checkout-summary .woo-gp-form-wrapper").forEach((node) => {
      node.remove();
    });
  };

  const initCheckoutGpForm = () => {
    if (document.body.classList.contains("trimvia-order-pay-page")) {
      return;
    }

    removeSidebarGpDuplicates();

    const gpForm = document.querySelector(".trimvia-checkout-gp-section .trimvia-gp-form");
    if (!gpForm || !window.jQuery) {
      return;
    }

    const $ = window.jQuery;
    const $form = $(gpForm);
    const $checkoutForm = $("form.checkout");
    const $radios = $form.find('input[name="gp_surgery"]');
    const $panels = $form.find(".trimvia-gp-panel");
    const $searchSelect = $form.find(".gp-surgery-selector");
    const $manualName = $form.find("#gp-surgery-name");
    const $manualAddress = $form.find("#gp-surgery-full-address");
    const $addressSubmit = $form.find("#gp-surgery-address-submit");
    const $emailField = $form.find("#gp-surgery-email");

    const syncGpAddressSubmit = () => {
      const mode = $radios.filter(":checked").val() || "nhs";

      if (mode === "manual") {
        $addressSubmit.val(($manualAddress.val() || "").trim());
        return;
      }

      if (mode === "nhs") {
        if ($searchSelect.data("select2")) {
          const selected = $searchSelect.select2("data");
          const selectedSurgery = selected && selected.length ? selected[0] : null;

          if (selectedSurgery) {
            $addressSubmit.val(selectedSurgery.address || selectedSurgery.id || selectedSurgery.text || "");
            if (!$manualName.val()) {
              $manualName.val(selectedSurgery.org_name || "");
            }
            if (!$emailField.val()) {
              $emailField.val(selectedSurgery.email || "");
            }
            return;
          }
        }

        $addressSubmit.val(($searchSelect.val() || "").trim());
        return;
      }

      $addressSubmit.val("");
    };

    const resetGpFields = () => {
      $emailField.val("");
      $manualName.val("");
      $manualAddress.val("");
      $addressSubmit.val("");
      if ($searchSelect.data("select2")) {
        $searchSelect.val("").trigger("change");
      } else {
        $searchSelect.val("");
      }
    };

    let activeGpMode = $radios.filter(":checked").val() || "nhs";

    const syncGpPanels = (resetFields) => {
      const mode = $radios.filter(":checked").val() || "nhs";

      if (resetFields && mode !== activeGpMode) {
        resetGpFields();
      }
      activeGpMode = mode;

      $panels.each(function syncPanel() {
        const $panel = $(this);
        const isActive = $panel.data("gp-panel") === mode;
        $panel.toggleClass("is-active", isActive);
        if (isActive) {
          $panel.removeAttr("hidden");
        } else {
          $panel.attr("hidden", "hidden");
        }
      });

      $form.find(".trimvia-gp-option").each(function syncOption() {
        const $option = $(this);
        const $input = $option.find('input[name="gp_surgery"]');
        $option.toggleClass("is-selected", $input.is(":checked"));
      });

      $manualName.prop("disabled", mode === "current");
      $manualAddress.prop("disabled", mode !== "manual");
      $searchSelect.prop("disabled", mode !== "nhs").trigger("change.select2");
      syncGpAddressSubmit();
    };

    const prepareGpFormForSubmit = () => {
      removeSidebarGpDuplicates();
      syncGpPanels(false);
      syncGpAddressSubmit();
    };

    if ($searchSelect.length && typeof $searchSelect.select2 === "function" && !$searchSelect.data("select2")) {
      $searchSelect.select2({
        placeholder: "Start typing to find your GP surgery",
        width: "100%",
        minimumInputLength: 4,
        dropdownParent: $(document.body),
        ajax: {
          delay: 1000,
          url: "https://api.nhs.uk/service-search/search?api-version=1",
          type: "post",
          crossDomain: true,
          dataType: "json",
          headers: {
            "subscription-key": "8a497723c41543b7b133a997edd3cd3e",
            "Content-Type": "application/json",
            "Access-Control-Allow-Origin": "*",
          },
          data(params) {
            return JSON.stringify({
              searchFields: "OrganisationName,Address1,City,County,Postcode",
              search: params.term,
              top: 25,
              skip: 0,
              count: true,
              filter: "OrganisationTypeID eq 'GPB'",
              orderby: "search.score() desc",
            });
          },
          processResults(data) {
            const result = [];
            const rows = Array.isArray(data && data.value) ? data.value : [];

            rows.forEach((value) => {
              try {
                let address = value.Address1 ? " - " + value.Address1 + ", " : "";
                address += address && value.Postcode ? value.Postcode : value.Postcode ? " - " + value.Postcode : "";
                const orgNameOnly = value.OrganisationName;
                const orgName = value.OrganisationName + address;

                // Contacts can be a JSON string, an array, null, or missing;
                // one unparsable row must not blank the whole results list.
                let contactDetails = [];
                if (Array.isArray(value.Contacts)) {
                  contactDetails = value.Contacts;
                } else if (typeof value.Contacts === "string" && value.Contacts.trim()) {
                  try {
                    contactDetails = JSON.parse(value.Contacts) || [];
                  } catch (parseError) {
                    contactDetails = [];
                  }
                }
                let telephone = null;
                let email = null;

                contactDetails.forEach((contact) => {
                  if (contact.OrganisationContactMethodType === "Telephone") {
                    telephone = contact.OrganisationContactValue;
                  }
                  if (contact.OrganisationContactMethodType === "Email") {
                    email = contact.OrganisationContactValue;
                  }
                });

                const fullAddress = [
                  value.Address1,
                  value.Address2,
                  value.Address3,
                  value.City,
                  value.County,
                  value.Postcode,
                ]
                  .filter((el) => el != null && el !== "")
                  .join(", ");

                result.push({
                  id: orgName,
                  text: orgName,
                  address: fullAddress,
                  contact_number: telephone,
                  email,
                  org_name: orgNameOnly,
                });
              } catch (rowError) {
                // Skip a malformed surgery row rather than losing every result.
              }
            });

            return { results: result };
          },
        },
      });

      $searchSelect.on("select2:select", (event) => {
        const selectedSurgery = event.params.data;
        $emailField.val(selectedSurgery.email || "");
        $manualName.val(selectedSurgery.org_name || "");
        $addressSubmit.val(selectedSurgery.address || selectedSurgery.id || selectedSurgery.text || "");
      });

      $searchSelect.on("select2:clear change", syncGpAddressSubmit);
    }

    $manualAddress.on("input change", syncGpAddressSubmit);
    $radios.on("change", () => syncGpPanels(true));
    $checkoutForm.on("checkout_place_order checkout_place_order_pm submit", prepareGpFormForSubmit);
    $(document.body).on("updated_checkout", removeSidebarGpDuplicates);
    syncGpPanels(false);
    requestAnimationFrame(() => syncGpPanels(false));
  };

  const initCheckoutValidationGuard = () => {
    if (
      !document.body.classList.contains("trimvia-checkout-page") ||
      document.body.classList.contains("trimvia-order-pay-page") ||
      !window.jQuery
    ) {
      return;
    }

    const $ = window.jQuery;
    const $form = $("form.checkout");

    if (!$form.length || $form.data("trimviaCheckoutValidation") === 1) {
      return;
    }

    $form.data("trimviaCheckoutValidation", 1);

    const mollieErrorPattern = /not all required components are mounted|mollie\.com\/guides\/mollie-components/i;
    const genericInvalidPattern = /^(error:\s*)?one or more fields are invalid\.?$/i;

    const getNoticeTarget = () => {
      let $target = $(".trimvia-checkout-form-notices").first();

      if (!$target.length) {
        $target = $(
          '<div class="trimvia-checkout-form-notices woocommerce-notices-wrapper" aria-live="polite"></div>'
        );
        const $before = $(".trimvia-checkout-before-form").first();
        if ($before.length) {
          $before.prepend($target);
        } else {
          $(".checkout-form").first().prepend($target);
        }
      }

      return $target;
    };

    const collectMollieFieldErrors = () => {
      const messages = [];
      const seen = {};

      const push = (raw) => {
        const message = String(raw || "")
          .replace(/\s+/g, " ")
          .trim();
        if (!message || genericInvalidPattern.test(message) || seen[message]) {
          return;
        }
        seen[message] = true;
        messages.push(message);
      };

      $(
        [
          "#payment .mollie-component-error",
          "#payment [class*='mollie'][class*='error']",
          "#payment .mollie-components .error",
          "#payment .wc-block-components-validation-error",
          ".payment_box .mollie-component-error",
          ".payment_box [data-testid='component-error']",
          ".payment_method_mollie_wc_gateway_creditcard .error",
        ].join(", ")
      ).each(function readMollieError() {
        push($(this).text());
      });

      // Fallback: visible red helper text under Mollie card fields
      $("#payment .payment_box, #payment .mollie-components, #payment")
        .find("p, span, div, small, label")
        .filter(":visible")
        .each(function readVisibleHint() {
          const text = String($(this).text() || "").trim();
          if (
            /verification code|cvc|cvv|expiry|card number|card holder|invalid|should be \d+ digits/i.test(
              text
            ) &&
            text.length < 120
          ) {
            push(text);
          }
        });

      return messages;
    };

    const scrollToPaymentFields = () => {
      const $payment =
        $("#payment").first().length
          ? $("#payment").first()
          : $(".payment_box, .mollie-components, .trimvia-checkout-summary").first();

      if (!$payment.length) {
        return;
      }

      const top = Math.max(0, ($payment.offset()?.top || 0) - 120);
      $("html, body").animate({ scrollTop: top }, 300);
    };

    const moveCheckoutNoticesToForm = () => {
      const $target = getNoticeTarget();

      $(
        [
          // Early WC print (before hero / under fixed header)
          ".woocommerce > .woocommerce-notices-wrapper",
          ".woocommerce > .woocommerce-error",
          ".woocommerce > .woocommerce-info",
          ".woocommerce > .woocommerce-message",
          ".woocommerce > .woocommerce-NoticeGroup",
          ".entry-content > .woocommerce > .woocommerce-error",
          ".entry-content > .woocommerce > .woocommerce-info",
          ".entry-content > .woocommerce > .woocommerce-message",
          ".trimvia-checkout-hero ~ .woocommerce-error",
          ".trimvia-checkout-hero ~ .woocommerce-notices-wrapper",
          // In-form / before-form notices
          ".trimvia-checkout-before-form .woocommerce-NoticeGroup",
          ".trimvia-checkout-before-form > .woocommerce-notices-wrapper:not(.trimvia-checkout-form-notices)",
          ".trimvia-checkout-before-form > .woocommerce-error",
          ".trimvia-checkout-before-form > .woocommerce-info",
          ".trimvia-checkout-before-form > .woocommerce-message",
          "form.checkout > .woocommerce-NoticeGroup",
          "form.checkout > .woocommerce-error",
          "form.checkout > .woocommerce-info",
          "form.checkout > .woocommerce-message",
          ".checkout-form > .woocommerce-NoticeGroup",
          ".checkout-form > .woocommerce-error",
        ].join(", ")
      ).each(function moveNotice() {
        const $group = $(this);

        if ($group.is($target) || $group.find($target).length || $group.closest($target).length) {
          return;
        }

        if ($group.children().length) {
          $target.append($group.contents());
        } else if ($group.is(".woocommerce-error, .woocommerce-info, .woocommerce-message")) {
          $target.append($group);
          return;
        }

        $group.remove();
      });

      // Replace opaque gateway/browser copy with actionable field messages when we can.
      $target.find(".woocommerce-error li").each(function replaceGenericError() {
        const $li = $(this);
        const text = String($li.text() || "").trim();
        if (genericInvalidPattern.test(text)) {
          $li.remove();
        }
      });

      // Drop empty error lists left behind after stripping generic Mollie copy.
      $target.find(".woocommerce-error").each(function removeEmptyErrorList() {
        if (!$(this).children("li").length && !String($(this).text() || "").trim()) {
          $(this).remove();
        }
      });

      revealCheckoutLoginOnAuthError($target);
    };

    const revealCheckoutLoginOnAuthError = ($target) => {
      const noticeText = String(($target && $target.length ? $target : getNoticeTarget()).text() || "")
        .replace(/\s+/g, " ")
        .trim()
        .toLowerCase();

      if (
        !noticeText ||
        !/(unknown email|incorrect (username|password|email)|invalid username|password you entered|please log in|already registered|username or email)/i.test(
          noticeText
        )
      ) {
        return;
      }

      const $group = $(".trimvia-checkout-login-group").first();
      const $loginForm = $group.find("form.woocommerce-form-login, form.login").first();

      if ($loginForm.length && !$loginForm.is(":visible")) {
        $loginForm.stop(true, true).slideDown(200);
        $group.addClass("is-open");
      }

      const top = Math.max(0, ($target.offset()?.top || $group.offset()?.top || 0) - 110);
      $("html, body").animate({ scrollTop: top }, 280);
    };

    const escapeHtml = (value) =>
      String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");

    const getFieldLabel = ($row, $input) => {
      const cleanLabel = (raw) =>
        String(raw || "")
          .replace(/\*/g, " ")
          .replace(/\brequired\b/gi, " ")
          .replace(/\s+/g, " ")
          .trim();

      const fieldNameMap = {
        billing_first_name: "First name",
        billing_last_name: "Last name",
        billing_company: "Company name",
        billing_country: "Country / Region",
        billing_address_1: "Street address",
        billing_address_2: "Apartment, suite, unit, etc.",
        billing_city: "Town / City",
        billing_state: "County",
        billing_postcode: "Postcode",
        billing_phone: "Phone",
        billing_email: "Email address",
        shipping_first_name: "Shipping first name",
        shipping_last_name: "Shipping last name",
        shipping_company: "Shipping company name",
        shipping_country: "Shipping country / region",
        shipping_address_1: "Shipping street address",
        shipping_address_2: "Shipping apartment, suite, unit, etc.",
        shipping_city: "Shipping town / city",
        shipping_state: "Shipping county",
        shipping_postcode: "Shipping postcode",
        account_password: "Account password",
        order_comments: "Order notes",
      };

      let label = "";

      if ($input && $input.length) {
        const id = $input.attr("id");
        if (id) {
          label = cleanLabel($(`label[for="${id}"]`).first().text());
        }

        const name = String($input.attr("name") || "").replace(/\[\]$/, "");
        if (!label && fieldNameMap[name]) {
          label = fieldNameMap[name];
        }

        if (!label && name) {
          label = cleanLabel(
            name
              .replace(/^(billing|shipping)_/, "")
              .replace(/[\[\]]/g, " ")
              .replace(/_/g, " ")
          );
          if (label) {
            label = label.charAt(0).toUpperCase() + label.slice(1);
          }
        }

        if (!label) {
          label = cleanLabel($input.attr("aria-label") || $input.attr("placeholder"));
        }
      }

      if (!label && $row && $row.length) {
        label = cleanLabel($row.find("label").first().text());
      }

      if (!label && $row && $row.length) {
        const rowId = String($row.attr("id") || "").replace(/_field$/, "");
        if (fieldNameMap[rowId]) {
          label = fieldNameMap[rowId];
        }
      }

      return label || "This field";
    };

    const showValidationNotice = (messages) => {
      const $target = getNoticeTarget();
      const list = (Array.isArray(messages) ? messages : [messages]).filter(Boolean);
      const items = list.map((message) => `<li>${escapeHtml(message)}</li>`).join("");

      $target.html(
        `<ul class="woocommerce-error trimvia-checkout-validation-error" role="alert">${items}</ul>`
      );
      moveCheckoutNoticesToForm();

      const top = Math.max(0, ($target.offset()?.top || 0) - 100);
      $("html, body").animate({ scrollTop: top }, 300);
    };

    const clearValidationNotice = () => {
      $(".trimvia-checkout-validation-error").remove();
    };

    const replaceGenericPaymentErrors = () => {
      let replacedMollie = false;
      let hadGenericInvalid = false;
      let hadGenericRequired = false;
      const genericRequiredPattern = /^(error:\s*)?this field is required\.?$/i;

      $(".woocommerce-error li, .woocommerce-error").each(function stripMollieError() {
        const $node = $(this);
        const text = String($node.text() || "").trim();

        if ($node.is("ul") && $node.children("li").length) {
          return;
        }

        if (mollieErrorPattern.test(text)) {
          $node.closest(".woocommerce-error, .woocommerce-NoticeGroup").remove();
          replacedMollie = true;
          return;
        }

        if (genericRequiredPattern.test(text)) {
          hadGenericRequired = true;
          if ($node.is("li")) {
            $node.remove();
          } else {
            $node.closest(".woocommerce-error, .woocommerce-NoticeGroup").remove();
          }
          return;
        }

        if (genericInvalidPattern.test(text)) {
          hadGenericInvalid = true;
          if ($node.is("li")) {
            $node.remove();
          } else {
            $node.closest(".woocommerce-error, .woocommerce-NoticeGroup").remove();
          }
        }
      });

      moveCheckoutNoticesToForm();

      if (!(replacedMollie || hadGenericInvalid || hadGenericRequired)) {
        return false;
      }

      if (hadGenericRequired) {
        const formOk = validateCheckoutForm();
        if (!formOk) {
          return true;
        }
      }

      const mollieMessages = collectMollieFieldErrors();
      if (mollieMessages.length) {
        showValidationNotice(mollieMessages);
        scrollToPaymentFields();
        return true;
      }

      // Billing/shipping may also be invalid — surface those if present.
      const formOk = validateCheckoutForm();
      if (!formOk) {
        return true;
      }

      showValidationNotice([
        "Please check your card details below. Fix any highlighted payment fields (for American Express the CVC must be 4 digits), then try again.",
      ]);
      scrollToPaymentFields();
      return true;
    };

    const validateCheckoutForm = () => {
      let valid = true;
      let $firstInvalid = null;
      const errorMessages = [];
      const seenMessages = {};

      const pushError = (message) => {
        if (!message || seenMessages[message]) {
          return;
        }
        seenMessages[message] = true;
        errorMessages.push(message);
      };

      $form
        .find(".woocommerce-invalid-required-field")
        .add($(".trimvia-checkout-gp-section .woocommerce-invalid-required-field"))
        .removeClass("woocommerce-invalid woocommerce-invalid-required-field");
      clearValidationNotice();

      const $requiredRows = $form
        .find(".validate-required:visible")
        .add(".trimvia-checkout-gp-section .validate-required:visible");

      $requiredRows.each(function validateRow() {
        const $row = $(this);
        const $inputs = $row.find(".input-text, select, textarea").filter(":visible");

        if ($row.find("input:checkbox").filter(":visible").length) {
          if (!$row.find("input:checkbox:checked").length) {
            valid = false;
            $row.addClass("woocommerce-invalid woocommerce-invalid-required-field");
            pushError(`${getFieldLabel($row)} is a required field.`);
            if (!$firstInvalid) {
              $firstInvalid = $row;
            }
          }
          return;
        }

        if (!$inputs.length) {
          return;
        }

        $inputs.each(function validateInput() {
          const $input = $(this);

          if ($input.is(":hidden, [type=submit], [type=button], [type=file]")) {
            return;
          }

          const value = String($input.val() || "").trim();
          if (!value) {
            valid = false;
            $row.addClass("woocommerce-invalid woocommerce-invalid-required-field");
            pushError(`${getFieldLabel($row, $input)} is a required field.`);
            if (!$firstInvalid) {
              $firstInvalid = $row;
            }
            return;
          }

          if ($input.is('[type="email"]') || $input.attr("name") === "billing_email") {
            const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            if (!emailOk) {
              valid = false;
              $row.addClass("woocommerce-invalid woocommerce-invalid-required-field");
              pushError(`${getFieldLabel($row, $input)} must be a valid email address.`);
              if (!$firstInvalid) {
                $firstInvalid = $row;
              }
            }
          }
        });
      });

      // Required file uploads (e.g. ID) are skipped above — check them separately.
      $form.find('input[type="file"]').filter(":visible").each(function validateFile() {
        const $input = $(this);
        const $row = $input.closest(".form-row, .validate-required, .form-group");
        const isRequired =
          $input.prop("required") ||
          $row.hasClass("validate-required") ||
          $row.find(".required").length > 0;

        if (!isRequired) {
          return;
        }

        if (!$input[0].files || !$input[0].files.length) {
          valid = false;
          $row.addClass("woocommerce-invalid woocommerce-invalid-required-field");
          pushError(`${getFieldLabel($row, $input)} is a required field.`);
          if (!$firstInvalid) {
            $firstInvalid = $row;
          }
        }
      });

      if (!valid) {
        if (!errorMessages.length) {
          errorMessages.push("Please complete the required fields below before placing your order.");
        }

        showValidationNotice(errorMessages);

        if ($firstInvalid && $firstInvalid.length) {
          const scrollTop = Math.max(0, ($firstInvalid.offset()?.top || 0) - 120);
          $("html, body").animate({ scrollTop }, 300);
        }
      }

      return valid;
    };

    const blockInvalidCheckout = (event) => {
      if (!validateCheckoutForm()) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        $form.removeClass("processing");

        if (typeof $form.unblock === "function") {
          $form.unblock();
        }

        return false;
      }

      return true;
    };

    const formEl = $form.get(0);

    if (formEl) {
      formEl.addEventListener("submit", blockInvalidCheckout, true);
    }

    document.addEventListener(
      "click",
      (event) => {
        const placeOrder = event.target.closest("#place_order");

        if (!placeOrder || placeOrder.disabled || !placeOrder.closest("form.checkout")) {
          return;
        }

        blockInvalidCheckout(event);
      },
      true
    );

    $form.on("checkout_place_order", function onCheckoutPlaceOrder() {
      return validateCheckoutForm();
    });

    $(document.body).on("checkout_error", () => {
      // Mollie often injects the notice slightly after checkout_error fires.
      window.setTimeout(() => {
        replaceGenericPaymentErrors();
      }, 0);
      window.setTimeout(() => {
        replaceGenericPaymentErrors();
      }, 150);
    });

    moveCheckoutNoticesToForm();
    $(document.body).on("updated_checkout", moveCheckoutNoticesToForm);

    $(document.body).on(
      "input change",
      "form.checkout .input-text, form.checkout select, form.checkout textarea, .trimvia-checkout-gp-section input, .trimvia-checkout-gp-section select, .trimvia-checkout-gp-section textarea",
      function clearFieldError() {
        const $row = $(this).closest(".form-row, .trimvia-gp-panel, .trimvia-gp-consent");

        if (!$row.hasClass("woocommerce-invalid-required-field")) {
          return;
        }

        if ($(this).is(":checkbox")) {
          if ($(this).is(":checked")) {
            $row.removeClass("woocommerce-invalid woocommerce-invalid-required-field");
          }
          return;
        }

        if (String($(this).val() || "").trim()) {
          $row.removeClass("woocommerce-invalid woocommerce-invalid-required-field");
        }
      }
    );
  };

  const enhanceCheckoutAccountPassword = () => {
    const input = document.getElementById("account_password");
    if (!input || input.dataset.trimviaPasswordToggle === "1") {
      return;
    }

    let wrapper = input.closest(".woocommerce-input-wrapper.password-input, .password-input");

    if (!wrapper) {
      wrapper = document.createElement("span");
      wrapper.className = "woocommerce-input-wrapper password-input";
      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(input);
    } else if (!wrapper.classList.contains("woocommerce-input-wrapper")) {
      wrapper.classList.add("woocommerce-input-wrapper");
    }

    if (wrapper.querySelector(".show-password-input")) {
      input.dataset.trimviaPasswordToggle = "1";
      return;
    }

    const button = document.createElement("button");
    button.type = "button";
    button.className = "show-password-input";
    button.setAttribute("aria-label", "Show password");
    button.setAttribute("aria-pressed", "false");

    button.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      const isHidden = input.type === "password";
      input.type = isHidden ? "text" : "password";
      button.classList.toggle("display-password", isHidden);
      button.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
      button.setAttribute("aria-pressed", String(isHidden));
    });
    button.addEventListener("mousedown", (event) => {
      // Keep focus on the password field without triggering label/tooltip side-effects.
      event.preventDefault();
    });

    wrapper.appendChild(button);
    input.dataset.trimviaPasswordToggle = "1";
  };

  let checkoutAccountPasswordBound = false;

  const initPasswordRequirementsTooltip = () => {
    const isTouchDevice = window.matchMedia("(hover: none), (pointer: coarse)").matches;
    const passwordFields = [
      { inputId: "reg_password", required: true },
      { inputId: "password_1", required: false },
      { inputId: "account_password", required: true },
    ];
    const reqList = [
      { key: "length", text: "At least 8 characters long" },
      { key: "uppercase", text: "At least one uppercase letter" },
      { key: "lowercase", text: "At least one lowercase letter" },
      { key: "number", text: "At least one number" },
      { key: "special", text: "At least one special character" },
    ];

    const checkReqs = (value) => ({
      length: value.length >= 8,
      uppercase: /[A-Z]/.test(value),
      lowercase: /[a-z]/.test(value),
      number: /[0-9]/.test(value),
      special: /[^A-Za-z0-9]/.test(value),
    });

    const fitTooltipInViewport = (tooltip, forceOpen = false) => {
      if (!tooltip) {
        return;
      }

      const shouldStayOpen =
        forceOpen ||
        tooltip.classList.contains("trimvia-pwd-tooltip-open") ||
        tooltip.style.display === "block";
      const isMobile = window.innerWidth <= 767;
      const pad = 16;

      tooltip.style.visibility = "hidden";
      tooltip.style.display = "block";
      tooltip.style.transform = "";
      tooltip.style.left = "";
      tooltip.style.right = "";

      if (isMobile) {
        tooltip.style.left = "50%";
        tooltip.style.right = "auto";
        tooltip.style.transform = "translateX(-50%)";
      } else {
        tooltip.style.left = "0";
        tooltip.style.right = "auto";
        tooltip.style.transform = "none";
      }

      const rect = tooltip.getBoundingClientRect();
      let shiftX = 0;

      if (rect.left < pad) {
        shiftX = pad - rect.left;
      } else if (rect.right > window.innerWidth - pad) {
        shiftX = -(rect.right - (window.innerWidth - pad));
      }

      if (isMobile) {
        tooltip.style.transform = `translateX(calc(-50% + ${shiftX}px))`;
        const arrowOffset = 50 - (shiftX / Math.max(rect.width, 1)) * 100;
        tooltip.style.setProperty(
          "--pwd-tooltip-arrow-left",
          `${Math.min(88, Math.max(12, arrowOffset))}%`
        );
      } else {
        tooltip.style.left = `${shiftX}px`;
        tooltip.style.transform = "none";
      }

      tooltip.style.visibility = "";
      if (!shouldStayOpen) {
        tooltip.style.display = "none";
      }
    };

    const openTooltip = (tooltip) => {
      fitTooltipInViewport(tooltip, true);
      tooltip.classList.add("trimvia-pwd-tooltip-open");
    };

    const closeTooltip = (tooltip) => {
      if (!tooltip) {
        return;
      }
      tooltip.style.display = "none";
      tooltip.classList.remove("trimvia-pwd-tooltip-open");
    };

    const updateTooltip = (value, tooltip) => {
      if (!tooltip) {
        return true;
      }

      const checks = checkReqs(value || "");
      let allPass = true;

      tooltip.querySelectorAll(".pm-pwd-req").forEach((row) => {
        const req = row.getAttribute("data-req");
        const text = row.getAttribute("data-text") || "";
        const pass = Boolean(checks[req]);
        if (!pass) {
          allPass = false;
        }
        row.style.color = pass ? "#51cf66" : "#ff6b6b";
        row.innerHTML = `${pass ? "&#10003; " : "&#10007; "}${text}`;
      });

      return allPass;
    };

    const buildTooltipWrapper = () => {
      const items = reqList
        .map(
          (req) =>
            `<span class="pm-pwd-req" data-req="${req.key}" data-text="${req.text}">&#10007; ${req.text}</span>`
        )
        .join("");

      const wrapper = document.createElement("span");
      wrapper.className = "pm-pwd-tooltip-wrapper";
      wrapper.innerHTML =
        '<span class="pm-pwd-tooltip-icon" role="button" tabindex="0" aria-label="Password requirements">' +
        '<i class="fas fa-info-circle" aria-hidden="true"></i></span>' +
        '<div class="pm-pwd-tooltip" role="tooltip">' +
        '<strong style="display: block; margin-bottom: 8px;">Password Requirements:</strong>' +
        items +
        "</div>";
      return wrapper;
    };

    const ensureTooltipsExist = () => {
      passwordFields.forEach((field) => {
        const input = document.getElementById(field.inputId);
        const label = document.querySelector(`label[for="${field.inputId}"]`);
        if (!input || !label || label.querySelector(".pm-pwd-tooltip-icon")) {
          return;
        }
        label.appendChild(buildTooltipWrapper());
      });
    };

    const bindTooltips = () => {
      ensureTooltipsExist();

      document.querySelectorAll(".pm-pwd-tooltip-wrapper").forEach((wrapper) => {
        if (wrapper.dataset.trimviaPwdBound === "1") {
          return;
        }

        const icon = wrapper.querySelector(".pm-pwd-tooltip-icon");
        const tooltip = wrapper.querySelector(".pm-pwd-tooltip");
        if (!icon || !tooltip) {
          return;
        }

        wrapper.dataset.trimviaPwdBound = "1";

        // Prevent label[for] from focusing the password input when using the info icon.
        icon.addEventListener("mousedown", (event) => {
          event.preventDefault();
        });

        const toggleFromIcon = (event) => {
          event.preventDefault();
          event.stopPropagation();
          const isOpen =
            tooltip.classList.contains("trimvia-pwd-tooltip-open") ||
            tooltip.style.display === "block";
          if (isOpen) {
            closeTooltip(tooltip);
            return;
          }
          openTooltip(tooltip);
        };

        if (isTouchDevice) {
          icon.addEventListener("click", toggleFromIcon);
        } else {
          icon.addEventListener("mouseenter", () => openTooltip(tooltip));
          icon.addEventListener("mouseleave", () => closeTooltip(tooltip));
          icon.addEventListener("focus", () => openTooltip(tooltip));
          icon.addEventListener("blur", () => closeTooltip(tooltip));
          // Desktop uses hover/keyboard focus only — click would fight mouseenter.
          icon.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
          });
        }

        const label = wrapper.closest("label");
        const inputId = label?.getAttribute("for");
        const input = inputId ? document.getElementById(inputId) : null;
        if (input && input.dataset.trimviaPwdInputBound !== "1") {
          input.dataset.trimviaPwdInputBound = "1";
          // Update requirement colours while typing — never auto-open the tooltip.
          input.addEventListener("input", () => {
            updateTooltip(input.value, tooltip);
          });
        }
      });
    };

    bindTooltips();
    window.setTimeout(bindTooltips, 300);

    if (!document.body.dataset.trimviaPwdTooltipDismissBound) {
      document.body.dataset.trimviaPwdTooltipDismissBound = "1";
      document.addEventListener("click", (event) => {
        if (event.target.closest(".pm-pwd-tooltip-wrapper")) {
          return;
        }
        // Eye / password field clicks must dismiss, not open, the tooltip.
        document.querySelectorAll(".pm-pwd-tooltip.trimvia-pwd-tooltip-open").forEach(closeTooltip);
      });
    }

    if (!document.body.dataset.trimviaPwdSubmitBound) {
      document.body.dataset.trimviaPwdSubmitBound = "1";
      document.addEventListener(
        "submit",
        (event) => {
          const form = event.target;
          if (
            !(form instanceof HTMLFormElement) ||
            (!form.classList.contains("woocommerce-form-register") &&
              !form.classList.contains("woocommerce-checkout") &&
              !form.classList.contains("woocommerce-EditAccountForm"))
          ) {
            return;
          }

          let isValid = true;
          let firstInvalid = null;

          passwordFields.forEach((field) => {
            const input = form.querySelector(`#${field.inputId}`);
            if (!input) {
              return;
            }

            const value = String(input.value || "");
            if (!value && !field.required) {
              return;
            }

            const tooltip = document.querySelector(
              `label[for="${field.inputId}"] .pm-pwd-tooltip`
            );
            const allPass = updateTooltip(value, tooltip);
            if (!allPass) {
              isValid = false;
              if (tooltip) {
                openTooltip(tooltip);
              }
              if (!firstInvalid) {
                firstInvalid = input;
              }
            }
          });

          if (!isValid) {
            event.preventDefault();
            event.stopPropagation();
            firstInvalid?.focus();
          }
        },
        true
      );
    }
  };

  const initCheckoutTogglePanels = () => {
    if (!document.body.classList.contains("trimvia-checkout-page")) {
      return;
    }

    const jq = window.jQuery;
    if (!jq) {
      return;
    }

    const syncGroupState = ($group, formSelector) => {
      if (!$group || !$group.length) {
        return;
      }

      $group.toggleClass("is-open", $group.find(formSelector).is(":visible"));
    };

    jq(".trimvia-checkout-login-group").each(function initLoginGroupState() {
      syncGroupState(jq(this), "form.login");
    });

    jq(".trimvia-checkout-coupon-group").each(function initCouponGroupState() {
      syncGroupState(jq(this), "form.checkout_coupon");
    });

    jq(document.body).on("click", ".showlogin", function handleCheckoutLoginToggle() {
      const $group = jq(this).closest(".trimvia-checkout-login-group");
      if (!$group.length) {
        return;
      }

      window.setTimeout(() => {
        syncGroupState($group, "form.login");
      }, 450);
    });

    jq(document.body).on("click", ".showcoupon", function handleCheckoutCouponToggle() {
      const $group = jq(this).closest(".trimvia-checkout-coupon-group");
      if (!$group.length) {
        return;
      }

      window.setTimeout(() => {
        syncGroupState($group, "form.checkout_coupon");
      }, 450);
    });
  };

  const initCheckoutAccountPasswordVisibility = () => {
    if (!document.body.classList.contains("trimvia-checkout-page")) {
      return;
    }

    enhanceCheckoutAccountPassword();

    if (checkoutAccountPasswordBound) {
      return;
    }
    checkoutAccountPasswordBound = true;

    const createAccount = document.getElementById("createaccount");
    if (createAccount) {
      createAccount.addEventListener("change", () => {
        window.setTimeout(enhanceCheckoutAccountPassword, 50);
      });
    }

    const checkout = document.querySelector(".woocommerce-checkout");
    if (checkout && checkout.dataset.trimviaAccountPasswordObserved !== "1") {
      checkout.dataset.trimviaAccountPasswordObserved = "1";
      new MutationObserver(() => {
        window.requestAnimationFrame(enhanceCheckoutAccountPassword);
      }).observe(checkout, { childList: true, subtree: true });
    }

    const jq = window.jQuery;
    if (jq) {
      jq(document.body).on("updated_checkout", () => {
        const input = document.getElementById("account_password");
        if (input) {
          input.dataset.trimviaPasswordToggle = "";
        }
        window.setTimeout(enhanceCheckoutAccountPassword, 50);
      });
    }
  };

  const enhanceTrimviaCustomSelect = (select, controlWrap) => {
    if (!select || !controlWrap || select.multiple || select.options.length === 0) {
      return;
    }

    if (select.dataset.trimviaSelectEnhanced === "1") {
      if (typeof select._trimviaSelectRefresh === "function") {
        select._trimviaSelectRefresh();
      }
      return;
    }

    select.dataset.trimviaSelectEnhanced = "1";
    controlWrap.classList.add("trimvia-custom-select-wrap");

    const customSelect = document.createElement("div");
    customSelect.className = "trimvia-custom-select";

    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "trimvia-custom-select__trigger";
    trigger.setAttribute("aria-haspopup", "listbox");
    trigger.setAttribute("aria-expanded", "false");

    const valueEl = document.createElement("span");
    valueEl.className = "trimvia-custom-select__value";

    const menu = document.createElement("ul");
    menu.className = "trimvia-custom-select__menu";
    menu.setAttribute("role", "listbox");
    menu.hidden = true;

    select.classList.add("trimvia-custom-select__native");
    select.tabIndex = -1;

    const optionItems = [];

    trigger.appendChild(valueEl);
    customSelect.appendChild(trigger);
    customSelect.appendChild(menu);
    customSelect.appendChild(select);
    controlWrap.appendChild(customSelect);

    const getSelectedOption = () => select.options[select.selectedIndex] || null;

    const syncSelection = () => {
      const selectedOption = getSelectedOption();
      valueEl.textContent = selectedOption ? selectedOption.textContent.trim() : "";
      customSelect.classList.toggle("is-placeholder", !select.value);

      optionItems.forEach((item) => {
        const option = Array.from(select.options).find(
          (entry) => entry.value === item.dataset.value
        );
        const isDisabled = option ? option.disabled : false;
        item.classList.toggle("is-disabled", isDisabled);
        item.setAttribute("aria-disabled", isDisabled ? "true" : "false");

        const isSelected = item.dataset.value === select.value;
        item.classList.toggle("is-selected", isSelected);
        item.setAttribute("aria-selected", isSelected ? "true" : "false");
      });
    };

    const rebuildOptions = () => {
      menu.innerHTML = "";
      optionItems.length = 0;

      Array.from(select.options).forEach((option) => {
        const item = document.createElement("li");
        item.className = "trimvia-custom-select__option";
        item.setAttribute("role", "option");
        item.dataset.value = option.value;
        item.textContent = option.textContent.trim();
        menu.appendChild(item);
        optionItems.push(item);
      });

      syncSelection();
    };

    const closeMenu = () => {
      customSelect.classList.remove("is-open");
      trigger.setAttribute("aria-expanded", "false");
      menu.hidden = true;
    };

    const openMenu = () => {
      document.querySelectorAll(".trimvia-custom-select.is-open").forEach((openSelect) => {
        if (openSelect !== customSelect) {
          openSelect.classList.remove("is-open");
          const openTrigger = openSelect.querySelector(".trimvia-custom-select__trigger");
          const openMenuEl = openSelect.querySelector(".trimvia-custom-select__menu");
          if (openTrigger) {
            openTrigger.setAttribute("aria-expanded", "false");
          }
          if (openMenuEl) {
            openMenuEl.hidden = true;
          }
        }
      });

      customSelect.classList.add("is-open");
      trigger.setAttribute("aria-expanded", "true");
      menu.hidden = false;
    };

    const chooseOption = (item) => {
      if (!item || item.classList.contains("is-disabled")) {
        return;
      }

      select.value = item.dataset.value;
      select.dispatchEvent(new Event("change", { bubbles: true }));
      select.dispatchEvent(new Event("input", { bubbles: true }));
      syncSelection();
      closeMenu();
      trigger.focus();
    };

    const getEnabledOptionItems = () =>
      optionItems.filter((item) => !item.classList.contains("is-disabled"));

    trigger.addEventListener("click", (event) => {
      event.preventDefault();
      if (customSelect.classList.contains("is-open")) {
        closeMenu();
      } else {
        openMenu();
      }
    });

    menu.addEventListener("click", (event) => {
      const item = event.target.closest(".trimvia-custom-select__option");
      if (!item) {
        return;
      }
      event.preventDefault();
      chooseOption(item);
    });

    trigger.addEventListener("keydown", (event) => {
      const enabledItems = getEnabledOptionItems();
      if (!enabledItems.length) {
        return;
      }

      let currentIndex = enabledItems.findIndex(
        (item) => item.dataset.value === select.value
      );
      if (currentIndex < 0) {
        currentIndex = 0;
      }

      if (event.key === "ArrowDown") {
        event.preventDefault();
        if (!customSelect.classList.contains("is-open")) {
          openMenu();
          return;
        }
        currentIndex = currentIndex < enabledItems.length - 1 ? currentIndex + 1 : 0;
      } else if (event.key === "ArrowUp") {
        event.preventDefault();
        if (!customSelect.classList.contains("is-open")) {
          openMenu();
          return;
        }
        currentIndex = currentIndex > 0 ? currentIndex - 1 : enabledItems.length - 1;
      } else if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        if (!customSelect.classList.contains("is-open")) {
          openMenu();
          return;
        }
        chooseOption(enabledItems[currentIndex]);
        return;
      } else if (event.key === "Escape") {
        closeMenu();
        return;
      } else {
        return;
      }

      const nextItem = enabledItems[currentIndex];
      if (nextItem) {
        select.value = nextItem.dataset.value;
        syncSelection();
        nextItem.scrollIntoView({ block: "nearest" });
      }
    });

    document.addEventListener("click", (event) => {
      if (!customSelect.contains(event.target)) {
        closeMenu();
      }
    });

    select.addEventListener("change", syncSelection);

    const observer = new MutationObserver(() => {
      rebuildOptions();
    });
    observer.observe(select, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ["disabled", "selected", "hidden"],
    });

    select._trimviaSelectRefresh = rebuildOptions;
    rebuildOptions();
  };

  const initTrimviaCustomSelects = (root, selector, resolveWrap) => {
    root.querySelectorAll(selector).forEach((select) => {
      if (select.dataset.trimviaSelectEnhanced === "1") {
        return;
      }

      const controlWrap = resolveWrap(select);
      enhanceTrimviaCustomSelect(select, controlWrap);
    });
  };

  const initContactFormSelects = (root = document) => {
    initTrimviaCustomSelects(
      root,
      ".trimvia-contact-form-wrap select:not([data-trimvia-select-enhanced])",
      (select) => select.closest(".wpcf7-form-control-wrap") || select.parentElement
    );
  };

  const initProductVariationSelects = (root = document) => {
    initTrimviaCustomSelects(
      root,
      ".trimvia-single-product-page form.variations_form table.variations select:not([data-trimvia-select-enhanced])",
      (select) => select.closest(".variation-input-wrapper") || select.parentElement
    );
  };

  const refreshProductVariationSelects = (root = document) => {
    root.querySelectorAll(
      ".trimvia-single-product-page form.variations_form table.variations select[data-trimvia-select-enhanced='1']"
    ).forEach((select) => {
      if (typeof select._trimviaSelectRefresh === "function") {
        select._trimviaSelectRefresh();
      }
    });
  };

  const initCommon = () => {
    ensureLegacyAdminAjaxGlobal();
    movePractitionerModalsToBody();
    movePrescriberOnboardingPopupsToBody();
    moveSignaturePopupToBody();
    watchSignaturePopupMount();
    document.addEventListener(
      "click",
      (event) => {
        if (!event.target.closest(".prescriber-sign-gen-wrapper.make-popup .close-me")) {
          return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        closeSignaturePopup();
      },
      true
    );
    document.addEventListener("click", (event) => {
      if (event.target.closest(".signature-modal")) {
        window.setTimeout(moveSignaturePopupToBody, 120);
      }
      if (event.target.closest(".woocommerce-MyAccount-navigation a")) {
        closeSignaturePopup();
      }
    });

    const jq = window.jQuery;
    if (jq) {
      jq(document).ajaxComplete((_event, xhr, settings) => {
        const data = settings?.data;
        let action = "";

        if (typeof data === "string") {
          const match = data.match(/(?:^|&)action=([^&]+)/);
          action = match ? decodeURIComponent(match[1]) : "";
        } else if (data instanceof FormData) {
          action = data.get("action") || "";
        } else if (data && typeof data === "object") {
          action = data.action || "";
        }

        if (action !== "prescriber_signature") {
          return;
        }

        let saved = false;
        try {
          const response = xhr.responseJSON || JSON.parse(xhr.responseText || "{}");
          saved = response && (response.success === 1 || response.success === true);
        } catch (error) {
          saved = false;
        }

        if (saved) {
          closeSignaturePopup();
        }
      });
    }
    ensureLegacyJqueryModalSupport();
    ensureGetOrderPrescriptionDataFallback();
    initCommerceViewportLock();
    initHeaderScroll();
    initScrollToTop();
    initHomeStickyConsultCta();
    syncHeaderAuthButtons();
    initMobileMenu();
    syncHeaderAuthButtons();
    initServiceContentFaqs();
    initPasswordRequirementsTooltip();
    initRevealOnScroll();
    initFaqAccordion();
    initTestimonialCarousel();
    initSingleProductTabs();
    initSingleProductGallery();
    initCartNoticeAlignment();
    initCartCouponMobilePlaceholder();
    initCartQuantityUpdates();
    initCheckoutTogglePanels();
    initCheckoutStickySummary();
    initCheckoutDeliveryPanel();
    initCheckoutAccountPasswordVisibility();
    initCheckoutGpForm();
    initCheckoutValidationGuard();
    initPrescriberConsultationAccordion();
    initPrescriberApprovalPinModal();
    initPrescriberModalInteractionFix();
    initConsultationPatientModal();
    initContactFormSelects();
    initProductVariationSelects();
  };

  document.addEventListener("wpcf7init", (event) => {
    initContactFormSelects(event.target || document);
  });

  document.addEventListener("wpcf7submit", (event) => {
    window.setTimeout(() => {
      initContactFormSelects(event.target || document);
    }, 0);
  });

  const jq = window.jQuery;
  if (jq) {
    jq(document.body).on("wc_variation_form", (_event, $form) => {
      initProductVariationSelects($form && $form.length ? $form[0] : document);
    });

    jq(document.body).on(
      "woocommerce_update_variation_values reset_data check_variations",
      ".variations_form",
      function handleVariationSelectRefresh() {
        refreshProductVariationSelects(this);
      }
    );
  }

  const enhancePrescriberPinApprovalForm = (root) => {
    if (!root) {
      return;
    }

    root.querySelectorAll(".prescriber-verification-form").forEach((form) => {
      if (form.dataset.trimviaPinApprovalReady === "1") {
        return;
      }

      form.dataset.trimviaPinApprovalReady = "1";
      form.classList.add("trimvia-pin-approval-shell");

      const pinInput = form.querySelector('input[name="pin_number"]');
      if (pinInput) {
        pinInput.classList.add("pin-input", "trimvia-pin-input");
        pinInput.setAttribute("maxlength", "6");
        pinInput.setAttribute("inputmode", "numeric");
        pinInput.setAttribute("pattern", "[0-9]*");
        pinInput.setAttribute("autocomplete", "off");
        if (!pinInput.getAttribute("placeholder")) {
          pinInput.setAttribute("placeholder", "••••••");
        }
      }

      form.querySelectorAll(".verify-me").forEach((button) => {
        button.classList.add("theme-btn", "btn", "trimvia-pin-authorise");
      });

      form.querySelectorAll(".dismiss-modal").forEach((button) => {
        button.classList.add("theme-btn-s4", "trimvia-pin-cancel");
      });

      const actionRows = form.querySelectorAll(".form-input-wrapper");
      const actionRow = actionRows.length ? actionRows[actionRows.length - 1] : null;
      if (actionRow && !actionRow.classList.contains("trimvia-pin-actions")) {
        actionRow.classList.add("trimvia-pin-actions");
      }
    });
  };

  const initPrescriberApprovalPinModal = () => {
    const modalIds = [
      "practitioner-order-modal",
      "practitioner-order-prescription-modal",
    ];

    const scanModal = (modal) => {
      if (!modal) {
        return;
      }
      enhancePrescriberPinApprovalForm(modal);
    };

    modalIds.forEach((id) => {
      const modal = document.getElementById(id);
      if (!modal || modal.dataset.trimviaPinApprovalObserved === "1") {
        return;
      }

      modal.dataset.trimviaPinApprovalObserved = "1";
      scanModal(modal);

      if (typeof MutationObserver === "undefined") {
        return;
      }

      const observer = new MutationObserver(() => scanModal(modal));
      observer.observe(modal, { childList: true, subtree: true });
    });
  };

  const initPrescriberConsultationAccordion = () => {
    const modalIds = [
      "practitioner-order-modal",
      "practitioner-order-prescription-modal",
      "prescription-extra-content-modal",
    ];

    const normalizeConsultMeta = (root) => {
      if (!root) {
        return;
      }

      root.querySelectorAll(".cons-completedby").forEach((meta) => {
        if (meta.dataset.trimviaMetaReady === "1" || meta.classList.contains("trimvia-consult-meta")) {
          return;
        }

        const rawText = meta.textContent.replace(/\s+/g, " ").trim();
        if (!rawText) {
          return;
        }

        const completedMatch = rawText.match(/Completed By:\s*(.*?)(?:\s+On:|$)/i);
        const onMatch = rawText.match(/On:\s*(.+)$/i);
        if (!completedMatch && !onMatch) {
          return;
        }

        meta.dataset.trimviaMetaReady = "1";
        meta.classList.add("trimvia-consult-meta");
        meta.innerHTML = "";

        if (completedMatch && completedMatch[1]) {
          const row = document.createElement("p");
          row.className = "trimvia-consult-meta-row";
          row.innerHTML =
            '<span class="trimvia-consult-meta-label">Completed By:</span>' +
            `<span class="trimvia-consult-meta-value">${completedMatch[1].trim()}</span>`;
          meta.appendChild(row);
        }

        if (onMatch && onMatch[1]) {
          const row = document.createElement("p");
          row.className = "trimvia-consult-meta-row";
          row.innerHTML =
            '<span class="trimvia-consult-meta-label">On:</span>' +
            `<span class="trimvia-consult-meta-value">${onMatch[1].trim()}</span>`;
          meta.appendChild(row);
        }
      });
    };

    const convertRowsToFaq = (group) => {
      if (!group || group.dataset.trimviaFaqReady === "1") {
        return;
      }

      const rows = [...group.querySelectorAll(":scope > .patient-row")];
      if (!rows.length) {
        return;
      }

      group.dataset.trimviaFaqReady = "1";

      const header = group.querySelector(".patient-group-header");
      if (header) {
        header.classList.add("trimvia-consult-group-label");
      }

      const list = document.createElement("div");
      list.className = "trimvia-consult-faq-list";

      rows.forEach((row, index) => {
        const questionEl = row.querySelector(".q-field-label");
        if (!questionEl) {
          return;
        }

        const fq = document.createElement("div");
        fq.className = "trimvia-fq" + (index === 0 ? " active" : "");

        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "trimvia-fq-btn";

        const questionWrap = document.createElement("span");
        questionWrap.className = "trimvia-fq-q";
        questionWrap.innerHTML = questionEl.innerHTML;

        const chev = document.createElement("span");
        chev.className = "trimvia-fq-chev";
        chev.setAttribute("aria-hidden", "true");

        btn.appendChild(questionWrap);
        btn.appendChild(chev);

        const answerWrap = document.createElement("div");
        answerWrap.className = "trimvia-fq-a";
        const answerIn = document.createElement("div");
        answerIn.className = "trimvia-fq-a-in";

        row.querySelectorAll(".pm-answer-row, .user-sub-detail, .user-submission, .child-description").forEach((node) => {
          answerIn.appendChild(node.cloneNode(true));
        });

        if (!answerIn.textContent.trim()) {
          const fallback = row.querySelector(".q-label .user-sub-detail, .q-label .user-submission");
          if (fallback) {
            answerIn.appendChild(fallback.cloneNode(true));
          }
        }

        answerWrap.appendChild(answerIn);
        fq.appendChild(btn);
        fq.appendChild(answerWrap);
        list.appendChild(fq);
        row.remove();
      });

      group.appendChild(list);
    };

    const initConsultFaqItems = (root) => {
      if (!root) {
        return;
      }

      root.querySelectorAll(".trimvia-fq-btn").forEach((button) => {
        if (button.dataset.trimviaFaqBound === "1") {
          return;
        }
        button.dataset.trimviaFaqBound = "1";
        button.addEventListener("click", (event) => {
          event.preventDefault();
          event.stopPropagation();
          const item = button.closest(".trimvia-fq");
          if (!item) {
            return;
          }
          const wasActive = item.classList.contains("active");
          item.parentElement.querySelectorAll(".trimvia-fq").forEach((faq) => faq.classList.remove("active"));
          if (!wasActive) {
            item.classList.add("active");
          }
        });
      });
    };

    const findSectionLabel = (root, expectedTitle) => {
      if (!root) {
        return null;
      }

      let prev = root.previousElementSibling;
      while (prev) {
        if (prev.matches(".pm-consult-section-label, h2, h3, h4, h5")) {
          const text = prev.textContent.replace(/\s+/g, " ").trim();
          if (
            prev.classList.contains("pm-consult-section-label") ||
            !expectedTitle ||
            text.toLowerCase() === expectedTitle.toLowerCase()
          ) {
            return prev;
          }
          return null;
        }
        prev = prev.previousElementSibling;
      }

      return root.querySelector(":scope > .pm-consult-section-label, :scope > h2, :scope > h3, :scope > h4");
    };

    const createConsultFaqHead = (titleText) => {
      const head = document.createElement("button");
      head.type = "button";
      head.className = "trimvia-consult-faq-head";
      head.setAttribute("aria-expanded", "false");

      const inner = document.createElement("div");
      inner.className = "trimvia-consult-faq-head-inner";

      const titleSpan = document.createElement("span");
      titleSpan.className = "trimvia-consult-faq-title";
      titleSpan.textContent = titleText;

      const chev = document.createElement("span");
      chev.className = "trimvia-consult-faq-chev";
      chev.setAttribute("aria-hidden", "true");

      inner.appendChild(titleSpan);
      head.appendChild(inner);
      head.appendChild(chev);
      return head;
    };

    const extractAccordionMeta = (faqRoot) => {
      const button = faqRoot?.querySelector(".accordion-button");
      if (!button) {
        return null;
      }

      const meta = document.createElement("div");
      meta.className = "trimvia-consult-faq-meta";

      const pill = button.querySelector(".pm-acc-pill");
      const date = button.querySelector(".pm-acc-date");
      if (pill) {
        meta.appendChild(pill.cloneNode(true));
      }
      if (date) {
        meta.appendChild(date.cloneNode(true));
      }

      if (!meta.textContent.trim()) {
        button.querySelectorAll(".cons-title, .cons-completedby").forEach((node) => {
          meta.appendChild(node.cloneNode(true));
        });
      }

      return meta.textContent.trim() ? meta : null;
    };

    const enrichConsultFaqHead = (faqRoot) => {
      if (!faqRoot || faqRoot.dataset.trimviaHeadMeta === "1") {
        return;
      }

      const head = faqRoot.querySelector(".trimvia-consult-faq-head");
      const meta = extractAccordionMeta(faqRoot);
      if (!head || !meta) {
        return;
      }

      faqRoot.dataset.trimviaHeadMeta = "1";

      faqRoot.querySelector(".trimvia-consult-summary-row")?.remove();

      const inner = head.querySelector(".trimvia-consult-faq-head-inner") || head;
      inner.appendChild(meta);

      const header = faqRoot.querySelector(".accordion-header");
      if (header) {
        header.classList.add("trimvia-sr-accordion-header");
      }
    };

    const wireFaqShellToggle = (root) => {
      const head = root.querySelector(".trimvia-consult-faq-head");
      const accordionButton = root.querySelector(".accordion-button");
      const accordionCollapse = root.querySelector(".accordion-collapse");
      if (!head || head.dataset.trimviaFaqBound === "1") {
        return;
      }

      head.dataset.trimviaFaqBound = "1";

      const syncSectionState = () => {
        const isOpen = accordionCollapse
          ? accordionCollapse.classList.contains("show")
          : root.classList.contains("is-open");
        root.classList.toggle("is-open", isOpen);
        root.classList.toggle("is-collapsed", !isOpen);
        head.setAttribute("aria-expanded", isOpen ? "true" : "false");
      };

      syncSectionState();

      head.addEventListener("click", (event) => {
        event.preventDefault();
        if (accordionButton && accordionCollapse) {
          accordionButton.click();
          return;
        }

        const isOpen = !root.classList.contains("is-open");
        root.classList.toggle("is-open", isOpen);
        root.classList.toggle("is-collapsed", !isOpen);
        head.setAttribute("aria-expanded", isOpen ? "true" : "false");
      });

      if (accordionCollapse && typeof MutationObserver !== "undefined") {
        const observer = new MutationObserver(syncSectionState);
        observer.observe(accordionCollapse, { attributes: true, attributeFilter: ["class"] });
      }
    };

    const removeNearbySectionLabels = (root, expectedTitle) => {
      if (!root || !expectedTitle) {
        return;
      }

      const normalizedExpected = expectedTitle.replace(/\s+/g, " ").trim().toLowerCase();
      const matchesTitle = (node) =>
        node.textContent.replace(/\s+/g, " ").trim().toLowerCase() === normalizedExpected;

      let parent = root.parentElement;
      while (parent && !parent.classList.contains("pm-prescription-review")) {
        parent.querySelectorAll(":scope > .pm-consult-section-label, :scope > h2, :scope > h3, :scope > h4, :scope > h5").forEach((label) => {
          if (matchesTitle(label)) {
            label.remove();
          }
        });
        parent = parent.parentElement;
      }

      root.querySelectorAll(":scope > .pm-consult-section-label, :scope > h2, :scope > h3, :scope > h4, :scope > h5").forEach((label) => {
        if (matchesTitle(label) && !label.classList.contains("trimvia-consult-faq-head")) {
          label.remove();
        }
      });
    };

    const getUnwrappedPreviousItems = (root) =>
      [...root.querySelectorAll(".pm-consultation-accordion > .accordion-item")].filter(
        (item) => !item.closest(".trimvia-consult-faq--previous")
      );

    const getUnwrappedPreviousWraps = (root) =>
      [...root.querySelectorAll(":scope > .pm-consultation-wrap")].filter((wrap) => !wrap.closest(".trimvia-consult-faq"));

    const mountSummaryRow = (faqRoot) => {
      enrichConsultFaqHead(faqRoot);
    };

    const buildConsultFaqShell = (root, forcedTitle) => {
      if (!root || root.dataset.trimviaFaqShell === "1") {
        return;
      }

      const titleText =
        forcedTitle ||
        (root.id === "pmPreviousConsultations" ? "Previous Consultations" : "Current Order");
      const labelEl = findSectionLabel(root, titleText);

      root.dataset.trimviaFaqShell = "1";
      root.classList.add("trimvia-consult-faq", "is-collapsed");

      if (labelEl) {
        labelEl.remove();
      }

      const panel = document.createElement("div");
      panel.className = "trimvia-consult-faq-panel";
      while (root.firstChild) {
        panel.appendChild(root.firstChild);
      }

      root.appendChild(createConsultFaqHead(titleText));
      root.appendChild(panel);

      removeNearbySectionLabels(root, titleText);
      mountSummaryRow(root);
      wireFaqShellToggle(root);
    };

    const syncPreviousCardHead = (card) => {
      const button = card.querySelector(".trimvia-prev-card-head, .accordion-button");
      const collapse = card.querySelector(".trimvia-prev-card-panel, .accordion-collapse");
      if (!button || !collapse) {
        return;
      }

      const isOpen = collapse.classList.contains("show");
      button.classList.toggle("is-open", isOpen);
      button.classList.toggle("collapsed", !isOpen);
      card.classList.toggle("is-open", isOpen);
      button.setAttribute("aria-expanded", isOpen ? "true" : "false");
    };

    const closeOtherPreviousPanels = (card, collapse) => {
      const parent = card.closest(".trimvia-prev-cards, .pm-consultation-accordion");
      if (!parent) {
        return;
      }

      parent.querySelectorAll(".trimvia-prev-card-panel.show, .accordion-collapse.show").forEach((panel) => {
        if (panel === collapse) {
          return;
        }

        panel.classList.remove("show");
        const otherCard = panel.closest(".trimvia-prev-card, .accordion-item");
        if (otherCard) {
          syncPreviousCardHead(otherCard);
        }
      });
    };

    const wirePreviousCardToggle = (card) => {
      const button = card.querySelector(".trimvia-prev-card-head, .accordion-button");
      const collapse = card.querySelector(".trimvia-prev-card-panel, .accordion-collapse");
      if (!button || !collapse || button.dataset.trimviaCardToggleBound === "1") {
        return;
      }

      button.dataset.trimviaCardToggleBound = "1";

      button.addEventListener("click", (event) => {
        event.preventDefault();

        const isOpen = collapse.classList.contains("show");
        const jq = window.jQuery;

        if (jq && typeof jq.fn.collapse === "function") {
          if (!isOpen) {
            closeOtherPreviousPanels(card, collapse);
          }
          jq(collapse).collapse("toggle");
          window.setTimeout(() => syncPreviousCardHead(card), 0);
          return;
        }

        if (!isOpen) {
          closeOtherPreviousPanels(card, collapse);
        }

        collapse.classList.toggle("show", !isOpen);
        syncPreviousCardHead(card);
      });

      if (window.jQuery && typeof window.jQuery.fn.collapse === "function") {
        window.jQuery(collapse).collapse({ toggle: false });
      }
    };

    const enhancePreviousCard = (card) => {
      if (!card || card.dataset.trimviaCardReady === "1") {
        return;
      }

      card.dataset.trimviaCardReady = "1";

      const body = card.querySelector(".trimvia-prev-card-body, .accordion-body");
      if (body) {
        normalizeConsultMeta(body);
        wrapConsultGroups(body);
        initConsultFaqItems(body);
      }

      wirePreviousCardToggle(card);

      const collapse = card.querySelector(".trimvia-prev-card-panel, .accordion-collapse");
      if (collapse && typeof MutationObserver !== "undefined") {
        syncPreviousCardHead(card);
        const observer = new MutationObserver(() => syncPreviousCardHead(card));
        observer.observe(collapse, { attributes: true, attributeFilter: ["class"] });
      }
    };

    const enhancePreviousConsultationsSection = (root) => {
      if (!root) {
        return;
      }

      const pendingCards = [...root.querySelectorAll(".trimvia-prev-card:not([data-trimvia-card-ready])")];
      const pendingItems = getUnwrappedPreviousItems(root).filter(
        (item) => !item.classList.contains("trimvia-prev-card")
      );
      const pendingWraps = getUnwrappedPreviousWraps(root);

      if (
        root.dataset.trimviaSectionReady === "1" &&
        !pendingCards.length &&
        !pendingItems.length &&
        !pendingWraps.length
      ) {
        return;
      }

      root.dataset.trimviaSectionReady = "1";
      root.classList.add("trimvia-previous-consultations-list", "trimvia-previous-consultations");

      removeNearbySectionLabels(root, "Previous Consultations");

      root
        .querySelectorAll(
          ":scope > .pm-consult-section-label, :scope > .trimvia-previous-section-heading, :scope > h2, :scope > h3, :scope > h4"
        )
        .forEach((label) => {
          if (label.closest(".trimvia-prev-section-head")) {
            return;
          }

          const text = label.textContent.replace(/\s+/g, " ").trim().toLowerCase();
          if (text === "previous consultations" || label.classList.contains("trimvia-previous-section-heading")) {
            label.remove();
          }
        });

      let cardsWrap = root.querySelector(":scope > .trimvia-prev-cards");
      if (!cardsWrap) {
        cardsWrap = document.createElement("div");
        cardsWrap.className = "trimvia-prev-cards pm-consultation-accordion accordion";

        const looseItems = [
          ...root.querySelectorAll(":scope > .trimvia-prev-card, :scope > .accordion-item, :scope > .pm-consultation-wrap"),
        ].filter((node) => !node.closest(".trimvia-prev-cards"));

        looseItems.forEach((node) => cardsWrap.appendChild(node));

        if (looseItems.length) {
          root.appendChild(cardsWrap);
        }
      }

      if (!root.querySelector(".trimvia-prev-section-head")) {
        const cardCount = root.querySelectorAll(".trimvia-prev-card, .accordion-item, .pm-consultation-wrap").length;
        const sectionHead = document.createElement("div");
        sectionHead.className = "trimvia-prev-section-head";

        const title = document.createElement("h3");
        title.className = "trimvia-prev-section-title";
        title.textContent = "Previous Consultations";

        const count = document.createElement("span");
        count.className = "trimvia-prev-section-count";
        count.textContent = String(cardCount || pendingCards.length || pendingItems.length || pendingWraps.length);

        sectionHead.appendChild(title);
        sectionHead.appendChild(count);
        root.insertBefore(sectionHead, root.firstChild);
      } else {
        const countEl = root.querySelector(".trimvia-prev-section-count");
        if (countEl) {
          const cardCount = root.querySelectorAll(".trimvia-prev-cards .trimvia-prev-card, .trimvia-prev-cards .accordion-item, .trimvia-prev-cards .pm-consultation-wrap").length;
          countEl.textContent = String(cardCount);
        }
      }

      root.querySelectorAll(".trimvia-prev-card").forEach(enhancePreviousCard);

      pendingItems.forEach((item) => {
        item.classList.add("trimvia-prev-card", "trimvia-previous-consultation-item");
        enhancePreviousCard(item);
      });

      pendingWraps.forEach((wrap) => {
        wrap.classList.add("trimvia-prev-card", "trimvia-previous-consultation-item");
        normalizeConsultMeta(wrap);
        wrapConsultGroups(wrap);
        initConsultFaqItems(wrap);
      });

      root.querySelectorAll(".pm-consultation-accordion").forEach((accordion) => {
        if (accordion.classList.contains("trimvia-prev-cards")) {
          return;
        }

        if (!accordion.querySelector(".accordion-item, .trimvia-prev-card")) {
          accordion.remove();
        }
      });

      root.querySelectorAll(".trimvia-consult-faq--previous").forEach((legacyShell) => {
        const nestedCard = legacyShell.querySelector(".trimvia-prev-card, .accordion-item");
        if (nestedCard && legacyShell.parentElement) {
          legacyShell.parentElement.insertBefore(nestedCard, legacyShell);
        }
        legacyShell.remove();
      });
    };

    const wrapConsultGroups = (root) => {
      if (!root) {
        return;
      }

      root.querySelectorAll(".prescription-patient-data.patient-consultation").forEach((container) => {
        if (container.dataset.trimviaGroupsReady === "1") {
          return;
        }

        const headers = [...container.querySelectorAll(":scope > .patient-group-header")];
        if (!headers.length) {
          return;
        }

        container.dataset.trimviaGroupsReady = "1";

        headers.reverse().forEach((header) => {
          if (header.closest(".trimvia-consult-group")) {
            return;
          }

          const group = document.createElement("div");
          group.className = "trimvia-consult-group";
          header.parentNode.insertBefore(group, header);
          group.appendChild(header);

          let sibling = group.nextElementSibling;
          while (sibling && !sibling.classList.contains("patient-group-header")) {
            const next = sibling.nextElementSibling;
            if (sibling.classList.contains("patient-row")) {
              group.appendChild(sibling);
            }
            sibling = next;
          }

          convertRowsToFaq(group);
        });
      });
    };

    const enhanceConsultSection = (root) => {
      if (!root) {
        return;
      }

      if (root.id === "pmPreviousConsultations") {
        enhancePreviousConsultationsSection(root);
        return;
      }

      if (root.id === "pmCurrentConsultations") {
        buildConsultFaqShell(root, "Current Order");
        return;
      }

      buildConsultFaqShell(root);
    };

    const normalizeAccordionItem = (item) => {
      if (!item || item.dataset.trimviaConsultAccordionReady === "1") {
        return;
      }

      const body = item.querySelector(".accordion-body");
      const button = item.querySelector(".accordion-button");
      if (!body || !button) {
        return;
      }

      item.dataset.trimviaConsultAccordionReady = "1";
      button.classList.add("trimvia-consultation-toggle");

      const isCurrentOrder = Boolean(item.closest("#pmCurrentConsultations"));
      const isPreviousCard = item.classList.contains("trimvia-prev-card") || Boolean(item.closest(".trimvia-prev-cards"));
      if (isPreviousCard) {
        enhancePreviousCard(item);
        return;
      }

      const isFaqCard = Boolean(item.closest(".trimvia-consult-faq"));
      if (isCurrentOrder || isFaqCard) {
        [".cons-title", ".cons-completedby"].forEach((selector) => {
          item.querySelectorAll(selector).forEach((node) => {
            if (body.contains(node)) {
              return;
            }
            body.insertBefore(node, body.firstChild);
          });
        });
      } else {
        button.classList.add("trimvia-previous-consultation-toggle");
      }

      normalizeConsultMeta(body);
      wrapConsultGroups(body);
      initConsultFaqItems(body);
    };

    const scanModal = (modal) => {
      if (!modal) return;
      modal.querySelectorAll("#pmCurrentConsultations, #pmPreviousConsultations").forEach(enhanceConsultSection);
      modal.querySelectorAll("#pmPreviousConsultations .trimvia-prev-card, #pmPreviousConsultations .accordion-item").forEach((item) => {
        if (item.closest("#pmCurrentConsultations")) {
          return;
        }
        if (item.classList.contains("trimvia-prev-card")) {
          enhancePreviousCard(item);
          return;
        }
        normalizeAccordionItem(item);
      });
      modal.querySelectorAll("#pmCurrentConsultations .accordion-item").forEach(normalizeAccordionItem);
      modal.querySelectorAll("#pmPreviousConsultations .pm-consultation-wrap").forEach((section) => {
        normalizeConsultMeta(section);
        wrapConsultGroups(section);
        initConsultFaqItems(section);
      });
    };

    const attachModalConsultScan = (modal) => {
      if (!modal) {
        return;
      }

      scanModal(modal);

      if (modal.dataset.trimviaConsultAccordionObserved === "1") {
        return;
      }

      modal.dataset.trimviaConsultAccordionObserved = "1";

      if (typeof MutationObserver === "undefined") {
        return;
      }

      let scanTimer = null;
      const scheduleScan = () => {
        if (scanTimer) {
          window.clearTimeout(scanTimer);
        }
        scanTimer = window.setTimeout(() => {
          scanTimer = null;
          scanModal(modal);
        }, 60);
      };

      const observer = new MutationObserver(scheduleScan);
      observer.observe(modal, { childList: true, subtree: true });
    };

    modalIds.forEach((id) => {
      attachModalConsultScan(document.getElementById(id));
    });

    document.addEventListener("shown.bs.modal", (event) => {
      const modal = event.target;
      if (!modal || !modal.id || !modalIds.includes(modal.id)) {
        return;
      }
      window.setTimeout(() => attachModalConsultScan(modal), 0);
    });

    if (window.jQuery) {
      window.jQuery(document).on(
        "shown.bs.modal",
        modalIds.map((id) => `#${id}`).join(", "),
        function onPrescriberModalShown() {
          window.setTimeout(() => attachModalConsultScan(this), 0);
        }
      );
    }

    if (typeof MutationObserver !== "undefined" && document.body) {
      const bodyObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType !== 1) {
              return;
            }

            modalIds.forEach((id) => {
              if (node.id === id) {
                attachModalConsultScan(node);
                return;
              }

              const nestedModal = node.querySelector?.(`#${id}`);
              if (nestedModal) {
                attachModalConsultScan(nestedModal);
              }
            });
          });
        });
      });
      bodyObserver.observe(document.body, { childList: true, subtree: true });
    }
  };

  const initConsultationPatientModal = () => {
    const modal = document.getElementById("consultation-patient-modal");
    if (!modal) return;

    const moveModalToBody = () => {
      if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
      }
    };

    const getBackdrop = () => document.querySelector(".modal-backdrop");

    const hideConsultationModal = () => {
      const jq = window.jQuery;
      if (jq && typeof jq.fn.modal === "function") {
        jq(modal).modal("hide");
        return;
      }

      modal.classList.remove("show");
      modal.style.display = "none";
      modal.setAttribute("aria-hidden", "true");
      modal.removeAttribute("aria-modal");
      document.body.classList.remove("modal-open");
      document.querySelectorAll(".modal-backdrop").forEach((backdrop) => backdrop.remove());
    };

    const showConsultationModal = (event) => {
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }

      moveModalToBody();
      modal.removeAttribute("hidden");

      const jq = window.jQuery;
      if (jq && typeof jq.fn.modal === "function") {
        jq(modal).modal("show");
        return;
      }

      modal.classList.add("show");
      modal.style.display = "flex";
      modal.setAttribute("aria-hidden", "false");
      modal.setAttribute("aria-modal", "true");
      document.body.classList.add("modal-open");

      if (!getBackdrop()) {
        const backdrop = document.createElement("div");
        backdrop.className = "modal-backdrop fade show";
        backdrop.addEventListener("click", hideConsultationModal);
        document.body.appendChild(backdrop);
      }
    };

    moveModalToBody();

    const jq = window.jQuery;
    if (jq && typeof jq.fn.modal === "function") {
      jq(modal).on("show.bs.modal", moveModalToBody);
      jq(modal).on("hidden.bs.modal", () => {
        document.body.classList.remove("modal-open");
        jq(".modal-backdrop").remove();
      });
    }

    document.addEventListener(
      "click",
      (event) => {
        const trigger = event.target.closest(
          '[data-target="#consultation-patient-modal"], [data-bs-target="#consultation-patient-modal"], .trimvia-view-consultation-btn'
        );
        if (!trigger) return;
        showConsultationModal(event);
      },
      true
    );

    modal.addEventListener("click", (event) => {
      if (event.target === modal) {
        hideConsultationModal();
      }
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initCommon);
  } else {
    initCommon();
  }
})();
