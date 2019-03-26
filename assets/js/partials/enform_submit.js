/* global en_vars, google_tag_value, dataLayer */
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

  function validateUrl(url) {
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);  //eslint-disable-line no-useless-escape
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

      if (
        $(this).attr('required') && ! formValue ||
        'email' === $(this).attr('type') && ! validateEmail( formValue )
      ) {
        addErrorMessage(this);
        formIsValid = false;
      }
    });

    return formIsValid;
  }

  // Submit to a en page process api endpoint.
  function submitToEn(formData, sessionToken) {
    const form = $('#enform');
    var en_page_id = $('input[name=en_page_id]').val(),
      uri = `https://e-activist.com/ens/service/page/${en_page_id}/process`;
    $.ajax({
      url: uri,
      type: 'POST',
      contentType: 'application/json',
      crossDomain: true,
      headers: {
        'ens-auth-token': sessionToken
      },
      data: JSON.stringify(formData),
    }).done(function () {
      var redirectURL = form.data('redirect-url');

      if (validateUrl(redirectURL)) {
        window.location = redirectURL;
      } else {
        var s =
          '<h2 class="thankyou">' +
          '<span class="thankyou-title">'+ $('input[name=thankyou_title]').val() +'</span><br />' +
          ' <span class="thankyou-subtitle">'+  $('input[name=thankyou_subtitle]').val() +'</span> ' +
          '</h2>';

        form.html(s);
      }
      $('.enform-notice').html('');
    }).fail(function (response) {
      $('.enform-notice').html('<span class="enform-error">There was a problem with the submission</span>');
      console.log(response); //eslint-disable-line no-console
    }).always(function() {
      hideENSpinner();
    });
  }

  // Get enform fields data and prepare them for submission to en api.
  function getFormData() {
    let supporter = {
      questions: {}
    };

    // Prepare the questions/optins values the way that ENS api expects them.
    $('.en__field__input--checkbox:checked').val('Y');
    $.each($('.en__field__input--checkbox:not(":checked")'), function (i, field) {
      if (field.name.indexOf('supporter.questions.') >= 0) {
        let id = field.name.split('.')[2];
        supporter.questions['question.' + id] = 'N';
      }
    });

    $.each($('#p4en_form').serializeArray(), function (i, field) {
      if (field.name.indexOf('supporter.questions.') >= 0) {
        let id = field.name.split('.')[2];
        supporter.questions['question.' + id] = field.value;
      } else if (field.name.indexOf('supporter.') >= 0 && '' != field.value) {
        supporter[field.name.replace('supporter.', '')] = field.value;
      }
    });

    var requestBody = {
      standardFieldNames: true,
      supporter: supporter
    };
    return requestBody;
  }

  function showENSpinner() {
    $('#p4en_form_save_button').attr('disabled', true);
    $('.en-spinner').show();
    $('.enform-notice').html('');

  }
  function hideENSpinner() {
    $('#p4en_form_save_button').attr('disabled', false);
    $('.en-spinner').hide();
  }

  // Submit handler for enform
  $('#p4en_form').submit(function (e) {
    e.preventDefault();

    // Don't bug users with validation before the first submit
    addChangeListeners(this);

    if (validateForm(this)) {
      const url = en_vars.ajaxurl;

      showENSpinner();
      $.ajax({
        url: url,
        type: 'POST',
        data: {
          action: 'get_en_session_token',
          '_wpnonce': $('#_wpnonce', $(this)).val(),
        },
      }).done(function (response) {
        var token = response.token;

        if ('' !== token) {
          var values = getFormData();
          submitToEn(values, token);

          // DataLayer push event on EN form submission.
          if ( typeof google_tag_value !== 'undefined' && google_tag_value ) {
            dataLayer.push({
              'event' : 'petitionSignup'
            });
          }
        } else {
          hideENSpinner();
          $('.enform-notice').html('There was a problem with the submission');
        }
      }).fail(function (response) {
        hideENSpinner();
        $('.enform-notice').html('There was a problem with the submission');
        console.log(response); //eslint-disable-line no-console
      });
    }
  });

  // DataLayer push value on EN form page load.
  if ( typeof google_tag_value !== 'undefined' && google_tag_value ) {
    dataLayer.push({
      'gCampaign' : $('#campaign_name').val(),
      'gBasket' : $('#basket_name').val(),
      'gScope' : $('#scope').val(),
      'gDepartment' : $('#department').val()
    });
  }
});
