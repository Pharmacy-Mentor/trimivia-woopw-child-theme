(() => {
  const initHeaderScroll = () => {
    const header = document.getElementById("header");
    if (!header) return;
    const onScroll = () => header.classList.toggle("scrolled", window.scrollY > 20);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  };

  const initRevealOnScroll = () => {
    const revealEls = document.querySelectorAll(".rv");
    if (!revealEls.length || typeof IntersectionObserver === "undefined") return;
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

  const initCommon = () => {
    initHeaderScroll();
    initMobileMenu();
    initRevealOnScroll();
    initFaqAccordion();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initCommon);
  } else {
    initCommon();
  }
})();
