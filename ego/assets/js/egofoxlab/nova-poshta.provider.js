/**
 * Nova Poshta provider
 *
 * @returns {NovaPoshtaProvider}
 * @constructor
 */
function NovaPoshtaProvider() {
	var self = this;

	var data = {
		url: 'https://api.novaposhta.ua/v2.0/json/',
		apiKey: null
	};

	/**
	 * Set API key
	 *
	 * @param value
	 * @returns {NovaPoshtaProvider}
	 */
	self.setApiKey = function (value) {
		data.apiKey = value;

		return self;
	};

	/**
	 * Return areas
	 *
	 * @param {Function} callback
	 */
	self.getAreas = function(callback) {
		_request({
			url: data.url,
			data: JSON.stringify({
				apiKey: data.apiKey,
				modelName: 'Address',
				calledMethod: 'getAreas',
				methodProperties: {}
			}),
			success: function (response) {
				if (response.success) {
					callback(response.data);
				} else {
					callback([]);
				}
			}
		});
	};

	/**
	 * Return settlements
	 *
	 * @param {object} properties
	 * @param {string} [properties.Ref] - Идентификатор города
	 * @param {string} [properties.FindByString] - Поиск по названию города
	 * @param {string} [properties.Page] - Номер страницы для отображения
	 * @param {Function} callback
	 */
	self.getCities = function(properties, callback) {
		_request({
			url: data.url,
			data: JSON.stringify({
				apiKey: data.apiKey,
				modelName: 'Address',
				calledMethod: 'getCities',
				methodProperties: properties
			}),
			success: function (response) {
				if (response.success) {
					callback(response.data);
				} else {
					callback([]);
				}
			}
		});
	};

	/**
	 * Return settlements
	 *
	 * @param {object} properties
	 * @param {string} [properties.Ref] - Идентификатор города
	 * @param {string} [properties.FindByString] - Поиск по названию города
	 * @param {string} [properties.Page] - Номер страницы для отображения
	 * @param {Function} callback
	 */
	self.getWarehouses = function(properties, callback) {
		_request({
			url: data.url,
			data: JSON.stringify({
				apiKey: data.apiKey,
				modelName: 'AddressGeneral',
				calledMethod: 'getWarehouses',
				methodProperties: properties
			}),
			success: function (response) {
				if (response.success) {
					callback(response.data);
				} else {
					callback([]);
				}
			}
		});
	};

	self.getStatusDocuments = function(properties, callback) {
		_request({
			url: data.url,
			data: JSON.stringify({
				apiKey: data.apiKey,
				modelName: 'TrackingDocument',
				calledMethod: 'getStatusDocuments',
				methodProperties: properties
			}),
			success: function (response) {
				if (response.success) {
					callback(response.data);
				} else {
					callback([]);
				}
			}
		});
	};

	return self;
}
