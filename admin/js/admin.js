jQuery(document).ready(function() {

    $('#setting-error-settings_updated').animate({"margin-left" : '+=20', "opacity" : '+=0.9'}, 800);
    $('#p4en_message_text').animate({"opacity" : '+=0.9'}, 800);
    
    setTimeout(function() {
        $('#setting-error-settings_updated, #p4en_message_text').fadeOut(2000, function () {
            $(this).remove();
        });
    }, 3600);

    $('.do_copy').off('click').on('click', function(e){
        e.preventDefault();

        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val( $(this).attr('data-href') ).select();
        document.execCommand("copy");
        $temp.remove();
    });
} );