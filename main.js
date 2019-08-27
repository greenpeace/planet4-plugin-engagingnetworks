"use strict";$(document).ready(function(){var e=$(".block-wide"),t=$("div.page-template, div.container").eq(0);function n(){var n=t.width();e.each(function(){var e=$(this).innerWidth(),t=(n-e)/2;"rtl"===$("html").attr("dir")?($(this).css("margin-left","auto"),$(this).css("margin-right",t+"px")):$(this).css("margin-left",t+"px")})}e.length>0&&t.length>0?(n(),$(window).on("resize",n)):($(".block-wide").attr("style","margin: 0px !important;padding-left: 0px !important;padding-right: 0px !important"),$("iframe").attr("style","left: 0"))}),jQuery(function(e){e(".campaign-climate #enform").length&&e(e(".form-description").prop("firstChild")).wrap('<span class="first-line">')}),$(document).ready(function(){var e=$("div.page-template, div.container").eq(0);e.length||(e=$("div.page-template").eq(0));var t=$(".enform-wrap.enform-side-style"),n=t.find(".enform");function a(){var a=$(window).width()>=992,i=$(window).width();if(a)n.css("margin-left",""),n.css("margin-right",""),n.css("padding-left",""),n.css("padding-right",""),t.find("picture img").css("height","100%");else{var r=(i-e.innerWidth())/2;"rtl"===$("html").attr("dir")?(n.css("margin-left","auto"),n.css("margin-right",-r+"px"),n.css("padding-right",r+"px"),n.css("padding-left",r+"px")):(n.css("margin-left",-r+"px"),n.css("padding-left",r+"px"),n.css("padding-right",r+"px"));var o=t.find(".form-caption").outerHeight();t.find("picture img").css("height",o+"px")}}t&&(a(),$(window).on("resize",a))});var p4_enform_frontend=function(e){var t={getFormData:function(){var t={questions:{}};return e.each(e(".en__field__input--checkbox:checked"),function(t,n){""===n.value&&e(this).val("Y")}),e.each(e('.en__field__input--checkbox:not(":checked")'),function(e,n){if(n.name.indexOf("supporter.questions.")>=0){var a=n.name.split(".")[2];t.questions["question."+a]="N"}}),e.each(e("#p4en_form").serializeArray(),function(e,n){if(n.name.indexOf("supporter.questions.")>=0){var a=n.name.split(".")[2];void 0===t.questions["question."+a]?t.questions["question."+a]=n.value:t.questions["question."+a]=t.questions["question."+a]+"~"+n.value}else n.name.indexOf("supporter.")>=0&&""!==n.value&&(t[n.name.replace("supporter.","")]=n.value)}),{standardFieldNames:!0,supporter:t}},addChangeListeners:function(n){e(n.elements).each(function(){e(this).off("change").on("change",function(){t.validateForm(n)})})},validateEmail:function(e){return/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(String(e).toLowerCase())},validateUrl:function(e){return/^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(e)},addErrorMessage:function(t,n){0===e(".invalid-feedback",e(t).parent()).length&&(void 0===n&&(n=e(t).data("errormessage")),e(t).addClass("is-invalid"),e('<div class="invalid-feedback">'+n+"</div>").appendTo(e(t).parent()).show())},removeErrorMessage:function(t){e(t).removeClass("is-invalid");var n=e(".invalid-feedback",e(t).parent());n.length&&e(n).remove()},validateForm:function(n){var a=!0;return e(n.elements).each(function(){t.removeErrorMessage(this);var n=e(this).val();(e(this).attr("required")&&"checkbox"!==e(this).attr("type")&&!n||e(this).attr("required")&&"checkbox"===e(this).attr("type")&&!1===e(this).prop("checked")||"email"===e(this).attr("type")&&!t.validateEmail(n))&&(t.addErrorMessage(this),a=!1);var i=e(this).attr("data-validate_regex");i&&(new RegExp(i).test(n)||(t.addErrorMessage(this,e(this).attr("data-validate_regex_msg")),a=!1));var r=e(this).attr("data-validate_callback");if("function"==typeof window[r]){var o=window[r](e(this).val());!0!==o&&(t.addErrorMessage(this,o),a=!1)}}),a},submitToEn:function(n,a){var i=e("#enform"),r=e("input[name=en_page_id]").val(),o="https://e-activist.com/ens/service/page/".concat(r,"/process");e.ajax({url:o,type:"POST",contentType:"application/json",crossDomain:!0,headers:{"ens-auth-token":a},data:JSON.stringify(n)}).done(function(){if("undefined"!=typeof google_tag_value&&google_tag_value){var n={event:"petitionSignup"},a=e("#enform_goal").val();a&&(n.gGoal=a),dataLayer.push(n)}var r=i.data("redirect-url");t.validateUrl(r)?window.location=r:(e("#enform-content").hide(),e(".thankyou").show()),e(".enform-notice").html("")}).fail(function(t){e(".enform-notice").html('<span class="enform-error">There was a problem with the submission</span>'),console.log(t)}).always(function(){t.hideENSpinner()})},showENSpinner:function(){e("#p4en_form_save_button").attr("disabled",!0),e(".en-spinner").show(),e(".enform-notice").html("")},hideENSpinner:function(){e("#p4en_form_save_button").attr("disabled",!1),e(".en-spinner").hide()}};return t}(jQuery);$(document).ready(function(){$("#p4en_form").submit(function(e){if(e.preventDefault(),p4_enform_frontend.addChangeListeners(this),p4_enform_frontend.validateForm(this)){var t=en_vars.ajaxurl;p4_enform_frontend.showENSpinner(),$.ajax({url:t,type:"POST",data:{action:"get_en_session_token",_wpnonce:$("#_wpnonce",$(this)).val()}}).done(function(e){var t=e.token;if(""!==t){var n=p4_enform_frontend.getFormData();p4_enform_frontend.submitToEn(n,t)}else p4_enform_frontend.hideENSpinner(),$(".enform-notice").html("There was a problem with the submission")}).fail(function(e){p4_enform_frontend.hideENSpinner(),$(".enform-notice").html("There was a problem with the submission"),console.log(e)})}})});
//# sourceMappingURL=main.js.map
