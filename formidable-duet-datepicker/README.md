Formidable Duet Date Picker
===========================

Adds a new Formidable field type “Duet Date” powered by the Duet Design System date picker. It does not modify Formidable core; assets load only when a Duet Date field is present.

Overview
- New builder field type: “Duet Date”.
- Original input is kept (hidden) for submission; Duet syncs its value and triggers change events.
- Uses Duet CDN assets (module + nomodule) and builds localization via the browser `Intl` APIs.
- Works on initial load and on AJAX‑inserted forms (MutationObserver).

Features
- Min/Max dates: accepts ISO `YYYY-MM-DD` or relative offsets like `+3days`, `+2months`, `-1year`.
- Disabled dates: one per line in ISO (`YYYY-MM-DD`).
- Disabled weekdays: checkboxes for Sun..Sat.
- Locale and first day of week.
- Custom input/display formats per field: tokens `YYYY`, `MM`, `DD` (separators such as `-`, `/`, `.` allowed). Multiple formats can be allowed by separating with `|`.
- Year‑only UI option: shows placeholder `YYYY` and hides the calendar button (typing only). The stored value remains full ISO (mapped to `YYYY-01-01`).
- Two‑field ranges: link an End field to a Start field (client‑side min/max linking).
- Accessibility: label clicks focus the visible Duet input; programmatic focus on the hidden input is forwarded to Duet for error links.

Field Options (Builder)
- Minimum Date: `YYYY-MM-DD` or relative offset (example: `+1year`).
- Maximum Date: `YYYY-MM-DD` or relative offset.
- Locale: e.g., `en`, `fr`, `de`.
- Display/Input Format: e.g., `DD-MM-YYYY`, `MM/DD/YYYY`, or multi‑format like `DD-MM-YYYY|YYYY`.
- Year only: shows `YYYY`, disables the calendar button; stores as `YYYY-01-01`.
- Disabled Days of the Week: checkboxes 0–6.
- Disabled Dates (one per line): ISO `YYYY-MM-DD`.

Validation & Storage
- Storage: always ISO `YYYY-MM-DD` for compatibility with Formidable and calculations.
- Server‑side validation: enforces ISO format, valid calendar dates, and Min/Max (including relative offsets). Out‑of‑range submissions are rejected with the field’s “invalid” message.
- Client‑side: Duet value is checked against Min/Max and `aria-invalid` is set when out of range (theme‑dependent styling).

Compatibility
- AJAX forms: supported (listens for added nodes and initializes automatically).
- Label focus and error links: label `for` points to the visible Duet input; any programmatic focus on the hidden input is forwarded to Duet.

Known Limitations
- Single‑input date ranges (one input with “start to end”) are not implemented; use two fields (Start + End).
- No custom error text for “before min/after max” — the generic invalid message is used.
- Advanced recurring disable rules (e.g., “every 2nd Tuesday”) are not implemented; use explicit dates or weekday disabling.
- Date‑only: no time picker mode.
- Server‑side cross‑field validation for Start/End is not enforced; only the End field receives dynamic client‑side linking to Start. A server rule can be added if required.

Install
1. Copy `formidable-duet-datepicker` into `wp-content/plugins`.
2. Activate “Formidable Duet Date Picker”.
3. Add the “Duet Date” field to your form and configure options as needed.

Notes
- This plugin avoids editing Formidable core. All integration happens by registering a new field type and attaching Duet next to its input.
