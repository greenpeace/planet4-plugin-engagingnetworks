/* global jQuery, Backbone, _ */

jQuery(function ($) {

  /**
   * Event listener for add field/question button.
   */
  $('.add-en-field').off('click').on('click', function (e) {
    e.preventDefault();

    $(this).prop('disabled', true);
    var id = $(this).data('id');
    var name = $(this).data('name');
    var type = $(this).data('type');
    var field_data = {
      name: name,
      en_type: type,
      id: id,
    };
    p4_enform.fields.add(new p4_enform.Models.EnformField(field_data));
  });

  /**
   * Make form selected fields table sortable.
   */
  $('#en_form_selected_fields_table > tbody').sortable({
    handle: '.dashicons-sort',
  });


  /**
   * Hook into post submit to inject form fields.
   */
  $('#post').on('submit', function (event) {
    $('#p4enform_fields').val(JSON.stringify(p4_enform.fields.toJSON()));
  });

});


var p4_enform = (function ($) {

  var app = {
    Models: {},
    Collections: {},
    Views: {},
  };

  /**
   * Model for en form field.
   */
  app.Models.EnformField = Backbone.Model.extend({
    urlRoot: '',
    defaults: {
      id: 0,
      name: null,
      label: '',
      default_value: '',
      en_type: 'N',
      hidden: false,
      required: false,
      input_type: 'text',
    }
  });

  /**
   * Collection of fields.
   */
  app.Collections.EnformFields = Backbone.Collection.extend(
    {
      model: app.Models.EnformField,
      url: ''
    });

  /**
   * A view for listing fields.
   */
  app.Views.FieldsListView = Backbone.View.extend({
    el: '#en_form_selected_fields_table',
    template: _.template($('#tmpl-en-selected-fields').html()),

    events: {
      'click .remove-en-field': 'removeField',
    },
    views: {},

    initialize: function () {
      this.listenTo(this.collection, 'add', this.renderOne);
    },

    renderOne: function (field) {
      var fieldView = new app.Views.FieldsListItemView({model: field});
      this.views[field.id] = fieldView;
      $('#en_form_selected_fields_table > tbody').append(fieldView.render());
      fieldView._delegateEvents();
    },

    /**
     * Event listener for remove field/question button.
     */
    removeField: function (e) {
      e.preventDefault();

      var $tr = $(e.target).closest('tr');
      var id = $tr.data('en-id');
      $('.add-en-field').filter('*[data-id="' + id + '"]').prop('disabled', false);
      this.collection.remove(this.collection.findWhere({id: id}));
      this.views[id].destroy();
      $tr.remove();
    },
  });

  /**
   * A single field view.
   */
  app.Views.FieldsListItemView = Backbone.View.extend({
    className: 'field-item',
    template: _.template($('#tmpl-en-selected-field').html()),
    dialog_view: null,

    events: {
      'keyup input[type="text"]': 'inputChanged',
      'change input[type="text"]': 'inputChanged',
      'change input[type="checkbox"]': 'checkboxChanged',
      'change select': 'selectChanged',
    },

    inputChanged(event) {
      var $target = $(event.target);
      var value = $target.val();
      var attr = $target.data('attribute');
      this.model.set(attr, value);
    },

    checkboxChanged(event) {
      var $target = $(event.target);
      var value = $target.is(':checked');
      var attr = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Register event listener for field type select box.
     */
    selectChanged(event) {
      var value = $(event.target).val();
      var $tr = $(event.target).closest('tr');
      var id = $tr.data('en-id');

      if ('text' === value) {
        this.dialog_view = new app.Views.FieldDialog({row: id, target: event.target, model: this.model});
      } else {
        if (null !== this.dialog_view) {
          this.dialog_view.destroy();
          this.dialog_view = null;
        }
        $('body').find('.dialog-' + id).remove();
        $tr.find('.dashicons-edit').remove();
      }
    },

    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
    },

    _delegateEvents: function () {
      this.$el = $('tr[data-en-id="' + this.model.id + '"]');
      this.delegateEvents();
    },
    render: function () {
      var html = this.template(this.model.toJSON());
      return html;
    },

    destroy: function () {
      if (null !== this.dialog_view) {
        this.dialog_view.destroy();
      }
      this.remove();
    }
  });

  /**
   * A single field view.
   */
  app.Views.FieldDialog = Backbone.View.extend({
    template: _.template($('#tmpl-en-field-dialog').html()),
    row: null,
    dialog: null,
    events: {
      'keyup input': 'inputChanged',
      'change input': 'inputChanged',
      'change input[type="checkbox"]': 'checkboxChanged',
    },

    inputChanged(event) {
      var $target = $(event.target);
      var value = $target.val();
      var attr = $target.data('attribute');
      this.model.set(attr, value);
    },

    checkboxChanged(event) {
      var $target = $(event.target);
      var value = $target.is(':checked');
      var attr = $target.data('attribute');
      this.model.set(attr, value);
    },

    initialize: function (options) {
      this.rowid = options.row;
      this.row = options.target;
      this.render();
    },

    render: function () {
      $(this.row).parent().next().prepend(this.template());
      $(this.row).parent().next().prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');

      var id = $(this.row).closest('tr').data('en-id');
      this.dialog = $(this.row).closest('tr').find('.dialog').dialog({
        autoOpen: false,
        height: 450,
        width: 350,
        modal: true,
        title: 'Edit: ' + this.model.get('name'),
        dialogClass: 'dialog-' + id,
        buttons: {
          'Close': function () {
            dialog.dialog('close');
          }
        },
      });

      this.el = '.dialog-' + this.rowid;
      this.$el = $('.dialog-' + this.rowid).find('.ui-dialog-content');
      this.delegateEvents();

      var dialog = this.dialog;
      $(this.row).closest('tr').find('.dashicons-edit').on('click', function (e) {
        e.preventDefault();
        dialog.dialog('open');
      });
    },

    destroy: function () {
      this.dialog.dialog('destroy');
      this.model.set('default_value', '');
      this.model.set('hidden', false);
      this.remove();
    }
  });

  return app;

})(jQuery);


(function ($, app) {

  /**
   * Initialize new enform page.
   */
  app.init_new_enform_page = function () {

    // Instantiate fields collection.
    app.fields = new app.Collections.EnformFields();
    app.fields_view = new app.Views.FieldsListView({collection: app.fields});

  };

  /**
   * Initialize page.
   */
  $(document).ready(function () {
    app.init_new_enform_page();
  });

})(jQuery, p4_enform);
