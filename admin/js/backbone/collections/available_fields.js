/* global Backbone, p4_en */

(function (app) {
  'use strict';

  /**
   * Collection of available fields.
   */
  app.Collections.AvailableFieldsCollection = Backbone.Collection.extend({
    model: app.Models.FieldAvailable,
    url: app.api_url + '/fields_available',

    comparator: function (a) {
      return a.get('name').toLowerCase();
    }
  });

})(p4_en);
