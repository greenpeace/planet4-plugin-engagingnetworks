/* global jQuery, p4_en */

(function ($, app) {
  
  /**
   * Retrieves fields from backend.
   */
  app.refresh_fields = function () {
    app.show_loader();
    app.fields.fetch({
      success: function (collection) {
        app.add_message('Fields reloaded', 'updated');
        app.fields_view.collection = collection;
        app.fields_view.render();
      },
      wait: true
    }).then(function () {
      app.hide_loader();
    });
  };

  /**
   * Retrieves available fields from backend.
   */
  app.refresh_available_fields = function () {
    app.show_loader();
    app.available_fields.fetch({
      success: function (collection) {
        app.add_message('Available Fields reloaded', 'updated');
        app.available_fields_view.collection = collection;
        app.available_fields_view.render();
      },
      wait: true
    }).then(function () {
      app.hide_loader();
    });
  };

  /**
   * Initialize data.
   */
  app.init_fields_settings_page = function () {

    // Create a new router for the app.
    app.router = new app.Router();

    // Instantiate fields collection.
    app.fields = new app.Collections.FieldsCollection();
    app.available_fields = new app.Collections.AvailableFieldsCollection();

    app.available_fields_view = new app.Views.AvailableFieldsListView();

    app.fields_view = new app.Views.FieldsListView();

    app.new_field_view = new app.Views.NewFieldView({
      model: new app.Models.Field({isNew: true})
    });

    // Define functions for the different routes.
    app.router.on('route:showFields', function () {
      app.refresh_fields();
    });

    app.router.on('route:newField', function () {
      app.new_field_view.model = new app.Models.Field({isNew: true});
      $('#new-field-div').html(app.new_field_view.render().$el);
    });

    app.router.on('route:editField', function (id) {
      if (0 === app.fields.length) {
        app.router.navigate('fields', {
          trigger: true,
          replace: true
        });
        return;
      }
      var field = app.fields.get(id);
      app.new_field_view.model = field;
      $('#new-field-div').html(app.new_field_view.render().$el);
    });

    app.refresh_available_fields();
    app.refresh_fields();
  };


  /**
   * Initialize data.
   */
  $(document).ready(function () {
    app.init_fields_settings_page();
  });

})(jQuery, p4_en);
