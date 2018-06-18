$ = jQuery;

$(document).ready(function () {
  $('.notice.is-dismissible').animate({"margin-left": '+=20', "opacity": '+=0.9'}, 800);
  $('.p4en_message').animate({"opacity": '+=0.9'}, 800);

  setTimeout(function () {
    $('.notice.is-dismissible, .p4en_message').fadeOut(2000, function () {
      $(this).remove();
    });
  }, 3800);

  $('.do_copy').off('click').on('click', function (e) {
    e.preventDefault();

    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(this).attr('data-href')).select();
    document.execCommand("copy");
    $temp.remove();
  });

  $('.do_scrape_form').off('click').on('click', function (e) {
    e.preventDefault();

    var _this = this;
    $.ajax({
      contentType: 'application/json',
      data: JSON.stringify({en_url: $(_this).data('href')}),
      method: 'POST',
      url: p4en.rest_url + 'form/get_fields',
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', p4en.nonce);
      },
      success: function (response) {
        console.log(response);
      },
      error: function (error_response) {
      }
    });
    return false;
  });
});
