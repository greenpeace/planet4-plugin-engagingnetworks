/* global Backbone, p4_en */

(function (app) {
  'use strict';

  /**
   * Collection of available questions.
   */
  app.Collections.AvailableQuestionsCollection = Backbone.Collection.extend({
    model: app.Models.QuestionAvailable,
    url: app.api_url + '/questions_available',

    comparator: function (a) {
      return a.get('type') + a.get('name').toLowerCase();
    }
  });

})(p4_en);
