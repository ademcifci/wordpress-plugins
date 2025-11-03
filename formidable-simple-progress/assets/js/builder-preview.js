(function($){
  function findBreakBoxes($preview){
    var $builder = $preview.closest('.frm_form_builder, .frm_wrap');
    if(!$builder.length){ return $(); }
    // Use only the explicit page break field wrapper to avoid counting UI decorations.
    var $boxes = $builder.find('.edit_field_type_break:visible');
    return $boxes;
  }

  function computeNumbers($preview){
    var $breakBoxes = findBreakBoxes($preview);
    if(!$breakBoxes.length){ return {cur:1,total:1}; }

    var total = $breakBoxes.length + 1;

    // Current page = 1 + number of break boxes above this field box.
    var cur = 1;
    var $myBox = $preview.closest('[data-fid], .frm_field_box');
    var myTop = ($myBox.offset()||{top:0}).top;
    $breakBoxes.each(function(){ if (($(this).offset()||{top:0}).top < myTop) { cur++; } });

    return {cur:cur,total:total};
  }

  function getStepLabel(fieldId, $preview){
    var $in = $('#frm_sp_step_label_' + fieldId);
    var label = $.trim($in.val());
    if(!label){ label = $in.attr('placeholder') || $preview.data('default-step') || 'Step'; }
    return label;
  }

  function render($preview){
    var fieldId = $preview.data('field-id');
    var nums = computeNumbers($preview);
    var ofLabel = $preview.data('of-label') || 'of';
    var text = getStepLabel(fieldId, $preview) + ' ' + nums.cur + ' ' + ofLabel + ' ' + nums.total;
    var $badge = $preview.find('.frm-simple-progress-badge');
    if($badge.text() !== text){ $badge.text(text); }
  }

  function renderAll(){ $('.frm-sp-preview').each(function(){ render($(this)); }); }

  // Initial and input-driven updates.
  $(document).on('input change', '[id^=frm_sp_step_label_]', function(){
    var id = this.id.replace('frm_sp_step_label_', '');
    var $preview = $('#frm-sp-preview-' + id);
    if($preview.length){ render($preview); }
  });

  // Observe builder changes.
  $(function(){
    renderAll();
    var $root = $('.frm_form_builder, .frm_wrap').first();
    if(!$root.length || !window.MutationObserver){ return; }
    try{
      var rafId = null;
      var obs = new MutationObserver(function(){
        if(rafId !== null){ return; }
        rafId = (window.requestAnimationFrame||setTimeout)(function(){ rafId=null; renderAll(); }, 16);
      });
      obs.observe($root[0], {childList:true,subtree:true});
    }catch(e){}
  });
})(jQuery);
