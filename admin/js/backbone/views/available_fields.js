/* global _, Backbone, p4_en */

(function (app) {
  'use strict';

  /**
   * A view for listing questions.
   */
  app.Views.AvailableFieldsListView = Backbone.View.extend({
    el: '#available-fields-div',
    bodyContainer: '.available-fields-container',
    template: _.template($('#tmpl-en-available-fields').html()),

    page: 0,
    filteredPage: 0,
    perPage: 10,
    totalPages: 0,
    totalFilteredPages: 0,
    totalFields: 0,
    totalFilteredFields: 0,
    start: 0,
    end: 0,
    filtered: null,

    events: {
      'click .reload-fields': 'reload',
      'click .loadprevious': 'renderPreviousPage',
      'click .loadnext': 'renderNextPage',
      'keyup #search_en_name': 'searchName',
    },

    initialize: function () {
      this.listenTo(this.collection, 'sync', this.render);
      this.listenTo(this.collection, 'remove', function () {
        this.collection.fetch();
      });
      this.listenTo(this.collection, 'add', this.render);
    },

    renderOne: function (field) {
      var fieldView = new app.Views.AvailableFieldsListItemView({model: field});
      this.$(this.bodyContainer).append(fieldView.render().$el);
    },

    render: function () {
      this.totalPages = Math.ceil(_.size(this.collection) / this.perPage);
      this.totalFields = _.size(this.collection);
      var html = this.template({col: this});
      this.$el.html(html);
      this.renderGroup(0, this.perPage - 1);

      return this;
    },

    reload: function () {
      app.router.navigate('fields_available', true);
    },

    renderPagination: function () {
      var pagination_tmpl = _.template($('#tmpl-fields-pagination').html());
      var pagination_html = pagination_tmpl({col: this, currentPage: 1});
      $('.available-fields-footer').html(pagination_html);
    },


    renderGroup: function (start, end) {

      var col;
      if (null !== this.filtered) {
        col = this.filtered;
        this.end = this.filteredPage + 1 === this.totalFilteredPages ? this.totalFilteredFields : end + 1;
      } else {
        col = this.collection.models;
        this.end = this.page + 1 === this.totalPages ? this.totalFields : end + 1;
      }

      var subset = _.filter(col, function (num, index) {
        return (index >= start) && (index <= end);
      });

      this.start = start + 1;

      this.renderSubset(subset);

      return this;
    },

    renderSubset: function (subset) {
      $(this.bodyContainer).html('');

      this.renderPagination();

      this.$el.find(this.bodyContainer).fadeTo('fast', 0.33);
      _.each(subset, function (project) {
        this.renderOne(project);
      }, this);
      this.$el.find(this.bodyContainer).fadeTo('slow', 1);
    },

    renderNextPage: function () {
      if (null !== this.filtered) {
        if (this.filteredPage + 1 < this.totalFilteredPages) {
          this.filteredPage++;
          var start = this.filteredPage * this.perPage;
          var end = start + (this.perPage - 1);
          this.renderGroup(start, end);
        }
      } else if (this.page + 1 < this.totalPages) {
        this.page++;
        var start = this.page * this.perPage;
        var end = start + (this.perPage - 1);
        this.renderGroup(start, end);
      }
    },

    renderPreviousPage: function () {
      if (null !== this.filtered) {
        if (this.filteredPage > 0) {
          this.filteredPage--;
          var start = this.filteredPage * this.perPage;
          var end = start + (this.perPage - 1);
          this.renderGroup(start, end);
        }
      } else if (this.page > 0) {
        this.page--;
        var start = this.page * this.perPage;
        var end = start + (this.perPage - 1);
        this.renderGroup(start, end);
      }

    },

    searchName: function () {

      var val = $('#search_en_name').val();
      if ('' === val) {
        this.resetFilteredPagination();
        return this.render();
      }

      this.resetPagination();

      var regex = new RegExp(val, 'gi');

      var results = this.collection.filter(function (model) {
        return model.get('name').match(regex) !== null;
      });
      this.filtered = results;
      this.totalFilteredFields = _.size(results);
      this.totalFilteredPages = Math.ceil(_.size(results) / this.perPage);
      this.filteredPage = 0;

      var start = 0;
      var end = this.perPage - 1;
      this.start = start + 1;
      this.end = end + 1;
      var subset = _.filter(this.filtered, function (num, index) {
        return (index >= start) && (index <= end);
      });
      console.log(results);
      this.renderSubset(subset);
    },

    resetPagination: function () {
      this.page = 0;
      this.totalPages = 0;
      this.totalFields = 0;
    },

    resetFilteredPagination: function () {
      this.filtered = null;
      this.totalFilteredFields = 0;
      this.totalFilteredPages = 0;
      this.filteredPage = 0;
    },
  });

  /**
   * A single available field view.
   */
  app.Views.AvailableFieldsListItemView = Backbone.View.extend({
    className: 'available-field-list-item',
    tagName: 'tr',
    template: _.template($('#tmpl-en-available-field').html()),

    events: {
      'click .add-field': 'add',
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
      var newFieldForm = new app.Views.NewFieldView({
        model: new app.Models.Field(this.model.toJSON()),
      });
      $('#new-field-div').html(newFieldForm.render().$el);
      $('html, body').animate({
        scrollTop: $('#new-field-div').offset().top
      }, 500);
    },
  });

})(p4_en);
