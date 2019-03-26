$(document).ready(function(){"use strict";var e=$(".block-wide"),t=$("div.page-template, div.container").eq(0);function n(){var n=t.width();e.each(function(){var e=$(this).innerWidth(),t=(n-e)/2;"rtl"===$("html").attr("dir")?($(this).css("margin-left","auto"),$(this).css("margin-right",t+"px")):$(this).css("margin-left",t+"px")})}e.length>0&&t.length>0?(n(),$(window).on("resize",n)):($(".block-wide").attr("style","margin: 0px !important;padding-left: 0px !important;padding-right: 0px !important"),$("iframe").attr("style","left: 0"))}),$(document).ready(function(){"use strict";function e(e){var t=!0;return $(e.elements).each(function(){!function(e){$(e).removeClass("is-invalid");var t=$(e).next();t.length&&t.hasClass("invalid-feedback")&&$(t).remove()}(this);var e=$(this).val();($(this).attr("required")&&!e||"email"===$(this).attr("type")&&!/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(String(e).toLowerCase()))&&(!function(e){$(e).addClass("is-invalid");var t=$("<div>");t.addClass("invalid-feedback"),t.html($(e).data("errormessage")),t.insertAfter(e)}(this),t=!1)}),t}function t(){$("#p4en_form_save_button").attr("disabled",!1),$(".en-spinner").hide()}$("#p4en_form").submit(function(n){var a;if(n.preventDefault(),a=this,$(a.elements).each(function(){$(this).off("change keyup").on("change keyup",function(){e(a)})}),e(this)){const e=en_vars.ajaxurl;$("#p4en_form_save_button").attr("disabled",!0),$(".en-spinner").show(),$(".enform-notice").html(""),$.ajax({url:e,type:"POST",data:{action:"get_en_session_token",_wpnonce:$("#_wpnonce",$(this)).val()}}).done(function(e){var n=e.token;""!==n?(!function(e,n){const a=$("#enform");var u=`https://e-activist.com/ens/service/page/${$("input[name=en_page_id]").val()}/process`;$.ajax({url:u,type:"POST",contentType:"application/json",crossDomain:!0,headers:{"ens-auth-token":n},data:JSON.stringify(e)}).done(function(){var e=a.data("redirect-url");if(/^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(e))window.location=e;else{var t='<h2 class="thankyou"><span class="thankyou-title">'+$("input[name=thankyou_title]").val()+'</span><br /> <span class="thankyou-subtitle">'+$("input[name=thankyou_subtitle]").val()+"</span> </h2>";a.html(t)}$(".enform-notice").html("")}).fail(function(e){$(".enform-notice").html('<span class="enform-error">There was a problem with the submission</span>'),console.log(e)}).always(function(){t()})}(function(){let e={questions:{}};return $(".en__field__input--checkbox:checked").val("Y"),$.each($('.en__field__input--checkbox:not(":checked")'),function(t,n){if(n.name.indexOf("supporter.questions.")>=0){let t=n.name.split(".")[2];e.questions["question."+t]="N"}}),$.each($("#p4en_form").serializeArray(),function(t,n){if(n.name.indexOf("supporter.questions.")>=0){let t=n.name.split(".")[2];e.questions["question."+t]=n.value}else n.name.indexOf("supporter.")>=0&&""!=n.value&&(e[n.name.replace("supporter.","")]=n.value)}),{standardFieldNames:!0,supporter:e}}(),n),"undefined"!=typeof google_tag_value&&google_tag_value&&dataLayer.push({event:"petitionSignup"})):(t(),$(".enform-notice").html("There was a problem with the submission"))}).fail(function(e){t(),$(".enform-notice").html("There was a problem with the submission"),console.log(e)})}})});
//# sourceMappingURL=main.js.map
