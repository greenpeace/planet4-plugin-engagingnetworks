/* global jQuery, Backbone, p4_en */

(function ($, app) {
  'use strict';
  

  /**
   * Retrieves questions from backend.
   */
  app.refresh_data = function () {
    app.show_loader();
    app.questions.fetch({
      success: function (collection) {
        app.add_message('Questions reloaded', 'updated');
        app.questions_view.collection = collection;
        app.questions_view.render();
      },
      wait: true
    }).then(function () {
      app.hide_loader();
    });
  };

  /**
   * Retrieves available questions from backend.
   */
  app.refresh_available = function () {
    app.show_loader();
    app.available_questions.fetch({
      success: function (collection) {
        app.add_message('Available Questions reloaded', 'updated');
        app.available_questions_view.collection = collection;
        app.available_questions_view.render();
      },
      wait: true
    }).then(function () {
      app.hide_loader();
    });
  };


  /**
   * Initialize data.
   */
  app.init = function () {

    // Create a new router for the app.
    app.router = new app.Router();

    // Instantiate questions collection.
    app.questions = new app.Collections.QuestionsCollection();
    app.available_questions = new app.Collections.AvailableQuestionsCollection();

    app.questions_view = new app.Views.QuestionsListView();
    app.available_questions_view = new app.Views.AvailableQuestionsListView();


    // Define functions for the different routes.
    app.router.on('route:showQuestions', function () {
      app.refresh_data();
    });

    app.router.on('route:showAvailable', function () {
      app.refresh_available();
    });

    app.router.on('route:newQuestion', function () {
      app.new_question_view.model = new app.Models.Question({isNew: true});
      $('#new-question-div').html(app.new_question_view.render().$el);
    });

    app.router.on('route:editQuestion', function (id) {
      if (0 === app.questions.length) {
        app.router.navigate('questions', {
          trigger: true,
          replace: true
        });
        return;
      }
      var question = app.questions.get(id);
      app.new_question_view.model = question;
      $('#new-question-div').html(app.new_question_view.render().$el);
    });

    // Instantiate history and navigate to questions.
    Backbone.history.start();
    app.refresh_data();
    app.refresh_available();
  };

  $(document).ready(function () {
    app.init();
  });

})(jQuery, p4_en);
