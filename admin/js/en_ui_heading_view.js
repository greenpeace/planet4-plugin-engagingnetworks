var editAttributeHeadingEN = sui.views.editAttributeField.extend({
	tagName: "span",
	className: 'en-attribute-wrapper',
	events: {
		'change input[type="radio"]': 'inputChanged',
	},
	inputChanged: function (e) {
		var $el;

		if (this.model.get('attr')) {
			$el = this.$el.find('[name="' + this.model.get('attr') + '"]');
		}

		if ('p4en_radio' === this.model.attributes.type) {
			this.setValue($el.filter(':checked').first().val());
		}

		this.triggerCallbacks();
	},

	setValue: function (val) {
		this.model.set('value', val);
	},

	triggerCallbacks: function () {
		var shortcodeName = this.shortcode.attributes.shortcode_tag,
			attributeName = this.model.get('attr'),
			hookName = [shortcodeName, attributeName].join('.'),
			changed = this.model.changed,
			collection = _.flatten(_.values(this.views.parent.views._views)),
			shortcode = this.shortcode;

		/*
		 * Action run when an attribute value changes on a shortcode
		 *
		 * Called as `{shortcodeName}.{attributeName}`.
		 *
		 * @param changed (object)
		 *           The update, ie. { "changed": "newValue" }
		 * @param viewModels (array)
		 *           The collections of views (editAttributeFields)
		 *                         which make up this shortcode UI form
		 * @param shortcode (object)
		 *           Reference to the shortcode model which this attribute belongs to.
		 */
		wp.shortcake.hooks.doAction(hookName, changed, collection, shortcode);
	}
});

sui.views.editAttributeHeadingEN = editAttributeHeadingEN;