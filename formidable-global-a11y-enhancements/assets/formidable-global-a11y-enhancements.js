/* global jQuery */
(function ($) {
  'use strict';

  function safeFocus($el) {
    if (!$el || !$el.length) return;
    var added = false;
    if (!$el.is('[tabindex]')) { $el.attr('tabindex', '-1'); added = true; }
    $el.trigger('focus');
    if (added || $el.attr('tabindex') === '-1') {
      if (typeof trackTabindex === 'function') try { trackTabindex($el, 'safeFocus'); } catch (e) {}
    }
  }

  // Read settings localized from PHP with sensible defaults
  var CFG = window.ff_globa11y || {
    other_fields_fix: true,
    global_message_focus: false,
    multi_page_focus: true,
    has_accessible_errors: false,
    debug_tabindex: false
  };
  CFG.has_accessible_errors = !!CFG.has_accessible_errors;
  try {
    if (!CFG.debug_tabindex) {
      var qs = (window.location && window.location.search) || '';
      if (/([?&])ff_globa11y_debug=1(?!\d)/.test(qs)) CFG.debug_tabindex = true;
    }
  } catch (e) {}

  function trackTabindex($el, label) {
    if (!CFG.debug_tabindex || !$el || !$el.length || typeof MutationObserver === 'undefined') return;
    var el = $el.get(0);
    try {
      if (el.__ffTabindexObserver) {
        try { el.__ffTabindexObserver.disconnect(); } catch (e) {}
      }
      var observer = new MutationObserver(function () {
        var current = el.getAttribute('tabindex');
        if (current !== '-1') {
          // eslint-disable-next-line no-console
          console.warn('[ff_globa11y] tabindex changed/removed', { label: label || '', element: el, tabindex: current });
          try { observer.disconnect(); } catch (e) {}
        }
      });
      observer.observe(el, { attributes: true, attributeFilter: ['tabindex'] });
      el.__ffTabindexObserver = observer;
    } catch (e) {}
  }

  // 1) "Other" inputs: hide duplicate SR labels, move text into aria-label, tidy error roles
  function enhanceOtherInputs(context) {
    var $ctx = context ? $(context) : $(document);
    $ctx.find('.frm_other_input').each(function () {
      var $input = $(this);
      var $srLabel = $input.siblings('label.frm_screen_reader').first();
      if ($srLabel.length) {
        $srLabel.css('display', 'none');
        var txt = ($srLabel.html() || '').trim();
        if (txt) $input.attr('aria-label', txt);
      }
    });
    $ctx.find('.frm_error_style[role]').removeAttr('role');
  }

  // 2) Focus management for global errors and success messages
  function focusMessages(context) {
    var $ctx = context ? $(context) : $(document);
    if (CFG.has_accessible_errors) return; // let Accessible Errors plugin handle focus

    // Prefer accessible error summary if present
    var $ffSummary = $ctx.find('.ff-global-errors').first();
    if ($ffSummary.length) {
      var $head = $ffSummary.find('[role="heading"], h2, h3').first();
      if ($head.length) {
        $head.attr('tabindex', '-1');
        try { trackTabindex($head, 'ff-global-errors heading'); } catch (e) {}
        safeFocus($head);
        return;
      }
      $ffSummary.attr('tabindex', '-1');
      try { trackTabindex($ffSummary, 'ff-global-errors container'); } catch (e) {}
      safeFocus($ffSummary);
      return;
    }

    // Legacy/global errors fallback
    var $errorHeading = $ctx.find('.global-errors h2').first();
    if ($errorHeading.length) {
      $errorHeading.attr('tabindex', '-1');
      try { trackTabindex($errorHeading, 'legacy global-errors h2'); } catch (e) {}
      safeFocus($errorHeading);
      return;
    }

    // Success messages
    var $msg = $ctx.find('.frm_message').first();
    if ($msg.length) {
      $msg.attr('tabindex', '-1');
      try { trackTabindex($msg, 'frm_message'); } catch (e) {}
      safeFocus($msg);
      return;
    }
    return;
  }

  // 3) Multi-page: focus H1 in the current page/form after navigation
  function focusFirstHeadingIn($root) {
    var $h1 = $root && $root.length ? $root.find('h1:visible').first() : $();
    if (!$h1 || !$h1.length) {
      $h1 = $('h1:visible').first();
    }
    if ($h1.length) {
      safeFocus($h1);
      return true;
    }
    return false;
  }

  function attemptFocusH1Around($el, tries, delay) {
    tries = typeof tries === 'number' ? tries : 10;
    delay = typeof delay === 'number' ? delay : 120;
    var $root = $el && $el.length ? $el.closest('form, .frm-show-form, .frm_form_fields') : $(document);
    // Prefer errors over heading after multi-page navigation
    var $foundSummary = $root.find('.ff-global-errors, .global-errors h2').first();
    if (!$foundSummary.length) {
      $foundSummary = $(document).find('.ff-global-errors, .global-errors h2').first();
    }
    if ($foundSummary.length) {
      if (CFG.has_accessible_errors) {
        // Accessible Errors will handle focusing the summary; avoid overriding
        return;
      }
      // Otherwise, use our own message focusing
      focusMessages($foundSummary.closest('form, .frm-show-form, .frm_form_fields').length ? $foundSummary.closest('form, .frm-show-form, .frm_form_fields') : document);
      return;
    }
    var done = focusFirstHeadingIn($root);
    if (done) return;
    if (tries <= 0) return;
    setTimeout(function () { attemptFocusH1Around($el, tries - 1, delay); }, delay);
  }

  $(function () {
    if (CFG.other_fields_fix) {
      enhanceOtherInputs(document);
    }
    if (CFG.global_message_focus && !CFG.has_accessible_errors) {
      focusMessages(document);
    }

    // Multi-page: handle following scenarios
    if (CFG.multi_page_focus) {
      // a) If we previously navigated pages via full reload, focus now
      try {
        if (window.sessionStorage && sessionStorage.getItem('fga11y_mp_focus') === '1') {
          attemptFocusH1Around($(document));
          sessionStorage.removeItem('fga11y_mp_focus');
        }
      } catch (e) {}

      // b) When clicking next/prev page buttons
      $(document).on('click', '.frm_next_page, .frm_prev_page', function () {
        try { if (window.sessionStorage) sessionStorage.setItem('fga11y_mp_focus', '1'); } catch (e) {}
        // Try focusing shortly after click for ajax-based transitions
        attemptFocusH1Around($(this));
        setTimeout(function () { attemptFocusH1Around($(this)); }.bind(this), 250);
        setTimeout(function () { attemptFocusH1Around($(this)); }.bind(this), 600);
      });
    }

    $(document).on('ajaxComplete', function () {
      if (CFG.other_fields_fix) {
        enhanceOtherInputs(document);
      }
      if (CFG.global_message_focus && !CFG.has_accessible_errors) {
        focusMessages(document);
      }
      if (CFG.multi_page_focus) {
        attemptFocusH1Around($(document));
      }
    });

    setTimeout(function () {
      if (CFG.other_fields_fix) {
        enhanceOtherInputs(document);
      }
      if (CFG.global_message_focus) {
        focusMessages(document);
      }
      if (CFG.multi_page_focus) {
        attemptFocusH1Around($(document));
      }
    }, 0);
  });
})(jQuery);
