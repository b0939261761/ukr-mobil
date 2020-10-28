/**
 * Send request
 *
 * @param params
 * @param {string} params.url URL request
 * @param {string} [params.method='POST'] params.method request method
 * @param {object} [params.data=null] params.data request data
 * @param {string} [params.type='json'] params.type response data type
 * @param {Function} [params.success=null] params.success callback for success event
 * @param {Function} [params.notSuccess=null] params.notSuccess callback for not success event
 * @param {Function} [params.error=null] params.error callback for error event
 */
function _request(params) {
	var $ = jQuery;

	var defaults = {
    url: params.url,
    timeout: 10000,
		method: params.method ? params.method : 'post',
		data: params.data ? params.data : null,
		dataType: params.type ? params.type : "json",
		success: function (response) {
			if (!response.success && params.type === 'json') {
				if (response.error) {
					alert(response.error);
				}

				if (typeof params.notSuccess === 'function') {
					params.notSuccess.call(this, response);
				}

				return;
			}

			if (typeof params.success === 'function') {
				params.success.call(this, response);
			}
			else if (typeof params.success === 'object') {
				alert(params.success.text);
			}
		},
		error: function (XHR, textStatus, errorThrown) {
			console.log(['Error:', textStatus, errorThrown, params]);

			if (typeof params.error === 'function') {
				params.error.call(this, XHR, textStatus, errorThrown);
			}

			$(document).trigger('_request.error');
		}
	};

	$.ajax(defaults);
}

/**
 * Check that form is empty or not
 *
 * @param {Object} data
 * @return {Boolean}
 */
function checkEmptyForm(data) {
	for (var key in data) {
		if (!data.hasOwnProperty(key)) {
			continue;
		}

		var item = data[key];

		if (empty(item.value) && item !== 0) {
			return false;
		}
	}

	return true;
}

/**
 * Collect data from form
 *
 * Return array in format
 * [
 * 		FIELD_NAME_ATTR: {
 * 				value: FIELD_VALUE,
 * 				required: IF_FIELD_REQUIRED,
 *	 			description: FIELD_LABEL//	Field label or `data-description` field attribute
 * 		}
 * ]
 *
 * @param {String} selector
 * @param {Object} container
 * @param {Object} [options]
 * @param {Object} [options.cutName]
 * @return {Object}
 */
function collectFormData(selector, container, options) {
	var $ = jQuery;
	options = empty(options) ? {} : options;

	if (empty(container)) {
		container = $('body');
	} else {
		container = $(container);
	}

	var result = {};

	container.find(selector).each(function (i, node) {
		node = $(node);

		var eLabel = container.find('label[for="' + node.attr('id') + '"]');
		var name = node.attr('name'),
			value = node.val(),
			disabled = node.prop('disabled'),
			description = eLabel.text();

		if (empty(eLabel.get(0)) && !empty(node.attr('data-description'))) {
			description = node.attr('data-description');
		}

		switch(node.attr('type')) {
			case 'checkbox':
				value = node.prop('checked');

				break;

			case 'radio':
				value = container.find('input:radio[name="' + name + '"]:checked').val();

				break;
		}

		switch (node.prop('tagName').toLowerCase()) {
			case 'select':
				if (node.prop('multiple')) {

				} else {
					value = [node.val()];
				}

				break;
		}

		if (empty(value)) {
			var defaultValue = node.attr('data-default-value');

			if (typeof defaultValue !== 'undefined') {
				value = defaultValue;
			}
		}

		if (!empty(name)) {
			var required = node.data('required') === true;

			if (node.attr('required') === 'required') {
				required = true;
			}

			if (!empty(options.cutName)) {
				name = name.replace(options.cutName, '');
			}

			result[name] = {
				value: value,
				required: required,
				disabled: disabled,
				description: description
			}
		}
	});

	return result;
}

/**
 * Is the required fields is Empty?
 * Use with `collectFormData`
 *
 * @param formData
 * @returns {boolean}
 */
function isRequiredFieldsEmpty(formData) {
	for (var key in formData) {
		if (!formData.hasOwnProperty(key)) {
			continue;
		}

		var field = formData[key];

		if (field.required && (empty(field.value) && field.value !== 0)) {
			return true;
		}
	}

	return false;
}

/**
 * Check that object is array
 *
 * @param mixed_var
 * @returns {boolean}
 */
function is_array(mixed_var) {
	return mixed_var instanceof Array;
}

/**
 * Check empty value
 *
 * @param mixed_var
 * @returns {boolean}
 */
function empty(mixed_var) {
	var result = true;

	try {
		if (mixed_var === ""
			|| mixed_var === 0
			|| mixed_var === "0"
			|| mixed_var === null
			|| mixed_var === false
			|| (is_array(mixed_var) && mixed_var.length === 0)
			|| typeof mixed_var === 'undefined'
		) {
			result = true;
		} else {
			result = false;
		}
	} catch (e) {
		console.log(e);
		result = true;
	}
	return result;
}

/**
 * Return URL parameter
 *
 * @param {String} param
 * @param {String} [url]
 * @returns {string | null}
 */
function getUrlParam(param, url) {
	url = empty(url) ? window.location.href : url;

	return (new URL(url)).searchParams.get(param);
}

/**
 * Clear all input fields in container
 *
 * @param eContainer
 */
function clearFields(eContainer) {
	var $ = jQuery;

	eContainer = $(eContainer);

	eContainer
		.find(':input')
		.not(':button, :submit, :reset')
		.val('')
		.prop('checked', false)
		.prop('selected', false);
}

/**
 * Set Field value
 *
 * @param eField
 * @param value
 */
function setFieldValue(eField, value) {
	var $ = jQuery;
	eField = $(eField);
	var tagName = eField.prop('tagName'),
		name = eField.first().attr('name');

	if (empty(tagName)) {
		return;
	}

	tagName = tagName.toLowerCase();

	if ($.inArray(tagName, ['input', 'textarea']) !== -1) {
		switch (eField.attr('type')) {
			case 'checkbox':
				eField.prop('checked', value === true);

				break;

			case 'radio':
				$('input:radio[name="' + name + '"][value="' + value + '"]').prop('checked', true);

				break;

			default:
				eField.val(value);

				break;
		}
	}
	else if (tagName === 'select') {
		eField
			.val(value)
			.change();
	}
}

String.prototype.trunc = function (n, useWordBoundary) {
	if (this.length <= n) {
		return this;
	}

	var subString = this.substr(0, n - 1);

	return (useWordBoundary
		? subString.substr(0, subString.lastIndexOf(' '))
		: subString) + "&hellip;";
};
