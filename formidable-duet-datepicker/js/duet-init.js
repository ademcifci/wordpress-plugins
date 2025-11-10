/* global wp, FrmDuetPickerCfg */
(function () {
  'use strict';

  // Simple registry to help link separate Start/End fields.
  const duetRegistry = {
    inputToDuet: new WeakMap(), // input HTMLElement -> duet HTMLElement
  };

  /**
   * Utility: trigger a native + jQuery change event on an element.
   */
  function triggerChange(el) {
    try {
      const evt = new Event('change', { bubbles: true });
      el.dispatchEvent(evt);
    } catch (e) {
      // IE fallback
      const evt = document.createEvent('HTMLEvents');
      evt.initEvent('change', true, false);
      el.dispatchEvent(evt);
    }
    if (window.jQuery) {
      window.jQuery(el).trigger('change');
    }
  }

  /**
   * Map Formidable date config to Duet properties.
   */
  function mapConfigToDuetProps(settings) {
    const props = {};
    try {
      // Handle both legacy and new structures defensively.
      const options = settings && (settings.options || settings.datepickerOptions || {});
      const frm = settings && (settings.formidable_dates || {});

      if (options.minDate) props.min = normalizeDate(options.minDate);
      if (options.maxDate) props.max = normalizeDate(options.maxDate);

      // first day of week
      const firstDay = options.firstDay != null ? parseInt(options.firstDay, 10) : (FrmDuetPickerCfg && FrmDuetPickerCfg.startOfWeek) || 1;
      props.firstDayOfWeek = isNaN(firstDay) ? 1 : firstDay;

      // default date
      if (options.defaultDate) props.value = normalizeDate(options.defaultDate);

      // Disabled dates (array of yyyy-mm-dd)
      const disabled = (frm && frm.datesDisabled) || options.datesDisabled || [];
      if (Array.isArray(disabled) && disabled.length) {
        const disabledSet = new Set(disabled.map(normalizeDate));
        props.isDateDisabled = (date) => disabledSet.has(formatDate(date));
      }

      // Days of week disabled (1-7; convert to 0-6)
      const daysDisabled = (frm && frm.daysOfTheWeekDisabled) || options.daysOfTheWeekDisabled;
      if (Array.isArray(daysDisabled) && daysDisabled.length) {
        const days = new Set(daysDisabled.map((d) => ((parseInt(d, 10) % 7) + 7) % 7));
        const prev = props.isDateDisabled;
        props.isDateDisabled = (date) => (prev && prev(date)) || days.has(date.getDay());
      }
    } catch (e) {
      // no-op; use safe defaults
    }
    // Locale code
    try {
      props.locale = (settings && settings.locale) || document.documentElement.lang || navigator.language || 'en';
    } catch (e) {}

    return props;
  }

  function getPropsFromDataset(input) {
    const props = {};
    const ds = input.dataset || {};
    const locale = ds.duetLocale || null;
    if (locale) props.locale = locale;

    // Optional custom display/input format(s), e.g. "DD-MM-YYYY" or "DD-MM-YYYY|YYYY".
    if (ds.duetFormat) props.format = ds.duetFormat;

    // Min/Max support absolute date or relative offsets.
    if (ds.duetMin) props.min = normalizeDateOrOffset(ds.duetMin);
    if (ds.duetMax) props.max = normalizeDateOrOffset(ds.duetMax);

    // Disabled specific dates (JSON array of yyyy-mm-dd)
    if (ds.duetDisabledDates) {
      try {
        const arr = JSON.parse(ds.duetDisabledDates);
        if (Array.isArray(arr) && arr.length) {
          const set = new Set(arr.map(normalizeDate));
          props.isDateDisabled = (d) => set.has(formatDate(d)) || (props.isDateDisabled && props.isDateDisabled(d));
        }
      } catch (e) {}
    }

    // Disabled days of week (comma list 0-6)
    if (ds.duetDaysDisabled) {
      const parts = ds.duetDaysDisabled.split(',').map((x) => parseInt(x, 10)).filter((n) => !isNaN(n));
      if (parts.length) {
        const days = new Set(parts);
        const prev = props.isDateDisabled;
        props.isDateDisabled = (date) => (prev && prev(date)) || days.has(date.getDay());
      }
    }

    // Year-only UI toggle
    if (ds.duetYearOnly === '1') props.yearOnly = true;

    return props;
  }

  function normalizeDate(input) {
    // Accept Date, timestamp, or string; output yyyy-mm-dd
    try {
      if (input instanceof Date) return formatDate(input);
      if (typeof input === 'number') return formatDate(new Date(input));
      if (typeof input === 'string') {
        // Attempt to coerce; supports already-correct yyyy-mm-dd
        if (/^\d{4}-\d{2}-\d{2}$/.test(input)) return input;
        const d = new Date(input);
        if (!isNaN(d)) return formatDate(d);
      }
    } catch (e) {}
    return undefined;
  }

  // Build a Duet dateAdapter supporting custom parse/format patterns.
  function buildDateAdapter(formatStr) {
    const formats = (formatStr || '').split('|').map((s) => s.trim()).filter(Boolean);
    const primary = formats[0] || 'YYYY-MM-DD';

    function pad2(n) { return String(n).padStart(2, '0'); }

    function formatISOTo(fmt, iso) {
      if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return '';
      const [y, m, d] = iso.split('-');
      if (fmt === 'YYYY') return y;
      return fmt
        .replace(/YYYY/g, y)
        .replace(/MM/g, m)
        .replace(/DD/g, d);
    }

    function parseWith(fmt, value) {
      if (!fmt || !value) return undefined;
      const v = String(value).trim();
      if (fmt === 'YYYY') {
        const m = v.match(/^(\d{4})$/);
        if (m) return new Date(parseInt(m[1], 10), 0, 1);
        return undefined;
      }
      // Escape regex special chars in fmt, then replace tokens with capture groups
      const esc = fmt.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const rxStr = esc
        .replace(/YYYY/g, '(\\d{4})')
        .replace(/MM/g, '(\\d{1,2})')
        .replace(/DD/g, '(\\d{1,2})');
      const rx = new RegExp('^' + rxStr + '$');
      const m = v.match(rx);
      if (!m) return undefined;
      // Determine token order
      const order = [];
      let i = 0;
      while (i < fmt.length) {
        if (fmt.startsWith('YYYY', i)) { order.push('Y'); i += 4; continue; }
        if (fmt.startsWith('MM', i)) { order.push('M'); i += 2; continue; }
        if (fmt.startsWith('DD', i)) { order.push('D'); i += 2; continue; }
        i += 1; // skip separator
      }
      const parts = { Y: '', M: '', D: '' };
      let gi = 1;
      for (const t of order) {
        if (gi >= m.length) break;
        parts[t] = m[gi++];
      }
      const year = parseInt(parts.Y, 10);
      const month = parseInt(parts.M, 10);
      const day = parseInt(parts.D, 10);
      if (!year || isNaN(year)) return undefined;
      const mm = isNaN(month) ? 1 : Math.min(Math.max(month, 1), 12);
      const dd = isNaN(day) ? 1 : Math.min(Math.max(day, 1), 31);
      return new Date(year, mm - 1, dd);
    }

    function parseValue(value) {
      // Only parse when a configured format fully matches.
      // This avoids "autofilling" a date when user has typed only a character or partial input.
      for (const f of formats) {
        const dt = parseWith(f, value);
        if (dt instanceof Date && !isNaN(dt)) return dt;
      }
      return undefined;
    }

    return {
      parse(value) {
        const d = parseValue(value);
        return d instanceof Date && !isNaN(d) ? d : undefined;
      },
      format(input) {
        // Duet may pass ISO string or a Date. Normalize to ISO first.
        let iso = undefined;
        try {
          if (input instanceof Date) {
            iso = formatDate(input);
          } else if (typeof input === 'number') {
            iso = formatDate(new Date(input));
          } else if (typeof input === 'string') {
            iso = normalizeDate(input);
          }
        } catch (e) {}
        return formatISOTo(primary, iso || '');
      },
    };
  }

  function normalizeDateOrOffset(input) {
    if (!input) return undefined;
    const str = String(input).trim().toLowerCase().replace(/\s+/g, '');
    const m = str.match(/^([+-]\d+)(day|days|month|months|year|years)$/);
    if (m) {
      const amount = parseInt(m[1], 10);
      const unit = m[2];
      const d = new Date();
      if (unit.startsWith('day')) d.setDate(d.getDate() + amount);
      else if (unit.startsWith('month')) d.setMonth(d.getMonth() + amount);
      else if (unit.startsWith('year')) d.setFullYear(d.getFullYear() + amount);
      return formatDate(d);
    }
    return normalizeDate(str);
  }

  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function formatDate(d) {
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
  }

  /**
   * Build and attach a Duet date picker next to a Formidable date input.
   */
  function attachDuetToInput(input, settings) {
    if (!input || input.dataset.duetAttached === '1') return;
    if (input.classList.contains('frm_date_range')) return; // handled by attachDuetRangeToInput

    // If a Duet picker already exists right after this input, skip.
    if (input.nextElementSibling && input.nextElementSibling.tagName === 'DUET-DATE-PICKER') {
      input.dataset.duetAttached = '1';
      duetRegistry.inputToDuet.set(input, input.nextElementSibling);
      tryLinkSeparateRange(input);
      return;
    }

    // Build Duet element
    const duet = document.createElement('duet-date-picker');
    duet.className = 'frm-duet-picker';
    // Keep unique identifier for Duet (not reusing input id to avoid duplicate ids)
    duet.identifier = (input.id || 'frm_duet_date') + '__duet';

    // Map props: from dataset for Duet field, or from legacy settings if passed
    const props = Object.assign({}, getPropsFromDataset(input), mapConfigToDuetProps(settings || {}));
    // Force year-only format if requested
    if (props.yearOnly) {
      props.format = 'YYYY';
    }
    if (props.value) duet.value = props.value;
    if (props.min) duet.min = props.min;
    if (props.max) duet.max = props.max;
    if (typeof props.firstDayOfWeek === 'number') duet.firstDayOfWeek = props.firstDayOfWeek;
    if (typeof props.isDateDisabled === 'function') duet.isDateDisabled = props.isDateDisabled;

    // Deep localization (month/day names, labels) using Intl if possible.
    duet.localization = buildLocalization(props.locale, props.firstDayOfWeek);
    // If a custom format is provided, reflect it in the placeholder.
    if (props.format) {
      try {
        const firstFmt = String(props.format).split('|').map((s) => s.trim()).filter(Boolean)[0];
        if (firstFmt) {
          duet.localization = Object.assign({}, duet.localization, { placeholder: firstFmt });
        }
      } catch (e) {}
    }

    // Provide a dateAdapter to support custom parse/format on user input.
    duet.dateAdapter = buildDateAdapter(props.format || '');

    // Keep the original input for submission but hide it from interaction.
    input.classList.add('frm-duet-hidden');
    input.setAttribute('aria-hidden', 'true');
    input.setAttribute('tabindex', '-1');
    input.readOnly = true;
    // Hard-hide as a fallback if styles are blocked
    try {
      input.style.position = 'absolute';
      input.style.left = '-9999px';
      input.style.width = '1px';
      input.style.height = '1px';
      input.style.opacity = '0';
      input.style.pointerEvents = 'none';
    } catch (e) {}

    // Sync value from existing input if any
    if (!duet.value && input.value) {
      const val = normalizeDate(input.value);
      if (val) duet.value = val;
    }
    // Apply initial validity
    try { applyValidity(duet.value || ''); } catch (e) {}

    // Helper: range validity using ISO lexical compare
    function outOfRange(iso) {
      if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return false;
      const min = props.min || null;
      const max = props.max || null;
      if (min && iso < min) return true;
      if (max && iso > max) return true;
      return false;
    }

    function applyValidity(iso) {
      try {
        const invalid = outOfRange(iso);
        if (invalid) duet.setAttribute('aria-invalid', 'true');
        else duet.removeAttribute('aria-invalid');
      } catch (e) {}
    }

    // On change, copy full ISO value back to original input, update validity, and fire change events.
    duet.addEventListener('duetChange', function (e) {
      const val = (e && e.detail && e.detail.value) || '';
      applyValidity(val);
      if (input.value !== val) {
        input.value = val;
        triggerChange(input);
      }
    });

    // Insert after the input
    input.parentNode.insertBefore(duet, input.nextSibling);
    input.dataset.duetAttached = '1';

    // If year-only, hide/disable the calendar toggle once hydrated
    if (props.yearOnly) {
      (function disableToggleWhenReady(el) {
        let tries = 0;
        function attempt() {
          tries++;
          try {
            const btn = (el.shadowRoot && el.shadowRoot.querySelector('.duet-date__toggle')) || el.querySelector('.duet-date__toggle');
            if (btn) {
              btn.setAttribute('disabled', 'true');
              btn.setAttribute('aria-hidden', 'true');
              btn.tabIndex = -1;
              btn.style.display = 'none';
              return; // done
            }
          } catch (e) {}
          if (tries < 20) setTimeout(attempt, 100);
        }
        attempt();
        // Prevent any programmatic attempts to open the calendar
        el.addEventListener('duetOpen', function (ev) {
          try { ev.preventDefault(); } catch (e) {}
        });
      })(duet);
    }

    // Register for range linking between two separate fields
    duetRegistry.inputToDuet.set(input, duet);
    tryLinkSeparateRange(input);

    // Accessibility & label behavior: clicking the label should focus the Duet field.
    try {
      if (input.id) {
        const label = document.querySelector('label[for="' + CSS.escape(input.id) + '"]');
        if (label) {
          // Point the label to Duet's visible input id so default click focuses it
          if (duet.identifier) {
            label.setAttribute('for', duet.identifier);
          }
          // Remove any redundant aria-labelledby to avoid double announcement
          if (duet.hasAttribute('aria-labelledby')) {
            duet.removeAttribute('aria-labelledby');
          }
        }
      }

      // Some error/scroll plugins programmatically focus the original hidden input.
      // If that happens, forward focus to the Duet input instead.
      input.addEventListener('focus', function () {
        setTimeout(function () {
          try {
            if (typeof duet.setFocus === 'function') duet.setFocus();
            else duet.focus();
          } catch (e) {}
        }, 0);
      });
      // No additional forwarding needed; label 'for' now targets Duet input.
    } catch (e) {}
  }

  /**
   * Attach Duet range UI (start + end) for a single input with class frm_date_range.
   */
  // Single-input range mode is no longer used; Duet Date field uses two-field ranges.

  /** Build Duet localization object using Intl API. */
  function buildLocalization(locale, firstDay) {
    const l = (locale || 'en').toString();
    const fd = typeof firstDay === 'number' ? firstDay : (FrmDuetPickerCfg && FrmDuetPickerCfg.startOfWeek) || 1;
    try {
      const monthFmtLong = new Intl.DateTimeFormat(l, { month: 'long' });
      const monthFmtShort = new Intl.DateTimeFormat(l, { month: 'short' });
      const weekdayFmtLong = new Intl.DateTimeFormat(l, { weekday: 'long' });
      const weekdayFmtShort = new Intl.DateTimeFormat(l, { weekday: 'short' });

      const monthsLong = Array.from({ length: 12 }, (_, i) => monthFmtLong.format(new Date(2020, i, 1)));
      const monthsShort = Array.from({ length: 12 }, (_, i) => monthFmtShort.format(new Date(2020, i, 1)));
      // Weekdays from Sunday (0) to Saturday (6)
      const weekdaysLong = Array.from({ length: 7 }, (_, i) => weekdayFmtLong.format(new Date(2020, 5, 7 + i)));
      const weekdaysShort = Array.from({ length: 7 }, (_, i) => weekdayFmtShort.format(new Date(2020, 5, 7 + i)));

      return {
        locale: l,
        firstDayOfWeek: fd,
        monthNames: monthsLong,
        monthNamesShort: monthsShort,
        dayNames: weekdaysLong,
        dayNamesShort: weekdaysShort,
        weekLabel: 'Wk',
        buttonLabel: 'Choose date',
        placeholder: 'YYYY-MM-DD',
        selectedDateMessage: 'Selected date is',
        prevMonthLabel: 'Previous month',
        nextMonthLabel: 'Next month',
        monthSelectLabel: 'Month',
        yearSelectLabel: 'Year',
        closeLabel: 'Close',
        calendarHeading: 'Choose a date',
      };
    } catch (e) {
      // Provide safe minimal defaults Duet expects
      return {
        locale: l,
        firstDayOfWeek: fd,
        monthNames: [ 'January','February','March','April','May','June','July','August','September','October','November','December' ],
        monthNamesShort: [ 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec' ],
        dayNames: [ 'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday' ],
        dayNamesShort: [ 'Sun','Mon','Tue','Wed','Thu','Fri','Sat' ],
        weekLabel: 'Wk',
        buttonLabel: 'Choose date',
        placeholder: 'YYYY-MM-DD',
        selectedDateMessage: 'Selected date is',
        prevMonthLabel: 'Previous month',
        nextMonthLabel: 'Next month',
        monthSelectLabel: 'Month',
        yearSelectLabel: 'Year',
        closeLabel: 'Close',
        calendarHeading: 'Choose a date',
      };
    }
  }

  /**
   * Find Formidable date inputs and attach Duet using __frmDatepicker settings if available.
   */
  function initAll(container) {
    const root = container || document;

    // Duet Date custom field type: always attach
    root.querySelectorAll('input.frm_duet_date').forEach(function (input) {
      attachDuetToInput(input, null);
    });
  }

  /**
   * Stop Formidable's default datepicker init by canceling focusin in capture phase, then open Duet.
   */
  // No interception needed; we no longer alter core Date fields.

  /**
   * If this input belongs to a two-field range (separate Start/End fields), link constraints.
   */
  function tryLinkSeparateRange(input) {
    // Identify role and partner via data attributes from Formidable.
    const isEnd = typeof input.dataset.rangeStartFieldId !== 'undefined' && input.dataset.rangeStartFieldId !== '';
    const fieldId = input.dataset.fieldId || '';
    if (!fieldId && !isEnd) return;

    let startInput = null;
    let endInput = null;

    if (isEnd) {
      // This is the End field. Find its Start partner by data-field-id
      const startId = input.dataset.rangeStartFieldId;
      startInput = document.querySelector('input.frm_duet_date[data-field-id="' + CSS.escape(startId) + '"]');
      endInput = input;
    } else {
      // This is the Start field. Find its End partner by data-range-start-field-id
      startInput = input;
      const candidate = document.querySelector('input.frm_duet_date[data-range-start-field-id="' + CSS.escape(fieldId) + '"]');
      if (candidate) {
        endInput = candidate;
      }
    }

    if (!startInput || !endInput) return;

    const startDuet = duetRegistry.inputToDuet.get(startInput);
    const endDuet = duetRegistry.inputToDuet.get(endInput);
    if (!startDuet || !endDuet) {
      // Partner not attached yet; retry shortly.
      setTimeout(function () { tryLinkSeparateRange(input); }, 50);
      return;
    }

    if (startInput.dataset.duetLinked === '1' && endInput.dataset.duetLinked === '1') {
      return; // already linked
    }

    // Initialize constraints from current values
    applyTwoFieldConstraints(startInput, startDuet, endInput, endDuet);

    // Listen for changes and keep constraints synced both ways
    const syncFromStart = function () {
      applyTwoFieldConstraints(startInput, startDuet, endInput, endDuet);
    };
    const syncFromEnd = function () {
      applyTwoFieldConstraints(startInput, startDuet, endInput, endDuet);
    };
    startDuet.addEventListener('duetChange', syncFromStart);
    endDuet.addEventListener('duetChange', syncFromEnd);

    startInput.dataset.duetLinked = '1';
    endInput.dataset.duetLinked = '1';
  }

  function applyTwoFieldConstraints(startInput, startDuet, endInput, endDuet) {
    const startVal = startDuet.value || '';
    const endVal = endDuet.value || '';

    if (startVal) {
      endDuet.min = startVal;
      // If end < start, clear end to prompt re-selection
      if (endVal && endVal < startVal) {
        endDuet.value = '';
        endInput.value = '';
        triggerChange(endInput);
      }
    } else {
      // No start; remove min constraint on end
      endDuet.min = '';
    }

    if (endVal) {
      startDuet.max = endVal;
      if (startVal && startVal > endVal) {
        startDuet.value = '';
        startInput.value = '';
        triggerChange(startInput);
      }
    } else {
      startDuet.max = '';
    }
  }

  function observeDomChanges() {
    const observer = new MutationObserver(function (mutations) {
      for (const m of mutations) {
        if (m.type === 'childList' && (m.addedNodes && m.addedNodes.length)) {
          m.addedNodes.forEach(function (node) {
            if (!(node instanceof HTMLElement)) return;
            if (node.matches && (node.matches('input.frm_duet_date') || node.querySelector('input.frm_duet_date'))) {
              initAll(node);
            }
          });
        }
      }
    });
    observer.observe(document.documentElement || document.body, { childList: true, subtree: true });
  }

  function boot() {
    initAll(document);
    observeDomChanges();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
