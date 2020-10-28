/**
 * Module Ego Newsletter component
 *
 * @extends BaseComponent
 * @param context
 * @returns {EgoNewsletterComponent}
 * @constructor
 */
function EgoNewsletterComponent(context) {
	BaseComponent.call(this, context);

	var self = this;
	var data = {};

	/**
	 * Init component
	 *
	 * @returns {CallbackComponent}
	 */
	self.init = function () {
		initActions();

		return self;
	};

	//region Auxiliary functions
	function initActions() {
		self.elementRef.find('.ego-btn-subscribe').click(function (e) {
			var eForm = $(e.target).closest('form');

			if (!eForm.get(0).checkValidity()) {
				return;
			}

			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();

			var formData = self.getFormData();

			_request({
				url: 'index.php?route=extension/module/ego_newsletter/subscribe',
				data: {
					transferData: formData
				},
				success: function (response) {
					if (response.success) {
						window.uiService.popup
							.setHeader(translate.get('popup.title.info'))
							.setBody(translate.get('module.newsletter.popup.success.body'))
							.hideFooter()
							.open();
					} else {
						window.uiService.popup
							.setHeader(translate.get('popup.title.error'))
							.setBody(response.message)
							.hideFooter()
							.open();
					}
				}
			});

			return false;
		});
	}
	//endregion

	return self;
}

EgoNewsletterComponent.prototype = Object.create(BaseComponent.prototype);
EgoNewsletterComponent.prototype.constructor = EgoNewsletterComponent;
