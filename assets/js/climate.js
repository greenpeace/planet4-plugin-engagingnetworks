jQuery(function ($) {
  'use strict';

  const $climate = $('.campaign-climate #enform');

  if ($climate.length) {
    // Adjust text height so that CTA is above the fold.
    const max = $(window).height() - $('#enform').offset().top; // - parseInt($('#enform').css('margin-top'));
    const current = $('#enform').outerHeight();
    const diff = current - max;
    const desc = $('.form-description').outerHeight() - diff;
    if (desc > 0) {
      $('.form-description').css('max-height', desc);
    } else {
      $('.form-description').css('max-height', 60);
    }

    // Adjust first line of the description
    $($('.form-description').prop('firstChild')).wrap('<span class="first-line">');
  }
});
