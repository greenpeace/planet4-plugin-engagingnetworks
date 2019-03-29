/* global Backbone, p4_en */

(function (app) {
  'use strict';

  app.Models.Question = Backbone.Model.extend({
    urlRoot: app.api_url + '/questions',

    defaults: {
      id: 0,
      name: null,
      questionId: 0,
      label: '',
      type: null,
      default_value: '',
      hidden: 'N',
    }
  });

})(p4_en);
