$(document).ready(function(){"use strict";var e=$(".block-wide"),n=$("div.page-template, div.container").eq(0);function t(){var t=n.width();e.each(function(){var e=$(this).innerWidth(),n=(t-e)/2;"rtl"===$("html").attr("dir")?($(this).css("margin-left","auto"),$(this).css("margin-right",n+"px")):$(this).css("margin-left",n+"px")})}e.length>0&&n.length>0?(t(),$(window).on("resize",t)):($(".block-wide").attr("style","margin: 0px !important;padding-left: 0px !important;padding-right: 0px !important"),$("iframe").attr("style","left: 0"))}),$(document).ready(function(){"use strict";var e=$("div.page-template, div.container").eq(0);e.length||(e=$("div.page-template").eq(0));var n=$(".enform-wrap.enform-side-style"),t=n.find(".enform");function a(){var a=$(window).width()>=992,i=$(window).width();if(a)t.css("margin-left",""),t.css("margin-right",""),t.css("padding-left",""),t.css("padding-right",""),n.find("picture img").css("height","100%");else{var r=(i-e.innerWidth())/2;"rtl"===$("html").attr("dir")?(t.css("margin-left","auto"),t.css("margin-right",-r+"px"),t.css("padding-right",r+"px"),t.css("padding-left",r+"px")):(t.css("margin-left",-r+"px"),t.css("padding-left",r+"px"),t.css("padding-right",r+"px"));var s=n.find(".form-caption").outerHeight();n.find("picture img").css("height",s+"px")}}n&&(a(),$(window).on("resize",a))});var p4_enform_frontend=function(e){var n={getFormData:function(){let n={questions:{}};return e(".en__field__input--checkbox:checked").val("Y"),e.each(e('.en__field__input--checkbox:not(":checked")'),function(e,t){if(t.name.indexOf("supporter.questions.")>=0){let e=t.name.split(".")[2];n.questions["question."+e]="N"}}),e.each(e("#p4en_form").serializeArray(),function(e,t){if(t.name.indexOf("supporter.questions.")>=0){let e=t.name.split(".")[2];n.questions["question."+e]=t.value}else t.name.indexOf("supporter.")>=0&&""!==t.value&&(n[t.name.replace("supporter.","")]=t.value)}),{standardFieldNames:!0,supporter:n}},addChangeListeners:function(t){e(t.elements).each(function(){e(this).off("change").on("change",function(){n.validateForm(t)})})},validateEmail:function(e){return/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(String(e).toLowerCase())},validateUrl:function(e){return/^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(e)},addErrorMessage:function(n,t){void 0===t&&(t=e(n).data("errormessage")),e(n).addClass("is-invalid");var a=e("<div>");a.addClass("invalid-feedback"),a.html(t),a.insertAfter(n)},removeErrorMessage:function(n){e(n).removeClass("is-invalid");var t=e(n).next();t.length&&t.hasClass("invalid-feedback")&&e(t).remove()},validateForm:function(t){var a=!0;return e(t.elements).each(function(){n.removeErrorMessage(this);var t=e(this).val();(e(this).attr("required")&&!t||"email"===e(this).attr("type")&&!n.validateEmail(t))&&(n.addErrorMessage(this),a=!1);var i=e(this).attr("validate_callback");if("function"==typeof window[i]){var r=window[i](e(this).val());!0!==r&&n.addErrorMessage(this,r)}}),a},submitToEn:function(t,a){const i=e("#enform");var r=`https://e-activist.com/ens/service/page/${e("input[name=en_page_id]").val()}/process`;e.ajax({url:r,type:"POST",contentType:"application/json",crossDomain:!0,headers:{"ens-auth-token":a},data:JSON.stringify(t)}).done(function(){var t=i.data("redirect-url");if(n.validateUrl(t))window.location=t;else{var a='<h2 class="thankyou"><span class="thankyou-title">'+e("input[name=thankyou_title]").val()+'</span><br /> <span class="thankyou-subtitle">'+e("input[name=thankyou_subtitle]").val()+"</span> </h2>";i.html(a)}e(".enform-notice").html("")}).fail(function(n){e(".enform-notice").html('<span class="enform-error">There was a problem with the submission</span>'),console.log(n)}).always(function(){n.hideENSpinner()})},showENSpinner:function(){e("#p4en_form_save_button").attr("disabled",!0),e(".en-spinner").show(),e(".enform-notice").html("")},hideENSpinner:function(){e("#p4en_form_save_button").attr("disabled",!1),e(".en-spinner").hide()}};return n}(jQuery);$(document).ready(function(){"use strict";$("#p4en_form").submit(function(e){if(e.preventDefault(),p4_enform_frontend.addChangeListeners(this),p4_enform_frontend.validateForm(this)){const e=en_vars.ajaxurl;p4_enform_frontend.showENSpinner(),$.ajax({url:e,type:"POST",data:{action:"get_en_session_token",_wpnonce:$("#_wpnonce",$(this)).val()}}).done(function(e){var n=e.token;if(""!==n){var t=p4_enform_frontend.getFormData();if(p4_enform_frontend.submitToEn(t,n),"undefined"!=typeof google_tag_value&&google_tag_value){var a={event:"petitionSignup"},i=$("#enform_goal").val();i&&(a.gGoal=i),dataLayer.push(a)}}else p4_enform_frontend.hideENSpinner(),$(".enform-notice").html("There was a problem with the submission")}).fail(function(e){p4_enform_frontend.hideENSpinner(),$(".enform-notice").html("There was a problem with the submission"),console.log(e)})}})});
//# sourceMappingURL=main.js.map
