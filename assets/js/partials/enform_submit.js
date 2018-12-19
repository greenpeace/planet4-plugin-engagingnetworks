/* global en_vars */
$(document).ready(function () {
  'use strict';

  $('#p4en_form').submit(function(e) {
    e.preventDefault();

    const url = en_vars.ajaxurl;
    const $content = $('#enform');
    let values = {};

    // Prepare the questions/optins values the way that ENS api expects them.
    $('.en__field__input--checkbox:checked').val('Y');
    $.each($('.en__field__input--checkbox:not(":checked")'), function(i, field) {
      if ( field.name.indexOf( 'supporter.questions.' ) >= 0 ) {
        let id = field.name.split('.')[2];
        values['supporter.question.' + id] = 'N';
      }
    });

    $.each($('#p4en_form').serializeArray(), function(i, field) {
      if ( field.name.indexOf( 'supporter.questions.' ) >= 0 ) {
        let id = field.name.split('.')[2];
        values['supporter.question.' + id] = field.value;
      } else {
        values[field.name] = field.value;
      }
    });

    $.ajax({
      url: url,
      type: 'POST',
      data: {
        action:       'handle_submit',
        '_wpnonce':   $( '#_wpnonce' ).val(),
        'en_page_id': $('input[name=en_page_id]').val(),
        values: values,
      },
    }).done(function ( response ) {
      var url = $content.data('redirect-url');

      if ( is_valid_url( url ) ) {
        window.location = url;
      } else {
        $content.html( response );
      }
    }).fail(function ( response ) {
      console.log(response); //eslint-disable-line no-console
    });
  });

  function is_valid_url(url) {
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);  //eslint-disable-line no-useless-escape
  }
});
