jQuery(document).ready(function() {
    $('.notice.is-dismissible').animate({"margin-left" : '+=20', "opacity" : '+=0.9'}, 800);
    setTimeout(function() {
        $('.notice.is-dismissible').fadeOut(2000, function () {
            $(this).remove();
        });
    }, 3200);

    $('.do_copy').off('click').on('click', function(e){
        e.preventDefault();

        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val( $(this).attr('data-href') ).select();
        document.execCommand("copy");
        $temp.remove();
    });
} );