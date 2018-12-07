/* global en_vars */
$(document).ready(function () {
  'use strict';

  $('#p4en_form').submit(function(e) {
    e.preventDefault();

    const url = en_vars.ajaxurl;
    const $content = $('#enform');
    let values = {};
    $.each($('#p4en_form').serializeArray(), function(i, field) {
      values[field.name] = field.value;
    });

    $.ajax({
      url: url,
      type: 'POST',
      data: {
        action:     'handle_submit',
        '_wpnonce': $( '#_wpnonce' ).val(),
        'en_page_id': $('input[name=en_page_id]').val(),
        values: values,
      },
    }).done(function ( response ) {
      if ('undefined' !== $content.data('redirect-url')) {
        var url = $content.data('redirect-url');
        if ('' !== url && 'false' !== url) {
          window.location = url;
        }
      } else {
        $content.html( response );
      }
    }).fail(function ( response ) {
      console.log(response); //eslint-disable-line no-console
    });
  });
});
