Formidable Duet Date Picker
===========================

Standalone plugin that replaces Formidable Forms date fields with the Duet Date Picker web component, without modifying Formidable core.

How it works
- Adds a "Duet Date Picker" checkbox under Formidable > Global Settings. Nothing runs until enabled.
- Uses Duet CDN assets (ESM + nomodule) to ensure all internal imports resolve.
- Intercepts Formidable default Flatpickr/jQuery init only for supported fields once enabled.
- Uses the `__frmDatepicker` configuration to map min/max, default date, first-day-of-week, disabled dates.
- Deep localization: builds month/day names from `Intl.DateTimeFormat` based on the field locale (fallbacks to site lang).
- Keeps the original input for submission; Duet updates it on change and triggers change events.

Scope and current limitations
- Single date fields: supported.
- Date ranges:
  - Single-input ranges (`.frm_date_range`): supported via two Duet pickers (start/end) that sync back to the single input as `YYYY-MM-DD to YYYY-MM-DD`.
  - Two-field ranges (separate "Start Date"/"End Date" fields): each field uses Duet individually; hard constraints between them can be added in a follow-up if needed.
- Inline datepickers: currently render as dropdown calendars; inline always-visible calendar can be added if required.

Install
1. Copy `formidable-duet-datepicker` into `wp-content/plugins`.
2. Activate "Formidable Duet Date Picker".
3. Go to Formidable > Global Settings and check "Duet Date Picker".
4. Date fields will use Duet on the front end and admin entries screens.

Opt-out
- Disable via the Global Settings checkbox. If you need per-field opt-out, we can add a data-attribute based skip on request.

Notes
- This plugin does not touch Formidable core. It relies on DOM interception and Formidable’s exported `__frmDatepicker` config to mirror behaviour.
