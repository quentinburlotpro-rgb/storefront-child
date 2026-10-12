/* Amorino — sélecteur d'options en tuiles (remplace visuellement les <select> WooCommerce). */
jQuery(function ($) {
  $('form.variations_form').each(function () {
    var $form = $(this);

    $form.find('table.variations select').each(function () {
      var $sel = $(this);
      if ($sel.data('amTiles')) return;
      $sel.data('amTiles', true);
      var label = $sel.closest('tr').find('th label').text() || 'Options';
      var $wrap = $('<div class="am-tiles" role="group"></div>').attr('aria-label', label);
      $sel.find('option').each(function () {
        if (!this.value) return;
        $('<button type="button" class="am-tile" aria-pressed="false"></button>')
          .text($(this).text())
          .attr('data-value', this.value)
          .appendTo($wrap);
      });
      $sel.addClass('am-hidden-select').attr({ 'aria-hidden': 'true', tabindex: '-1' }).after($wrap);
      $wrap.on('click', '.am-tile', function () {
        if (this.disabled) return;
        var v = $(this).attr('data-value');
        $sel.val($sel.val() === v ? '' : v).trigger('change');
      });
    });

    function sync() {
      $form.find('table.variations select').each(function () {
        var $sel = $(this), val = $sel.val() || '';
        var avail = {};
        $sel.find('option').each(function () {
          if (this.value && !this.disabled) avail[this.value] = true;
        });
        $sel.next('.am-tiles').find('.am-tile').each(function () {
          var v = $(this).attr('data-value');
          $(this).attr('aria-pressed', v === val ? 'true' : 'false').prop('disabled', !avail[v]);
        });
      });
    }
    $form.on('woocommerce_update_variation_values change reset_data found_variation', function () {
      setTimeout(sync, 0);
    });
    sync();
  });
});
