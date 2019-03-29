/* global p4_data, Backbone, jQuery */

// Define custom event on #en_settings_notices element.
jQuery('#en_settings_notices, #create_field_notices').on('message:add', function (event, options) {
  var $el = $('<div>', {'class': options.type, text: options.message});
  jQuery(this).append($el);
  setTimeout(function () {
    $el.remove();
  }, 5000);
});


// Set wp nonce header needed for wp rest api authentication.
Backbone.$.ajaxSetup({
  headers: {'X-WP-Nonce': p4_data.nonce}
});

var p4_en = (function ($, p4_data) {

  var api_url = p4_data.api_url;
  var app = {
    api_url: api_url,
    Router: Backbone.Router.extend({
      routes: {
        'questions': 'showQuestions',
        'questions_available': 'showAvailable',
        'questions/new': 'newQuestion',
        'questions/edit/:id': 'editQuestion',
        'fields': 'showFields',
        'fields/new': 'newField',
        'fields/edit/:id': 'editField',
        'fields_available': 'showAvailableFields',
      }
    }),
    Models: {},
    Collections: {},
    Views: {},

    general_messages_container: '#en_settings_notices',
    field_messages_container: '#create_field_notices',

    /**
     * Show loader
     */
    show_loader: function () {
      $('#en_loader').removeClass('hidden');
    },

    /**
     * Hide loader
     */
    hide_loader: function () {
      $('#en_loader').addClass('hidden');
    },

    /**
     * Add a message div to dom.
     * @param message
     * @param type
     */
    add_message: function (message, type) {
      $(this.general_messages_container).trigger('message:add', {message: message, type: type});
    },

    /**
     * Add a message div to dom.
     * @param message
     * @param type
     */
    add_field_message: function (message, type) {
      $(this.field_messages_container).trigger('message:add', {message: message, type: type});
    }
  };

  return app;

})(jQuery, p4_data);
