/* Amorino — fiche produit fidèle à la maquette :
   options en tuiles avec prix, mention « Obligatoire », total dans le bouton,
   parfums en pastilles reliées au champ « Vos parfums ». */
jQuery(function ($) {
  function euros(n) {
    return (Math.round(n * 100) / 100).toFixed(2).replace('.', ',') + ' €';
  }

  $('form.variations_form').each(function () {
    var $form = $(this);
    var variations = $form.data('product_variations') || [];
    var $btn = $form.find('.single_add_to_cart_button');
    var baseLabel = $.trim($btn.text()) || 'Ajouter au panier';
    var currentPrice = null;

    $form.find('table.variations select').each(function () {
      var $sel = $(this);
      if ($sel.data('amTiles')) return;
      $sel.data('amTiles', true);
      var attrName = $sel.attr('name');                // ex. attribute_pa_taille
      var single = $form.find('table.variations select').length === 1;
      var $label = $sel.closest('tr').find('th label');
      var labelText = $.trim($label.text()) || 'Options';
      if (!$label.find('.am-required').length) {
        $label.append(' <span class="am-required">Obligatoire</span>');
      }
      var $wrap = $('<div class="am-tiles" role="group"></div>').attr('aria-label', labelText);

      $sel.find('option').each(function () {
        if (!this.value) return;
        var value = this.value, price = '';
        if (single) {
          $.each(variations, function (_, v) {
            if (v.attributes && v.attributes[attrName] === value) { price = euros(v.display_price); }
          });
        }
        $('<button type="button" class="am-tile" aria-pressed="false"></button>')
          .attr('data-value', value)
          .append($('<span class="am-tile__name"></span>').text($(this).text()))
          .append(price ? $('<span class="am-tile__price"></span>').text(price) : '')
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
        var $sel = $(this), val = $sel.val() || '', avail = {};
        $sel.find('option').each(function () {
          if (this.value && !this.disabled) avail[this.value] = true;
        });
        $sel.next('.am-tiles').find('.am-tile').each(function () {
          var v = $(this).attr('data-value');
          $(this).attr('aria-pressed', v === val ? 'true' : 'false').prop('disabled', !avail[v]);
        });
      });
    }

    function updateButton() {
      var qty = parseInt($form.find('input.qty').val(), 10) || 1;
      $btn.text(currentPrice === null ? baseLabel : baseLabel + ' · ' + euros(currentPrice * qty));
    }

    $form.on('found_variation', function (e, v) {
      currentPrice = v && v.is_purchasable && v.is_in_stock ? v.display_price : null;
      updateButton();
    });
    $form.on('reset_data hide_variation', function () { currentPrice = null; updateButton(); });
    $form.on('change input', 'input.qty', updateButton);
    $form.on('woocommerce_update_variation_values change reset_data found_variation', function () {
      setTimeout(sync, 0);
    });
    sync();
  });

  /* Parfums : pastilles ⇄ champ texte */
  $('.amorino-flavours').each(function () {
    var $box = $(this), $input = $box.find('#amorino_parfums');
    function list() {
      return $.grep($.map(($input.val() || '').split(','), $.trim), function (s) { return s !== ''; });
    }
    function paint() {
      var chosen = list();
      $box.find('.am-chip').each(function () {
        $(this).attr('aria-pressed', chosen.indexOf($(this).attr('data-flavour')) > -1 ? 'true' : 'false');
      });
    }
    $box.on('click', '.am-chip', function () {
      var name = $(this).attr('data-flavour'), chosen = list(), i = chosen.indexOf(name);
      if (i > -1) { chosen.splice(i, 1); } else { chosen.push(name); }
      $input.val(chosen.join(', ')).trigger('change');
      paint();
    });
    $input.on('input change', paint);
    paint();
  });
});
