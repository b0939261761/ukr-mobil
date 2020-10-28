/**
 * Base component
 *
 * @param {object} context
 * @param {object} context.elementRef
 * @returns {BaseComponent}
 * @constructor
 */
function BaseComponent(context) {
	var $ = jQuery;
	var self = this;

	self.elementRef = $(context.elementRef);
	self.fieldPrefix = 'ego-form-field-';

	/**
	 * Return Form Data
	 *
	 * @param {Object} [options]
	 * @param {String} [options.selector=null]
	 * @param {Object} [options.container=null]
	 * @returns {{}}
	 */
	self.getFormData = function (options) {
		options = empty(options) ? {} : options;
		var selector = empty(options.selector) ? '[name^="ego-form-field"]' : options.selector;
		var container = empty(options.container) ? self.elementRef : options.container;

		var data = collectFormData(selector, container),
			formData = {};

		for (var origFieldName in data) {
			var field = data[origFieldName];

			if (field.required && empty(field.value)) {
				self.elementRef
					.find('[name="' + origFieldName + '"]')
					.closest('.ego-control-container')
					.css({
						border: '1px solid red',
						borderRadius: '5px'
					});
			} else {
				self.elementRef
					.find('[name="' + origFieldName + '"]')
					.closest('.ego-control-container')
					.css('border', 'none');
			}

			var fieldName = origFieldName.replace('ego-form-field-', '');
			fieldName = fieldName.split('-').join('_');

			formData[fieldName] = field;
		}

		return formData;
	};

	function init() {

	}

	init();

	//region Auxiliary functions

	//endregion

	return self;
}
