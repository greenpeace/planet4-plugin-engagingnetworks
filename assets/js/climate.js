jQuery(function ($) {
  'use strict';

  const $climate = $('.campaign-climate #enform');

  if ($climate.length) {
    // Adjust first line of the description
    $($('.form-description').prop('firstChild')).wrap('<span class="first-line">');
  }
});
