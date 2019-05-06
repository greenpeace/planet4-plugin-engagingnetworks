// Force wide blocks outside the container
$(document).ready(function() {
  'use strict';
  var $container = $('div.page-template, div.container').eq(0);

  if (!$container.length) {
    $container = $('div.page-template').eq(0);
  }

  var $enform = $('.enform-wrap.enform-side-style');

  function setContainerWidth() {
    var isLarge = $(window).width() >= 992;

    if (isLarge) {
      $container.find('h1').css('width', '50%');
      $container.find('h3').css('width', '50%');

      $enform.css('box-sizing', 'border-box')
        .css('position', 'absolute')
        .css('z-index', '4')
        .css('top', '80px');

      if ($('html').attr('dir') === 'rtl') {
        var rightOffset = $container.offset().left - $container.width();
        $enform.css('left', rightOffset + 'px');
        $enform.css('right', '');
      } else {
        var leftOffset = $container.offset().left;
        $enform.css('right', leftOffset + 'px');
        $enform.css('left', '');
      }

      $container.css('min-height', $enform.height() + 'px');
    } else {
      $container.find('h1').css('width', '');
      $container.find('h3').css('width', '');
      $container.find('.row').css('width', '');
      $container.css('min-height', '');
      $enform.css('box-sizing', 'border-box')
        .css('position', '')
        .css('top', '')
        .css('right', '')
        .css('left', '')
        .css('height', '');
    }
  }

  if ($enform) {
    setContainerWidth();
    $(window).on('resize', setContainerWidth);
  }
});