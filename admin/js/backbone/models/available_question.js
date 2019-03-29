/* global Backbone, p4_en */

(function (app) {
  'use strict';

  app.Models.QuestionAvailable = Backbone.Model.extend({
    urlRoot: app.api_url + '/questions_available',

    defaults: {
      id: 0,
      name: null,
      questionId: 0,
      label: '',
    }
  });

})(p4_en);
