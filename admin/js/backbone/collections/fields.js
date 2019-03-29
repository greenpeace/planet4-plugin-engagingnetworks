/* global Backbone, p4_en */

(function (app) {
  'use strict';

  /**
   * Collection of fields.
   */
  app.Collections.FieldsCollection = Backbone.Collection.extend(
    {
      model: app.Models.Field,
      url: app.api_url + '/fields'
    });

})(p4_en);
