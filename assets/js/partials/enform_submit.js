/* global en_vars */
$(document).ready(function () {
  'use strict';

  function addChangeListeners(form) {
    $(form.elements).each(function() {
      $(this).off('change').on('change', function() {
        validateForm(form);
      });
    });
  }

  function validateEmail(email) {
    // Reference: https://stackoverflow.com/questions/46155/how-to-validate-an-email-address-in-javascript
    var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  }

  function addErrorMessage(element) {
    $(element).addClass('is-invalid');
    var $invalidDiv = $('<div>');
    $invalidDiv.addClass('invalid-feedback');
    $invalidDiv.html($(element).data('errormessage'));
    $invalidDiv.insertAfter(element);
  }

  function removeErrorMessage(element) {
    $(element).removeClass('is-invalid');
    var errorDiv = $(element).next();
    if (errorDiv.length && errorDiv.hasClass('invalid-feedback')) {
      $(errorDiv).remove();
    }
  }

  function validateForm(form) {
    var formIsValid = true;

    $(form.elements).each(function() {
      removeErrorMessage(this);
      var formValue = $(this).val();

      if ($(this).attr('required') && !formValue) {
        addErrorMessage(this);

        formIsValid = false;
      } else if ('email' === $(this).attr('type')) {
        var emailValid = validateEmail(formValue);
        formIsValid = formIsValid === true ? emailValid : false;

        if (!emailValid) {
          addErrorMessage(this);
        }
      }
    });

    return formIsValid;
  }

  $('#p4en_form').submit(function(e) {
    e.preventDefault();

    // Don't bug users with validation before the first submit
    addChangeListeners(this);

    const $content = $('#enform');
    if (validateForm(this)) {
      const url = en_vars.ajaxurl;
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
          action:     'handle_submit',
          '_wpnonce': $( '#_wpnonce' ).val(),
          'en_page_id': $('input[name=en_page_id]').val(),
          values: values,
        },
      }).done(function ( response ) {
        var redirectURL = $content.data('redirect-url');

        if ( is_valid_url( redirectURL ) ) {
          window.location = redirectURL;
        } else {
          $content.html( response );
        }
      }).fail(function ( response ) {
        console.log(response); //eslint-disable-line no-console
      });
    }
  });

  function is_valid_url(url) {
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);  //eslint-disable-line no-useless-escape
  }
});
