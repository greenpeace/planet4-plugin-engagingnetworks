/* global _, Backbone, p4_en */

(function (app) {
  'use strict';

  app.Views.QuestionsListView = Backbone.View.extend({
    el: '#selected-questions-div',
    template: _.template($('#tmpl-en-questions').html()),

    events: {
      'click .create-question': 'addQuestion',
      'click .edit-question': 'editQuestion',
      'click .reload-questions': 'reload',
      'click .cancel-question': 'reload',
    },

    initialize: function () {
      this.listenTo(this.collection, 'sync', this.render);
      this.listenTo(this.collection, 'remove', function () {
        this.collection.fetch();
      });
      this.listenTo(this.collection, 'add', this.render);
    },

    renderOne: function (question) {
      var questionView = new app.Views.QuestionsListItemView({model: question});
      this.$('.questions-container').append(questionView.render().$el);
    },

    render: function () {
      var html = this.template({col: this.collection});
      this.$el.html(html);
      $('#new-question-div').html('');

      this.$el.find('.questions-container').fadeTo('fast', 0.33);
      this.collection.each(this.renderOne, this);
      this.$el.find('.questions-container').fadeTo('slow', 1);

      return this;
    },

    reload: function (e) {
      e.preventDefault();
      app.refresh_data();
    },

    validateFields: function () {
      var name = $('#en_question_name').val();
      var label = $('#en_question_label').val();
      var type = $('#en_question_type').val();
      var id = $('#en_question_id').val();
      var questionId = $('#en_question__id').val();

      var default_value = '';
      if ($('#en_question_default').length > 0) {
        default_value = $('#en_question_default').val();
      } else if ($('input[name=\'en_question_default\']').length > 0) {
        default_value = $('input[name=\'en_question_default\']:checked').val();
      }

      var hidden = $('#en_question_hidden').is(':checked') ? 'Y' : 'N';
      if ('' === name || '' === label) {
        alert('Name and Question can\'t be empty');
        return false;
      }

      return {
        id: id,
        name: name,
        label: label,
        type: type,
        questionId: questionId,
        hidden: hidden,
        default_value: default_value,
      };
    },

    addQuestion: function (e) {
      e.preventDefault();

      var attrs = this.validateFields();
      if (false === attrs) {
        return;
      }

      var question = new app.Models.Question(attrs);
      app.show_loader();
      question.save({}, {
        type: 'POST',
        url: app.api_url + '/questions',

        success: function () {
          app.add_message('Question has been saved.', 'updated');
          app.hide_loader();
          app.refresh_data();
        },
        error: function (model, xhr) {
          var resp = xhr.responseJSON;
          var messages = resp.messages;
          app.add_message(messages.join('<br>'), 'error');
          app.hide_loader();
        },
      });
    },

    editQuestion: function (e) {
      e.preventDefault();

      var attrs = this.validateFields();
      if (false === attrs) {
        return;
      }

      var question = app.questions.get(attrs.id);
      question.set(attrs);
      app.show_loader();
      question.save({}, {

        success: function () {
          console.log('The model has been saved to the server');
          app.add_message('Question has been saved.', 'updated');
          app.hide_loader();
          app.refresh_data();
        },
        error: function (model, xhr) {
          console.log('Something went wrong while saving the model');
          var resp = xhr.responseJSON;
          app.add_message(resp.messages.join('<br>'), 'error');
          app.hide_loader();
        }
      });
    },
  });

  /**
   * A single question view.
   */
  app.Views.QuestionsListItemView = Backbone.View.extend({
    className: 'question-list-item card',
    tagName: 'li',
    template: _.template($('#tmpl-en-question').html()),

    events: {
      'click .edit-this-question': 'edit',
      'click .delete-question': 'delete'
    },

    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
      this.listenTo(this.model, 'destroy', this.remove);
    },

    render: function () {
      var html = this.template(this.model.toJSON());
      this.$el.html(html);
      return this;
    },

    edit: function (e) {
      e.preventDefault();
      var newQuestionForm = new app.Views.NewQuestionView({
        model: this.model,
      });

      $('#new-question-div').html(newQuestionForm.render().$el);
      $('html, body').animate({
        scrollTop: $('#new-question-div').offset().top
      }, 500);
    },

    delete: function (e) {
      e.preventDefault();
      app.show_loader();
      this.model.destroy({
        success: function (model, response, options) {
          app.add_message('Question has been deleted', 'updated');
        },
        error: function (model, xhr, options) {
          var resp = xhr.responseJSON;
          app.add_message(resp.messages.join('<br>'), 'error');
        },
        wait: true
      }).then(function () {
        app.hide_loader();
        app.refresh_data();
      });
    }
  });

  /**
   * A new question view.
   */
  app.Views.NewQuestionView = Backbone.View.extend({
    tagName: 'div',
    template: _.template($('#tmpl-new-en-question').html()),

    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
      this.listenTo(this.model, 'destroy', this.remove);
    },

    render: function () {
      var html = this.template(this.model.toJSON());
      this.$el.html(html);
      return this;
    }
  });

})(p4_en);
