(function () {
  'use strict';

  // Run only on pages where the base script is present
  if (typeof window === 'undefined') return;

  var focusableSelectors = [
    'a[href]','area[href]','input:not([disabled]):not([type="hidden"])','select:not([disabled])',
    'textarea:not([disabled])','button:not([disabled])','iframe','object','embed',
    '[contenteditable]','[tabindex]:not([tabindex="-1"])'
  ].join(',');

  var idCounter = 0;
  var lastActiveEl = null;
  var disabledOutside = [];
  var clickHandlerByModal = new WeakMap();
  var openHandled = new WeakSet();
  var allowEscapeToChrome = false;
  var allowEscapeTimer = null;

  function visible(el) {
    if (!el) return false;
    var style = window.getComputedStyle(el);
    return style && style.visibility !== 'hidden' && style.display !== 'none' && el.offsetParent !== null;
  }

  function firstFocusable(modal) {
    var nodes = modal.querySelectorAll(focusableSelectors);
    for (var i = 0; i < nodes.length; i++) {
      if (visible(nodes[i])) return nodes[i];
    }
    return null;
  }

  var currentOpenModal = null;

  function trapKeydown(e) {
    if (!currentOpenModal) return;
    var key = e.key;
    var lower = (key || '').toLowerCase();
    if (key === 'F6' || (e.ctrlKey && lower === 'l') || (e.altKey && lower === 'd')) {
      allowEscapeToChrome = true;
      if (allowEscapeTimer) clearTimeout(allowEscapeTimer);
      allowEscapeTimer = setTimeout(function(){ allowEscapeToChrome = false; }, 1500);
      return;
    }
    if (key !== 'Tab') return;
    var modal = currentOpenModal;
    // Only trap if focus is outside or within the modal
    if (!modal.contains(document.activeElement)) {
      var focusStart = firstFocusable(modal) || modal;
      if (focusStart && typeof focusStart.focus === 'function') {
        e.preventDefault();
        focusStart.focus();
      }
      return;
    }
    var all = Array.prototype.slice.call(modal.querySelectorAll(focusableSelectors))
      .filter(function (el) { return visible(el) && !el.hasAttribute('disabled'); });
    if (!all.length) return;
    var first = all[0];
    var last = all[all.length - 1];
    if (e.shiftKey) {
      if (document.activeElement === first) {
        e.preventDefault();
        last.focus();
      }
    } else {
      if (document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
  }

  // Track previous states so we can restore accurately
  var toggledState = new WeakMap();

  function supportsInert() {
    var el = document.createElement('div');
    return ('inert' in el);
  }

  function setPageHidden(hidden, modal) {
    // Deactivate everything except the modal element
    var children = Array.prototype.slice.call(document.body.children);
    var useInert = supportsInert();

    children.forEach(function (node) {
      if (node === modal) return;
      if (hidden) {
        // Save prior state
        if (!toggledState.has(node)) {
          toggledState.set(node, {
            aria: node.getAttribute('aria-hidden'),
            inert: node.hasAttribute('inert'),
            pe: node.style.pointerEvents
          });
        }
        if (useInert) {
          node.setAttribute('inert', '');
          // Some UAs require pointer-events none for clicks even with inert
          node.style.pointerEvents = 'none';
        } else {
          node.setAttribute('aria-hidden', 'true');
          node.style.pointerEvents = 'none';
        }
      } else {
        var prev = toggledState.get(node);
        if (prev) {
          if (useInert) {
            if (!prev.inert) {
              node.removeAttribute('inert');
            }
          } else {
            if (prev.aria === null) {
              node.removeAttribute('aria-hidden');
            } else {
              node.setAttribute('aria-hidden', prev.aria);
            }
          }
          node.style.pointerEvents = prev.pe || '';
          toggledState.delete(node);
        } else {
          // Best-effort cleanup
          if (useInert) {
            node.removeAttribute('inert');
          } else {
            node.removeAttribute('aria-hidden');
          }
          node.style.pointerEvents = '';
        }
      }
    });
  }

  function ensureIds(modal) {
    var content = modal.querySelector('.tingle-modal-box__content');
    if (!content) return;
    var title = content.querySelector('h1, h2, h3, [data-modal-title]');
    var desc = content.querySelector('p, [data-modal-desc]');
    if (title) {
      if (!title.id) {
        title.id = 'frm-abdn-modal-title-' + (++idCounter);
      }
      modal.setAttribute('aria-labelledby', title.id);
    } else {
      modal.removeAttribute('aria-labelledby');
    }
    if (desc) {
      if (!desc.id) {
        desc.id = 'frm-abdn-modal-desc-' + (++idCounter);
      }
      modal.setAttribute('aria-describedby', desc.id);
    } else {
      modal.removeAttribute('aria-describedby');
    }
  }

  function enhanceModal(modal) {
    if (!modal || modal.getAttribute('data-apg-enhanced') === '1') return;
    modal.setAttribute('data-apg-enhanced', '1');
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');

    // Prepare focus trap and state handlers
    function disableOutsideFocus(modal) {
      // Fallback when inert is not supported: explicitly pull outside elements from tab order
      if (supportsInert()) return;
      disabledOutside = [];
      var all = Array.prototype.slice.call(document.querySelectorAll(focusableSelectors));
      all.forEach(function (el) {
        if (modal.contains(el)) return;
        var prev = {
          el: el,
          tabindexAttr: el.getAttribute('tabindex')
        };
        disabledOutside.push(prev);
        el.setAttribute('tabindex', '-1');
      });
    }

    function restoreOutsideFocus() {
      if (supportsInert()) return;
      disabledOutside.forEach(function (rec) {
        if (rec.tabindexAttr === null) {
          rec.el.removeAttribute('tabindex');
        } else {
          rec.el.setAttribute('tabindex', rec.tabindexAttr);
        }
      });
      disabledOutside = [];
    }

    function onOpen() {
      if (openHandled.has(modal)) return; // idempotent
      openHandled.add(modal);
      lastActiveEl = document.activeElement;
      ensureIds(modal);
      setPageHidden(true, modal);
      currentOpenModal = modal;
      var useInert = supportsInert();
      if (!useInert) {
        document.addEventListener('keydown', trapKeydown, true);
        document.addEventListener('focusin', keepFocusInModal, true);
        disableOutsideFocus(modal);
      }
      // Make container programmatically focusable as a fallback
      var box = modal.querySelector('.tingle-modal-box');
      if (box && !box.hasAttribute('tabindex')) {
        box.setAttribute('tabindex', '-1');
      }
      var target = modal.querySelector('[autofocus]') || firstFocusable(modal) || box || modal;
      if (target === modal && !modal.hasAttribute('tabindex')) {
        modal.setAttribute('tabindex', '-1');
      }
      if (target && typeof target.focus === 'function') {
        setTimeout(function(){ target.focus(); }, 0);
      }
      // Normalize anchors that would cause '#'
      var anchors = modal.querySelectorAll('a');
      for (var i=0;i<anchors.length;i++) {
        var a = anchors[i];
        var href = (a.getAttribute('href') || '').trim();
        if (a.classList.contains('frm-abandonment-close') || href === '#' || href === '' || href === '#!') {
          if (!a.hasAttribute('data-original-href')) {
            a.setAttribute('data-original-href', href);
          }
          a.setAttribute('href', 'javascript:void(0)');
          a.setAttribute('aria-label', 'Close');
          if (!a.hasAttribute('role')) a.setAttribute('role', 'button');
        }
      }

      // Prevent '#' navigation on close links (capture to beat stopImmediatePropagation)
      var clickHandler = function (e) {
        var t = e.target.closest('a');
        if (!t) return;
        var href = t.getAttribute('href');
        if (t.classList.contains('frm-abandonment-close') || href === '#' || href === '' || href === '#!') {
          e.preventDefault();
        }
      };
      clickHandlerByModal.set(modal, clickHandler);
      modal.addEventListener('click', clickHandler, true);

      // Apply text overrides then enhance ARIA semantics
      applyContentOverrides(modal);
      enhanceFieldA11y(modal);
    }

    function onClose() {
      if (!openHandled.has(modal)) return; // only run once per open cycle
      openHandled.delete(modal);
      document.removeEventListener('keydown', trapKeydown, true);
      document.removeEventListener('focusin', keepFocusInModal, true);
      currentOpenModal = null;
      setPageHidden(false, modal);
      if (!supportsInert()) {
        restoreOutsideFocus();
      }
      // Restore any anchor hrefs we changed
      var restore = modal.querySelectorAll('a[data-original-href]');
      for (var j=0;j<restore.length;j++) {
        var an = restore[j];
        var orig = an.getAttribute('data-original-href');
        if (orig === '') {
          an.removeAttribute('href');
        } else {
          an.setAttribute('href', orig);
        }
        an.removeAttribute('data-original-href');
      }
      var ch = clickHandlerByModal.get(modal);
      if (ch) {
        modal.removeEventListener('click', ch);
        clickHandlerByModal.delete(modal);
      }
      if (lastActiveEl && typeof lastActiveEl.focus === 'function') {
        setTimeout(function(){ lastActiveEl.focus(); }, 0);
      }
    }

    function keepFocusInModal(e) {
      if (!currentOpenModal) return;
      if (!currentOpenModal.contains(e.target)) {
        if (allowEscapeToChrome) {
          allowEscapeToChrome = false;
          return;
        }
        var target = currentOpenModal.querySelector('[autofocus]') || firstFocusable(currentOpenModal) || currentOpenModal;
        if (target && typeof target.focus === 'function') {
          e.stopPropagation();
          setTimeout(function(){ target.focus(); }, 0);
        }
      }
    }

    // Observe visibility by class change from tingle
    var observer = new MutationObserver(function () {
      if (modal.classList.contains('tingle-modal--visible')) {
        onOpen();
      } else {
        onClose();
      }
    });
    observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
    // If modal is already visible when we attach, run open logic immediately
    if (modal.classList.contains('tingle-modal--visible')) {
      onOpen();
    }
  }

  function enhanceFieldA11y(modal) {
    var content = modal.querySelector('.tingle-modal-box__content');
    if (!content) return;
    var input = content.querySelector('#frm-abandonment-modal-email, input[type="email"]');
    if (!input) return;

    // aria-required for required field
    input.setAttribute('aria-required', 'true');

    // Build ids for error and description
    var error = content.querySelector('.frm-abandonment-field-error, .frm_error[role="alert"]');
    // If admin provided a custom error message, override the text
    try {
      if (window.FrmAbdnApg && window.FrmAbdnApg.errorMessage && error) {
        error.textContent = String(window.FrmAbdnApg.errorMessage);
      }
    } catch (e) {}
    if (error) {
      if (!error.id) {
        error.id = 'frm-abdn-modal-error-' + (++idCounter);
      }
      // Ensure role alert for SR announcement
      if (error.hasAttribute('role')) {
        error.removeAttribute('role');
      }
    }
    var desc = content.querySelector('.frm_description p, [data-modal-desc], .frm_description');
    if (desc && !desc.id) {
      desc.id = 'frm-abdn-modal-desc-' + (++idCounter);
    }

    function updateDescribedBy() {
      var ids = [];
      if (error && !error.classList.contains('frm-abandonment-btn-invisible')) {
        ids.push(error.id);
      }
      if (desc) {
        ids.push(desc.id);
      }
      if (ids.length) {
        input.setAttribute('aria-describedby', ids.join(' '));
      } else {
        input.removeAttribute('aria-describedby');
      }
    }

    function updateInvalid() {
      var invalid = false;
      if (error && !error.classList.contains('frm-abandonment-btn-invisible')) {
        invalid = true;
      } else if (typeof input.checkValidity === 'function') {
        // Use native constraint validation as a fallback
        invalid = !input.checkValidity();
      }
      if (invalid) {
        input.setAttribute('aria-invalid', 'true');
      } else {
        input.removeAttribute('aria-invalid');
      }
    }

    // Initial sync
    updateInvalid();
    updateDescribedBy();

    // React to changes: typing and error visibility toggles
    input.addEventListener('input', function () {
      updateInvalid();
      updateDescribedBy();
    });
    if (error) {
      var mo = new MutationObserver(function (muts) {
        for (var i=0;i<muts.length;i++) {
          if (muts[i].type === 'attributes' && muts[i].attributeName === 'class') {
            updateInvalid();
            updateDescribedBy();
            break;
          }
        }
      });
      mo.observe(error, { attributes: true, attributeFilter: ['class'] });
    }
  }

  function applyContentOverrides(modal) {
    if (!window.FrmAbdnApg) return;
    var cfg = window.FrmAbdnApg;
    var content = modal.querySelector('.tingle-modal-box__content');
    if (!content) return;

    try {
      // Title
      if (cfg.title) {
        var titleEl = content.querySelector('h1, h2, h3, [data-modal-title]');
        if (titleEl) titleEl.textContent = String(cfg.title);
      }
      // Description
      if (cfg.description) {
        var descEl = content.querySelector('.frm_description p, [data-modal-desc], .frm_description');
        if (descEl) {
          // If container, prefer first paragraph
          var p = descEl.matches('.frm_description') ? descEl.querySelector('p') || descEl : descEl;
          p.textContent = String(cfg.description);
        }
      }
      // Label
      if (cfg.label) {
        var input = content.querySelector('#frm-abandonment-modal-email, input[type="email"]');
        var labelEl = null;
        if (input && input.id) {
          labelEl = content.querySelector('label[for="' + input.id + '"]');
        }
        if (!labelEl) {
          labelEl = content.querySelector('label.frm_primary_label');
        }
        if (labelEl) {
          var required = labelEl.querySelector('.frm_required');
          labelEl.textContent = String(cfg.label);
          if (required) {
            labelEl.appendChild(required);
          }
        }
      }
      // Footer primary button
      if (cfg.button) {
        var btn = modal.querySelector('.tingle-modal-box__footer .frm-abandonment-modal-btn-primary, .tingle-modal-box__footer button');
        if (btn) btn.textContent = String(cfg.button);
      }
      // Close labels
      if (cfg.close) {
        var closeA = content.querySelector('a.frm-abandonment-close');
        if (closeA) {
          closeA.setAttribute('aria-label', String(cfg.close));
          closeA.setAttribute('title', String(cfg.close));
        }
        var tingleCloseLabel = modal.querySelector('.tingle-modal__closeLabel');
        if (tingleCloseLabel) tingleCloseLabel.textContent = String(cfg.close);
      }
    } catch (e) {}
  }

  function scan() {
    var all = document.querySelectorAll('.tingle-modal');
    for (var i = 0; i < all.length; i++) {
      enhanceModal(all[i]);
    }
  }

  // Initial scan and watch for dynamically created modal elements
  scan();
  var rootObserver = new MutationObserver(scan);
  rootObserver.observe(document.documentElement, { childList: true, subtree: true });
})();
