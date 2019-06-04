// Force wide blocks outside the container
$(document).ready(function() {
  'use strict';
  var $container = $('div.page-template, div.container').eq(0);

  if (!$container.length) {
    $container = $('div.page-template').eq(0);
  }

  var $sideStyleForm = $('.enform-wrap.enform-side-style');
  var $enform = $sideStyleForm.find('.enform');

  function setContainerWidth() {
    var isLarge = $(window).width() >= 992;
    var vw = $(window).width();

    if (!isLarge) {
      var margin = ((vw - $container.innerWidth()) / 2);

      if ($('html').attr('dir') === 'rtl') {
        $enform.css('margin-left', 'auto');
        $enform.css('margin-right', - margin + 'px');
        $enform.css('padding-right', margin + 'px');
        $enform.css('padding-left', margin + 'px');
      } else {
        $enform.css('margin-left', - margin + 'px');
        $enform.css('padding-left', margin + 'px');
        $enform.css('padding-right', margin + 'px');
      }

      var captionHeight = $sideStyleForm.find('.form-caption').outerHeight();
      $sideStyleForm.find('picture img').css('height', captionHeight + 'px');
    } else {
      $enform.css('margin-left', '');
      $enform.css('margin-right', '');
      $enform.css('padding-left', '');
      $enform.css('padding-right', '');
      $sideStyleForm.find('picture img').css('height', '100%');
    }
  }

  if ($sideStyleForm) {
    setContainerWidth();
    $(window).on('resize', setContainerWidth);
  }
});