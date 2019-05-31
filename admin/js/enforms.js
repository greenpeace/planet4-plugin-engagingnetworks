/* global ajaxurl, jQuery, Backbone, _ */

jQuery(function ($) {

  /**
   * Event listener for add field/question button.
   */
  $('.add-en-field').off('click').on('click', function (e) {
    e.preventDefault();

    $(this).prop('disabled', true);
    var field_data = {
      name: $(this).data('name'),
      en_type: $(this).data('type'),
      property: $(this).data('property'),
      id: $(this).data('id'),
      locales: {},
    };

    // If we add an Opt-in then retrieve the labels for all locales that exist for it from EN.
    if( 'OPT' === field_data.en_type ) {
      $.ajax({
        url: ajaxurl,
        type: 'GET',
        data: {
          action: 'get_supporter_question_by_id',
          id: $(this).data('id')
        },
      }).done(function (response) {
        $.each(response, function (i, value) {
          let label = value.content.data[0].label;
          field_data['locales'][value.locale] = _.escape( label );
        });
        p4_enform.fields.add(new p4_enform.Models.EnformField(field_data));
      }).fail(function (response) {
        console.log(response); //eslint-disable-line no-console
      });
    } else {
      p4_enform.fields.add(new p4_enform.Models.EnformField(field_data));
    }
  });

  /**
   * Make form selected fields table sortable.
   */
  $('#en_form_selected_fields_table > tbody').sortable({
    handle: '.dashicons-sort',
    stop: function (event, ui) {
      ui.item.trigger('sort-field', ui.item.index());
    }
  });


  /**
   * Hook into post submit to inject form fields.
   */
  $('#post').on('submit', function () {
    $('#p4enform_fields').val(JSON.stringify(p4_enform.fields.toJSON()));
  });

  /**
   * Disable preview form fields.
   */
  $('#meta-box-form :input').prop('disabled', true);

});

/**
 * Define models, collections, views for p4 en forms.
 */
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
      property: '',
      label: '',
      default_value: '',
      js_validate_regex: '',
      js_validate_regex_msg: '',
      js_validate_function: '',
      en_type: 'N',
      hidden: false,
      required: false,
      input_type: '0',
      locales: {},
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
      'update-sort': 'updateSort',
    },
    views: {},

    /**
     * Initialize view.
     */
    initialize: function () {
      this.listenTo(this.collection, 'add', this.renderOne);
    },

    /**
     * Render a single field.
     *
     * @param field Field model.
     */
    renderOne: function (field) {
      var fieldView = new app.Views.FieldsListItemView({model: field});
      this.views[field.id] = fieldView;
      $('#en_form_selected_fields_table > tbody').append(fieldView.render());
      $('.add-en-field').filter('*[data-id="' + field.id + '"]').prop('disabled', true);
      fieldView._delegateEvents();
      fieldView.createFieldDialog();
    },

    /**
     * Render view.
     */
    render: function () {
      _.each(this.collection.models, function (project) {
        this.renderOne(project);
      }, this);
      this.disableEmailField();
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

    /**
     * Reorder collection models.
     *
     * @param event Event object
     * @param model Field Model.
     * @param position New index.
     */
    updateSort: function (event, model, position) {
      this.collection.remove(model, {silent: true}); //
      this.collection.add(model, {at: position, silent: true});
    },

    /**
     * Disable email field attributes besides label.
     */
    disableEmailField: function () {
      $('tr[data-en-name="Email"] span.remove-en-field').remove();
      $('tr[data-en-name="Email"] input[data-attribute="required"]').prop('checked', true).prop('disabled', true);
      $('tr[data-en-name="Email"] select[data-attribute="input_type"]').val('email').prop('disabled', true);
      let emailModel = this.collection.findWhere({property: 'emailAddress'});
      if ('undefined' !== typeof emailModel) {
        emailModel.set('input_type', 'email')
          .set('required', true);
      }
    }
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
      'change select.field-type-select': 'selectChanged',
      'sort-field': 'sortField'
    },

    /**
     * Handles input text value changes and stores them to the model.
     *
     * @param event Event object.
     */
    inputChanged(event) {
      var $target = $(event.target);
      var value = $target.val();
      var attr = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Handles input checkbox value changes and stores them to the model.
     *
     * @param event Event object.
     */
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
      var value   = $(event.target).val();
      var $tr     = $(event.target).closest('tr');
      var id      = $tr.data('en-id');
      var attr    = $(event.target).data('attribute');
      var en_type = this.model.get('en_type');
      this.model.set(attr, value);

      $tr.find('.dashicons-edit').parent().remove();
      if ('text' === value) {
        this.createFieldDialog();
      } else if ('hidden' === value) {
        this.$el.find('input[data-attribute="required"]').prop('checked', false).trigger('change').prop('disabled', true);
        this.$el.find('input[data-attribute="label"]').val('').trigger('change').prop('disabled', true);
        this.createFieldDialog();
      } else if ('OPT' === en_type) {
        this.$el.find('input[data-attribute="label"]').trigger('change').prop('disabled', true);
        this.createFieldDialog();
      } else {
        if (null !== this.dialog_view) {
          this.dialog_view.destroy();
          this.dialog_view = null;
        }
        $('body').find('.dialog-' + id).remove();
      }

      if ('hidden' !== value) {
        this.$el.find('input[data-attribute="required"]').prop('disabled', false);
        if ('OPT' !== en_type) {
          this.$el.find('input[data-attribute="label"]').prop('disabled', false);
        }
      }
    },

    /**
     * Initialize view.
     */
    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
    },

    /**
     * Create field dialog view.
     */
    createFieldDialog: function () {
      var input_type = this.model.get('input_type');
      var en_type    = this.model.get('en_type');
      var tmpl       = '';

      if ( 'Field' === en_type || 'GEN' === en_type ) {
        if ('hidden' === input_type) {
          tmpl = '#tmpl-en-hidden-field-dialog';
        } else if ('text' === input_type) {
          tmpl = '#tmpl-en-text-field-dialog';
        }

        if (null !== this.dialog_view) {
          this.dialog_view.destroy();
          $('body').find('.dialog-' + this.model.id).remove();
          this.$el.find('.dashicons-edit').parent().remove();
        }
      } else if ( 'OPT' === en_type ) {
        tmpl = '#tmpl-en-question-dialog';
      }

      if ( tmpl ) {
        this.dialog_view = new app.Views.FieldDialog({row: this.model.id, model: this.model, template: tmpl});
      }
    },

    /**
     * Delegate events after view is rendered.
     */
    _delegateEvents: function () {
      this.$el = $('tr[data-en-id="' + this.model.id + '"]');
      this.delegateEvents();
    },

    /**
     * Render view.
     */
    render: function () {
      var html = this.template(this.model.toJSON());
      return html;
    },

    /**
     * Destroy view.
     */
    destroy: function () {
      if (null !== this.dialog_view) {
        this.dialog_view.destroy();
      }
      this.remove();
    },

    /**
     * Trigger collection sorting.
     *
     * @param event Event object
     * @param index New index for the field model.
     */
    sortField: function (event, index) {
      this.$el.trigger('update-sort', [this.model, index]);
    },
  });

  /**
   * A single field view.
   */
  app.Views.FieldDialog = Backbone.View.extend({
    row: null,
    dialog: null,
    events: {
      'keyup input': 'inputChanged',
      'change input[type="text"]': 'inputChanged',
      'change input[type="checkbox"]': 'checkboxChanged',
      'change .question-locale-select': 'localeChanged',
    },

    /**
     * Handles input text value changes and stores them to the model.
     *
     * @param event Event object.
     */
    inputChanged(event) {
      var $target = $(event.target);
      var value   = $target.val();
      var attr    = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Handles input checkbox value changes and stores them to the model.
     *
     * @param event Event object.
     */
    checkboxChanged(event) {
      var $target = $(event.target);
      var value   = $target.is(':checked');
      var attr    = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Handles locale select changes and stores them to the model.
     *
     * @param event Event object.
     */
    localeChanged(event) {
      let $dialog  = $(event.target).closest('div.dialog');
      let field_id = $dialog.attr('data-en-id');
      let label    = $(event.target).val();

      $('.question-label', $dialog).html( $(event.target).val() );
      $('input[data-attribute="label"]', $('tr[data-en-id="' + field_id + '"]'))
        .prop('disabled', false)
        .val( label )
        .trigger('change')
        .prop('disabled', true);
    },

    /**
     * Initialize view instance.
     *
     * @param options Options object.
     */
    initialize: function (options) {
      this.template = _.template($(options.template).html());
      this.rowid    = options.row;
      this.row      = $('tr[data-en-id="' + this.rowid + '"]');
      this.model    = options.model;
      this.render();
    },

    /**
     * Render dialog view
     */
    render: function () {
      $(this.row).find('.actions').prepend(this.template(this.model.toJSON()));
      $(this.row).find('.actions').prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');

      this.dialog = $(this.row).find('.dialog').dialog({
        autoOpen: false,
        height: 450,
        width: 350,
        modal: true,
        title: 'Edit: ' + this.model.get('name'),
        dialogClass: 'dialog-' + this.rowid,
        buttons: {
          'Close': function () {
            dialog.dialog('close');
          }
        },
      });

      this.el   = '.dialog-' + this.rowid;
      this.$el  = $(this.el).find('.ui-dialog-content');
      let label = $('.question-locale-select', this.$el).val();
      this.delegateEvents();

      var dialog = this.dialog;
      $(this.row).find('.dashicons-edit').on('click', function (e) {
        e.preventDefault();
        dialog.dialog('open');
      });

      // Handle Label selection.
      $('.question-label', this.$el).html( label );
      $('.question-locale-select').change();
    },

    /**
     * Destroy dialog view.
     * Set default values to model.
     */
    destroy: function () {
      this.dialog.dialog('destroy');
      this.model.set('default_value', '');
      this.model.set('js_validate_regex', '');
      this.model.set('js_validate_regex_msg', '');
      this.model.set('js_validate_function', '');
      this.model.set('hidden', false);
      this.model.set('locales', {});
      this.remove();
    }
  });

  return app;

})(jQuery);

// Handles initial page load of new/edit enform page.
// Create fields collections and views and populate views if there are any saved fields.
(function ($, app) {

  /**
   * Initialize new/edit enform page.
   */
  app.init_new_enform_page = function () {

    // Create fields collection.
    app.fields = new app.Collections.EnformFields();

    // Instantiate fields collection.
    var fields = $('#p4enform_fields').val();

    // If fields are set populate the fields collection.
    if ('' !== fields) {

      fields = JSON.parse(fields);
      var fields_arr = [];
      _.each(fields, function (field) {
        fields_arr.push(new app.Models.EnformField(field));
      }, this);
      app.fields.add(fields_arr);
    }

    // If it is a new post, add email field.
    if ('auto-draft' === $('#original_post_status').val()) {
      $('button[class="add-en-field"][data-property="emailAddress"] ').click();
    }

    app.fields_view = new app.Views.FieldsListView({collection: app.fields});
    app.fields_view.render();
  };

  /**
   * Initialize app when page is loaded.
   */
  $(document).ready(function () {

    // Initialize app when document is loaded.
    app.init_new_enform_page();

    // Initialize tooltips.
    app.fields_view.$el.tooltip({
      track: true,
      show: { effect: 'fadeIn', duration: 500 }
    });
  });

})(jQuery, p4_enform);
