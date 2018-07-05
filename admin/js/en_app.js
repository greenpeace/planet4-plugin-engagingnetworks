$ = jQuery;

var p4_en = {
    api_url: p4_data.api_url,
    Router: null,
    Models: {
        Field: null
    },
    Collections: {
        FieldsCollection: null
    },
    Views: {
        FieldsListView: null,
        FieldsListItemView: null,
        NewFieldView: null
    }
};

// Define custom event on #en_settings_notices element.
$('#en_settings_notices').on('message:add', function (event, options) {
    var $el = $("<div>", {"class": options.type, text: options.message});
    $('#en_settings_notices').append($el);
    setTimeout(function () {
        $el.remove();
    }, 5000);
});

// Set wp nonce header needed for wp rest api authentication.
Backbone.$.ajaxSetup({
    headers: {'X-WP-Nonce': p4_data.nonce}
});


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
        urlRoot: p4_data.api_url + '/fields',

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
        model: p4_en.Models.Field,
        url: p4_data.api_url + '/fields'
    });


    /**
     * A view for listing fields.
     */
    p4_en.Views.FieldsListView = Backbone.View.extend({
        el: '#existing-fields-div',
        template: _.template($('#tmpl-en-fields').html()),

        events: {
            'click .add-field': 'addField',
            'click .edit-field': 'editField',
            'click .reload-fields': 'reload',
        },

        initialize: function () {
            this.listenTo(this.collection, 'sync', this.render);
            this.listenTo(this.collection, 'remove', function () {
                this.collection.fetch()
            });
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

            this.$el.find(".fields-container").fadeTo("fast", 0.33);
            this.collection.each(this.renderOne, this);
            this.$el.find(".fields-container").fadeTo("slow", 1);

            return this;
        },

        reload: function () {
            p4_en.router.navigate('fields', true);
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
            var field = new p4_en.Models.Field(attrs);
            p4_en.show_loader();
            field.save({}, {
                type: 'POST',
                url: p4_data.api_url + '/fields',

                success: function (model, response, options) {
                    p4_en.add_message('Field has been saved.', 'updated');
                    p4_en.hide_loader();
                    p4_en.router.navigate('fields', true);
                },
                error: function (model, xhr, options) {
                    var resp = xhr.responseJSON;
                    var messages = resp.messages;
                    p4_en.add_message(messages.join('<br>'), 'error');
                    p4_en.hide_loader();
                },
            });
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
            p4_en.show_loader();
            field.save({}, {

                success: function (model, response, options) {
                    console.log("The model has been saved to the server");
                    p4_en.add_message('Field has been saved.', 'updated');
                    p4_en.hide_loader();
                    p4_en.router.navigate('fields', true);
                },
                error: function (model, xhr, options) {
                    console.log("Something went wrong while saving the model");
                    var resp = xhr.responseJSON;
                    p4_en.add_message(resp.messages.join('<br>'), 'error');
                    p4_en.hide_loader();
                }
            });
        },
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
            p4_en.show_loader();
            this.model.destroy({
                success: function (model, response, options) {
                    p4_en.add_message('Field has been deleted', 'updated');
                },
                error: function (model, xhr, options) {
                    var resp = xhr.responseJSON;
                    p4_en.add_message(resp.messages.join('<br>'), 'error');
                },
                wait: true
            }).then(function () {
                p4_en.hide_loader();
                p4_en.refresh_data();
            });
        }
    });

    /**
     * A new field view.
     */
    p4_en.Views.NewFieldView = Backbone.View.extend({
        tagName: 'div',
        template: _.template($('#tmpl-new-en-field').html()),


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
     * Retrieves fields from backend.
     */
    p4_en.refresh_data = function () {
        p4_en.show_loader();
        p4_en.fields.fetch({
            success: function (collection, response, options) {
                p4_en.add_message('Fields reloaded', 'updated');
                p4_en.fields_view.collection = collection;
                p4_en.fields_view.render();
            },
            wait: true
        }).then(function () {
            p4_en.hide_loader();
        });
    };

    /**
     * Show loader image
     */
    p4_en.show_loader = function () {
        $('#en_loader').removeClass('hidden');
    };

    /**
     * Hide loader image
     */
    p4_en.hide_loader = function () {
        $('#en_loader').addClass('hidden');
    };

    /**
     * Add a message div to dom.
     * @param message
     * @param type
     */
    p4_en.add_message = function (message, type) {
        $('#en_settings_notices').trigger('message:add', {message: message, type: type});
    };


    /**
     * Initialize data.
     */
    p4_en.init = function () {

        // Create a new router for the app.
        p4_en.router = new p4_en.Router();

        // Instantiate fields collection.
        p4_en.fields = new p4_en.Collections.FieldsCollection();

        p4_en.fields_view = new p4_en.Views.FieldsListView({
            // collection: []
        });
        p4_en.new_field_view = new p4_en.Views.NewFieldView({
            model: new p4_en.Models.Field({isNew: true})
        });

        // Define functions for the different routes.
        p4_en.router.on('route:showFields', function () {
            p4_en.refresh_data();
        });

        p4_en.router.on('route:newField', function () {
            p4_en.new_field_view.model = new p4_en.Models.Field({isNew: true});
            $('#new-field-div').html(p4_en.new_field_view.render().$el);
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
            p4_en.new_field_view.model = field;
            $('#new-field-div').html(p4_en.new_field_view.render().$el);
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