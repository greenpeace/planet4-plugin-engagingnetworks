/* global _, Backbone, p4_en */

(function (app) {
  'use strict';

  /**
   * A view for listing questions.
   */
  app.Views.AvailableQuestionsListView = Backbone.View.extend({
    el: '#existing-questions-div',
    template: _.template($('#tmpl-en-available-questions').html()),

    events: {
      'click .reload-questions': 'reload',
    },

    initialize: function () {
      this.listenTo(this.collection, 'sync', this.render);
      this.listenTo(this.collection, 'remove', function () {
        this.collection.fetch();
      });
      this.listenTo(this.collection, 'add', this.render);
    },

    renderOne: function (question) {
      var questionView = new app.Views.AvailableQuestionsListItemView({model: question});
      this.$('.available-questions-container').append(questionView.render().$el);
    },

    render: function () {
      var html = this.template({col: this.collection});
      this.$el.html(html);
      this.$el.find('.available-questions-container').fadeTo('fast', 0.33);
      this.collection.each(this.renderOne, this);
      this.$el.find('.available-questions-container').fadeTo('slow', 1);

      return this;
    },

    reload: function () {
      app.router.navigate('questions_available', true);
    },

  });

  /**
   * A single question view.
   */
  app.Views.AvailableQuestionsListItemView = Backbone.View.extend({
    className: 'question-list-item card',
    tagName: 'li',
    template: _.template($('#tmpl-en-available-question').html()),

    events: {
      'click .add-question': 'add',
    },

    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
    },

    render: function () {
      var html = this.template(this.model.toJSON());
      this.$el.html(html);
      return this;
    },

    add: function (e) {
      e.preventDefault();
      var newQuestionForm = new app.Views.NewQuestionView({
        model: new app.Models.Question(this.model.toJSON()),
      });
      $('#new-question-div').html(newQuestionForm.render().$el);
      $('html, body').animate({
        scrollTop: $('#new-question-div').offset().top
      }, 500);
    },
  });


})(p4_en);
