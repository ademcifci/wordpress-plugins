/* global jQuery, MutationObserver */
(function($){
  'use strict';

  function isBuilderPage() {
    return $(document.body).is('.formidable_page_formidable, .toplevel_page_formidable') || $(document).find('#frm_fields').length > 0 || $(document).find('.frm_form_builder').length > 0;
  }

  function nativeClick(el){
    if (!el) return;
    try {
      if (typeof el.click === 'function') { el.click(); return; }
      var evt = new MouseEvent('click', { bubbles: true, cancelable: true, view: window });
      el.dispatchEvent(evt);
    } catch (e) {
      try { $(el).trigger('click'); } catch (e2) {}
    }
  }

  function collapseOneDivider($li) {
    if (!$li || !$li.length) return;
    if ($li.data('fbcsInitDone')) return;

    var $toggle = $li.find('a.frm-collapse-section').first();
    var $ul = $li.find('> ul.start_divider').first();

    // Use native Formidable toggle: if children are visible, trigger one click to collapse.
    if ($toggle.length && $ul.length && $ul.is(':visible')) {
      nativeClick($toggle.get(0));
    }

    $li.data('fbcsInitDone', true);
  }

  function collapseAllDividers(context) {
    var $ctx = context ? $(context) : $(document);
    $ctx.find('li[data-type="divider"]').each(function(){ collapseOneDivider($(this)); });
  }

  function observeBuilder() {
    var target = document.querySelector('#frm_fields') || document.querySelector('.frm_form_builder') || document.body;
    if (!target || typeof MutationObserver === 'undefined') return;
    var obs = new MutationObserver(function(){ collapseAllDividers(target); });
    try { obs.observe(target, { childList: true, subtree: true }); } catch(e) {}
  }

  $(function(){
    if (!isBuilderPage()) return;
    // Initial pass – collapse visible sections using native toggle (defer to ensure handlers bound)
    setTimeout(function(){ collapseAllDividers(document); }, 0);
    setTimeout(function(){ collapseAllDividers(document); }, 200);
    // Watch for changes (adding/removing/moving fields)
    observeBuilder();
    // Also run after ajax updates in the admin if available
    $(document).on('ajaxComplete', function(){ collapseAllDividers(document); });
  });
})(jQuery);
