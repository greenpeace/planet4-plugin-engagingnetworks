$ = jQuery;

var p4_en = p4_en ||
    {
        Models: {},
        Collections: {},
        Views: {}
    };


(function ($, p4_en) {
    'use strict';

    p4_en.Router = Backbone.Router.extend({
        routes: {
            'fields': 'showFields',
            'fields/new': 'newField',
            'fields/edit/:id': 'editField'
        }
    });

    /**
     * Field model.
     */
    p4_en.Models.Field = Backbone.Model.extend({
        defaults: {
            id: 0,
            name: null,
            mandatory: false,
            label: null,
            type: null,
        }
    });

    /**
     * Collection of fields.
     */
    p4_en.Collections.FieldsCollection = Backbone.Collection.extend({
        model: p4_en.Models.Field
    });


    /**
     * A view for listing fields.
     */
    p4_en.Views.FieldsListView = Backbone.View.extend({
        el: '#existing-fields-div',
        template: _.template($('#tmpl-en-fields').html()),

        initialize: function () {
            this.listenTo(this.collection, 'sync', this.render);
            this.listenTo(this.collection, 'remove', this.render);
            this.listenTo(this.collection, 'add', this.render);
        },

        renderOne: function (field) {
            var fieldView = new p4_en.Views.FieldsListItemView({model: field});
            this.$('.fields-container').append(fieldView.render().$el);
        },

        render: function () {
            var html = this.template({col: this.collection});
            this.$el.html(html);
            $('#new-field-div').html('');

            this.collection.each(this.renderOne, this);

            return this;
        }
    });

    /**
     * A single field view.
     */
    p4_en.Views.FieldsListItemView = Backbone.View.extend({
        className: 'field-list-item',
        tagName: 'li',
        template: _.template($('#tmpl-en-field').html()),

        events: {
            'click .edit-field': 'edit',
            'click .delete-field': 'delete'
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
            var newContactForm = new p4_en.Views.NewFieldView({
                model: this.model,
            });

            $('#new-field-div').html(newContactForm.render().$el);
        },

        delete: function (e) {
            e.preventDefault();
            this.model.collection.remove(this.model);
        }
    });

    /**
     * A new field view.
     */
    p4_en.Views.NewFieldView = Backbone.View.extend({
        tagName: 'div',
        template: _.template($('#tmpl-new-en-field').html()),

        events: {
            'click .add-field': 'addField',
            'click .edit-field': 'editField',
        },

        addField: function (e) {
            e.preventDefault();

            var name = $('#en_field_name').val();
            var label = $('#en_field_label').val();
            var mandatory = $('#en_field_mandatory').prop('checked');
            var type = $("#en_field_type option:selected").val();
            if ('' === name || '' === label) {
                alert('Name and label fields can\'t be empty');
                return;
            }

            var attrs = {
                name: name,
                label: label,
                mandatory: mandatory,
                type: type,
            };
            attrs.id = p4_en.fields.isEmpty() ? 1 : (_.max(p4_en.fields.pluck('id')) + 1);
            p4_en.fields.add(new p4_en.Models.Field(attrs));
            p4_en.router.navigate('fields', true);
        },

        editField: function (e) {
            e.preventDefault();

            var name = $('#en_field_name').val();
            var label = $('#en_field_label').val();
            var mandatory = $('#en_field_mandatory').prop('checked');
            var type = $("#en_field_type option:selected").val();
            if ('' === name || '' === label) {
                alert('Name and label fields can\'t be empty');
                return;
            }

            var target = e.target;
            var id = $(target).data('id');
            var attrs = {
                name: name,
                label: label,
                mandatory: mandatory,
                type: type,
            };

            var field = p4_en.fields.get(id);
            field.set(attrs);
            p4_en.router.navigate('fields', true);
        },

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


    /**
     * Fetches data from server.
     */
    p4_en.refreshData = function () {
        p4_en.fields.fetch();
    };


    /**
     * Set initial data
     */
    p4_en.init = function () {

        // Create a new router for the app.
        p4_en.router = new p4_en.Router();

        // Instantiate fields collection.
        p4_en.fields = new p4_en.Collections.FieldsCollection();


        // Define functions for the different routes.
        p4_en.router.on('route:showFields', function () {
            $('#new-field-div').html('');
            var fieldsView = new p4_en.Views.FieldsListView({
                collection: p4_en.fields
            });

            fieldsView.render();
            $('#new-field-div').html('');
        });

        p4_en.router.on('route:newField', function () {
            var newFieldView = new p4_en.Views.NewFieldView({
                model: new p4_en.Models.Field({isNew: true})
            });
            $('#new-field-div').html(newFieldView.render().$el);
        });

        p4_en.router.on('route:editField', function (id) {
            if (0 === p4_en.fields.length) {
                p4_en.router.navigate('fields', {
                    trigger: true,
                    replace: true
                });
                return;
            }
            var field = p4_en.fields.get(id);
            var newFieldView = new p4_en.Views.NewFieldView({
                    model: field
                }
            );
            $('#new-field-div').html(newFieldView.render().$el);
        });

        // Instantiate history and navigate to fields.
        Backbone.history.start();
        p4_en.router.navigate('fields', {
            trigger: true,
            replace: true
        });
    };

    $(document).ready(function () {
        p4_en.init();
    });

})(jQuery, p4_en);