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

function empty(mixed_var) {
	var result = true;

	try {
		if (mixed_var === ""
			|| mixed_var === 0
			|| mixed_var === "0"
			|| mixed_var === null
			|| mixed_var === false
			|| (mixed_var instanceof Array && mixed_var.length === 0)
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

