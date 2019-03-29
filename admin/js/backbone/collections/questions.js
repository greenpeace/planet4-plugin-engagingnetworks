/* global Backbone, p4_en */

(function (app) {
  'use strict';

  /**
   * Collection of questions.
   */
  app.Collections.QuestionsCollection = Backbone.Collection.extend({
    model: app.Models.Question,
    url: app.api_url + '/questions'
  });

})(p4_en);
