/* global _, Backbone, p4_en */

(function (app) {
  'use strict';

  /**
   * A view for listing fields.
   */
  app.Views.FieldsListView = Backbone.View.extend({
    el: '#stored-fields-div',
    template: _.template($('#tmpl-en-fields').html()),

    events: {
      'click .add-field': 'addField',
      'click .edit-field': 'editField',
      'click .reload-fields': 'reload',
      'click .cancel-field': 'reload',
    },

    initialize: function () {
      this.listenTo(this.collection, 'sync', this.render);
      this.listenTo(this.collection, 'remove', function () {
        this.collection.fetch();
      });
      this.listenTo(this.collection, 'add', this.render);
    },

    renderOne: function (field) {
      var fieldView = new app.Views.FieldsListItemView({model: field});
      this.$('.fields-container').append(fieldView.render().$el);
    },

    render: function () {
      var html = this.template({col: this.collection});
      this.$el.html(html);
      $('#new-field-div').html('');

      this.$el.find('.fields-container').fadeTo('fast', 0.33);
      this.collection.each(this.renderOne, this);
      this.$el.find('.fields-container').fadeTo('slow', 1);

      return this;
    },

    reload: function (e) {
      e.preventDefault();
      app.refresh_fields();
    },

    validateFields: function () {
      var name = $('#en_field_name').val();
      var label = $('#en_field_label').val();
      var id = $('#en_field_id').val();
      var default_value = $('#en_field_default').val();

      var hidden = $('#en_field_hidden').is(':checked') ? 'Y' : 'N';
      if ('' === name || '' === label) {
        app.add_field_message(['Name and Label can\'t be empty'].join('<br>'), 'error');
        return false;
      }

      return {
        id: id,
        name: name,
        label: label,
        hidden: hidden,
        default_value: default_value,
      };
    },

    addField: function (e) {
      e.preventDefault();

      var attrs = this.validateFields();
      if (false === attrs) {
        return;
      }

      var field = new app.Models.Field(attrs);
      app.show_loader();
      field.save({}, {
        type: 'POST',
        url: app.api_url + '/fields',

        success: function (model, response, options) {
          app.add_message('Field has been saved.', 'updated');
          app.hide_loader();
          app.refresh_fields();
          app.refresh_available_fields();
        },
        error: function (model, xhr, options) {
          var resp = xhr.responseJSON;
          var messages = resp.messages;
          app.add_field_message(messages.join('<br>'), 'error');
          app.hide_loader();
        },
      });
    },

    editField: function (e) {
      e.preventDefault();

      var attrs = this.validateFields();
      if (false === attrs) {
        return;
      }

      var target = e.target;
      var id = $(target).data('id');

      var field = app.fields.get(id);
      field.set(attrs);
      app.show_loader();
      field.save({}, {

        success: function (model, response, options) {
          console.log('The model has been saved to the server');
          app.add_message('Field has been saved.', 'updated');
          app.hide_loader();
          app.refresh_fields();
          app.refresh_available_fields();
        },
        error: function (model, xhr, options) {
          console.log('Something went wrong while saving the model');
          var resp = xhr.responseJSON;
          app.add_field_message(resp.messages.join('<br>'), 'error');
          app.hide_loader();
        }
      });
    },
  });

  /**
   * A single field view.
   */
  app.Views.FieldsListItemView = Backbone.View.extend({
    className: 'field-list-item',
    tagName: 'tr',
    template: _.template($('#tmpl-en-field').html()),

    events: {
      'click .edit-this-field': 'edit',
      'click .delete-field': 'delete'
    },

    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
      this.listenTo(this.model, 'destroy', this.remove);
    },

    render: function () {
      var html = this.template(this.model.toJSON());
      this.$el.html(html);
      return this;
    },

    edit: function (e) {
      e.preventDefault();
      var newFieldForm = new app.Views.NewFieldView({
        model: this.model,
      });

      $('#new-field-div').html(newFieldForm.render().$el);
      $('html, body').animate({
        scrollTop: $('#new-field-div').offset().top
      }, 500);
    },

    delete: function (e) {
      e.preventDefault();
      app.show_loader();
      this.model.destroy({
        success: function (model, response, options) {
          app.add_message('Field has been deleted', 'updated');
        },
        error: function (model, xhr, options) {
          var resp = xhr.responseJSON;
          app.add_message(resp.messages.join('<br>'), 'error');
        },
        wait: true
      }).then(function () {
        app.hide_loader();
        app.refresh_fields();
        app.refresh_available_fields();
      });
    }
  });

  /**
   * A new field view.
   */
  app.Views.NewFieldView = Backbone.View.extend({
    tagName: 'div',
    template: _.template($('#tmpl-new-en-field').html()),

    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
      this.listenTo(this.model, 'destroy', this.remove);
    },

    render: function () {
      var html = this.template(this.model.toJSON());
      this.$el.html(html);
      return this;
    }
  });

})(p4_en);
