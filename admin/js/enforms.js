/* global jQuery */

jQuery(function ($) {

  /**
   * Event listener for remove field/question button.
   */
  $('.remove-en-field').live('click', function (e) {
    e.preventDefault();
    var id = $(this).closest('tr').data('en-id');
    $(this).closest('tr').remove();
    $('.add-en-field').filter('*[data-id="' + id + '"]').prop('disabled', false);
  });

  /**
   * Event listener for add field/question button.
   */
  $('.add-en-field').off('click').on('click', function (e) {
    e.preventDefault();

    $(this).prop('disabled', true);
    var id = $(this).data('id');
    var name = $(this).data('name');
    var type = $(this).data('type');

    var selected_fields_tmpl = _.template($('#tmpl-en-selected-fields').html());
    var selected_field_tmpl = _.template($('#tmpl-en-selected-field').html());

    var html = selected_fields_tmpl({
      items: [{
        name: name,
        type: type,
        enid: id,
      }],
      row_template: selected_field_tmpl
    });
    $('#en_form_selected_fields_table > tbody').append(html);
  });

  /**
   * Make form selected fields table sortable.
   */
  $('#en_form_selected_fields_table > tbody').sortable({
    handle: '.dashicons-sort',
  });

  /**
   * Register event listener for field type select box.
   */
  $('body').on('change', '.field-type-select', function (e) {
    var value = $(this).val();
    var $tr = $(this).closest('tr');
    var name = $tr.data('en-name');

    if ('text' === value) {

      dialog_tmpl = _.template($('#tmpl-en-field-dialog').html());
      $(this).parent().next().prepend(dialog_tmpl());
      $(this).parent().next().prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');

      var dialog = $tr.find(".dialog").dialog({
        autoOpen: false,
        height: 450,
        width: 350,
        modal: true,
        title: 'Edit: ' + name,
        buttons: {
          'Cancel': function () {
            dialog.dialog("close");
          }
        },
        close: function() {}
      });

      $tr.find(".dashicons-edit").on("click", function (e) {
        e.preventDefault();
        dialog.dialog("open");
      });
    } else {
      $tr.find(".dialog").dialog("instance").dialog("destroy");
      $tr.find(".dashicons-edit").remove();
    }
  });
});
