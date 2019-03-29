/* global Backbone, p4_en */

(function (app) {
  'use strict';

  app.Models.FieldAvailable = Backbone.Model.extend({
    urlRoot: app.api_url + '/fields_available',

    defaults: {
      id: 0,
      name: null,
      property: null,
      tag: '',
    }
  });

})(p4_en);
