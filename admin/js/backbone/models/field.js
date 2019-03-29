/* global Backbone, p4_en */

(function (app) {
  'use strict';

  app.Models.Field = Backbone.Model.extend({
    urlRoot: app.api_url + '/fields',

    defaults: {
      id: 0,
      name: null,
      label: '',
      default_value: '',
      hidden: 'N',
    }
  });

})(p4_en);
