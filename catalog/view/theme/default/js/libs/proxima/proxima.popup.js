/**
 * Proxima popup
 *
 * @returns {EgoProximaPopup}
 * @constructor
 */
function EgoProximaPopup() {
	var $ = jQuery,
		self = this;
	var data = {
		/**
		 * Is open popup or not
		 */
		open: false,
		/**
		 * Popup template
		 */
		template: '\
			<div class="ego-suite-popup-proxima">\
				<div class="ego-suite-popup-wrap">\
					<div class="ego-suite-popup-header">\
						<div class="ego-suite-popup-header-content">\
							[header]\
						</div>\
						<div class="ego-suite-popup-close-icon">\
							&#10005;\
						</div>\
					</div>\
					<div class="ego-suite-popup-body">\
						<div class="ego-suite-popup-body-content">\
							[body]\
						</div>\
					</div>\
					<div class="ego-suite-popup-footer [footer-hide]">\
						<div class="ego-suite-popup-footer-content">\
							[footer]\
						</div>\
					</div>\
				</div>\
			</div>\
		',
		/**
		 * Header template
		 */
		headerTpl: '',
		/**
		 * Body template
		 */
		bodyTpl: '',
		/**
		 * Footer template
		 */
		footerTpl: '',
		/**
		 * Container for popup
		 */
		eContainer: $('body'),
		/**
		 * Popup element
		 */
		ePopup: null,
		/**
		 * Hide footer if TRUE
		 */
		hideFooter: false
	};

	/**
	 * Set header template
	 *
	 * @param template
	 * @returns {EgoProximaPopup}
	 */
	self.setHeader = function (template) {
		data.headerTpl = template;

		return self;
	};

	/**
	 * Set body template
	 *
	 * @param template
	 * @returns {EgoProximaPopup}
	 */
	self.setBody = function (template) {
		data.bodyTpl = template;

		return self;
	};

	/**
	 * Set footer template
	 *
	 * @param template
	 * @returns {EgoProximaPopup}
	 */
	self.setFooter = function (template) {
		data.footerTpl = template;

		return self;
	};

	/**
	 * Set popup container
	 *
	 * @param eContainer
	 * @returns {EgoProximaPopup}
	 */
	self.setContainer = function (eContainer) {
		data.eContainer = $(eContainer);

		return self;
	};

	/**
	 * Hide footer
	 *
	 * @returns {EgoProximaPopup}
	 */
	self.hideFooter = function () {
		data.hideFooter = true;

		return self;
	};

	/**
	 * Show footer
	 *
	 * @returns {EgoProximaPopup}
	 */
	self.showFooter = function () {
		data.hideFooter = false;

		return self;
	};

	/**
	 * Open popup
	 *
	 * @returns {EgoProximaPopup}
	 */
	self.open = function () {
		data.open = true;

		var template = data.template
			.replace('[header]', data.headerTpl)
			.replace('[body]', data.bodyTpl)
			.replace('[footer]', data.footerTpl);

		//	Hide footer
		if (data.hideFooter) {
			template = template.replace('[footer-hide]', 'hide');
		} else {
			template = template.replace('[footer-hide]', '');
		}

		data.ePopup = $(template);

		//	Add popup to DOM
		data.eContainer.append(data.ePopup);

		//	Init listeners
		initListeners();

		return self;
	};

	/**
	 * Close popup
	 *
	 * @returns {EgoProximaPopup}
	 */
	self.close = function () {
		if (!empty(data.ePopup)) {
			data.ePopup.remove();
		}

		return self;
	};

	//region Auxiliary functions
	/**
	 * Initialize event listeners
	 */
	function initListeners() {
		//	Close popup clicking on close icon
		data.ePopup.find('.ego-suite-popup-close-icon').click(function () {
			self.close();
		});

		//region Close on clicking out of popup content block
		$('.ego-suite-popup-proxima').click(function () {
			self.close();
		});

		$('.ego-suite-popup-wrap').click(function (e) {
			e.stopPropagation();
		});
		//endregion
	}
	//endregion

	return self;
}
