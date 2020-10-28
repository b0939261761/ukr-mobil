/**
 * Callback component.
 *
 * Create callback form for send Callback mail to administrator
 *
 * @extends BaseComponent
 * @param context
 * @returns {CallbackComponent}
 * @constructor
 */
function CallbackComponent(context) {
	BaseComponent.call(this, context);

	var self = this;
	var data = {
		style: '\
			<style>\
				\
				.ego-callback-trigger-container {\
					position: fixed;\
					bottom: 10px;\
					left: 10px;\
					width: 50px;\
					height: 50px;\
					border-radius: 100%;\
					background-color: rgba(237,134,63,1);\
					background-repeat: no-repeat;\
					background-position: center;\
					background-size: 24px;\
					background-image: url(/image/ego/phone-call.png);\
					box-shadow: 0 0 16px rgba(237, 134, 63, 0.5);\
					cursor: pointer;\
					z-index: 999;\
				}\
				\
				.ego-callback-form-container {\
					position: fixed;\
					left: 0;\
					bottom: 0;\
					display: flex;\
					align-items: center;\
					justify-content: center;\
					width: 100%;\
					height: 100%;\
					background-color: rgba(23,23,30,0.3);\
					z-index: 20000 !important;\
				}\
				\
				.ego-callback-form-container > .wrap {\
					position: relative;\
					width: 100%;\
					max-width: 800px;\
					padding: 50px;\
					background-color: rgba(237,134,63,0.96)\
				}\
				\
				.ego-callback-form-container > .wrap .title {\
					margin-bottom: 30px;\
					font-size: 30px;\
					color: #fff;\
				}\
				\
				.ego-callback-form-container > .wrap .close-icon {\
					position: absolute;\
					top: 10px;\
					right: 10px;\
					font-size: 20px;\
					color: #fff;\
					cursor: pointer;\
				}\
				\
				.ego-callback-form-container > .wrap form {\
					display: flex;\
				}\
				\
				.ego-callback-form-container > .wrap form input {\
					width: 275px !important;\
					height: 54px !important;\
					padding: 13px 20px;\
					border: 0;\
					font-weight: 300;\
					font-size: 20px;\
				}\
				\
				.ego-callback-form-container > .wrap form button {\
					width: 250px;\
					height: 54px;\
					border: 0;\
					background-color: #d0632b;\
					font-size: 20px;\
					font-weight: 300;\
					color: #fff;\
				}\
				\
				.ego-callback-form-container > .wrap form button:hover,\
				.ego-callback-form-container > .wrap form button:focus {\
					background-color: #c85f29;\
				}\
				\
				.ego-callback-form-container > .wrap form button:active {\
					background-color: #ae5324;\
				}\
				\
				@media (max-width: 768px) {\
					.ego-callback-form-container > .wrap form {\
						flex-wrap: wrap;\
					}\
					\
					.ego-callback-form-container > .wrap form input {\
						width: 100% !important;\
					}\
					\
					.ego-callback-form-container > .wrap form button {\
						width: 100%;\
					}\
				}\
				</style>\
			',
		template: '\
			<div>\
				<div class="ego-callback-trigger-container"></div>\
				<div class="ego-callback-form-container">\
					<div class="wrap">\
						<div class="title">\
							[title]\
						</div>\
						<div class="close-icon">\
							&#10005;\
						</div>\
						<div class="form-container">\
							<form>\
								<input\
									type="text"\
									name="ego-form-field-callback-phone-number"\
									placeholder="[placeholder_phone_number]" \
									required\
									>\
								<button>[btn_wait_call]</button>\
							</form>\
						</div>\
					</div>\
				</div>\
			</div>\
			'
	};

	/**
	 * Init component
	 *
	 * @returns {CallbackComponent}
	 */
	self.init = function () {
		initForm();
		initActions();

		self.closeModal();

		return self;
	};

	/**
	 * Open modal
	 *
	 * @returns {CallbackComponent}
	 */
	self.openModal = function () {
		self.elementRef
			.find('.ego-callback-form-container')
			.removeClass('hide');

		return self;
	};

	/**
	 * Close modal
	 *
	 * @returns {CallbackComponent}
	 */
	self.closeModal = function () {
		self.elementRef
			.find('.ego-callback-form-container')
			.addClass('hide');

		return self;
	};

	//region Auxiliary functions
	/**
	 * Initialize form
	 */
	function initForm() {
		var eBody = $('body');

		//	Set text
		var template = data.template;
		template = template
			.replace('[title]', translate.get('callback.form.title'))
			.replace('[placeholder_phone_number]', translate.get('callback.form.placeholder_phone_number'))
			.replace('[btn_wait_call]', translate.get('callback.form.btn_wait_call'));

		self.elementRef = $(template);

		eBody.append($(data.style));
		eBody.append(self.elementRef);
	}

	/**
	 * Init actions
	 */
	function initActions() {
		//region Wait call
		self.elementRef.find('form button').click(function (e) {
			var eForm = $(e.target).closest('form');

			if (!eForm.get(0).checkValidity()) {
				return;
			}

			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();

			var formData = self.getFormData();

			_request({
				url: 'index.php?route=ajax/index/newSendCallback',
				data: {
					transferData: formData
				},
				success: function (response) {
					if (response.success) {
						self.closeModal();

						uiService.popup
							.setHeader(translate.get('callback.success.popup.title'))
							.setBody(translate.get('callback.success.popup.body'))
							.hideFooter()
							.open();
					}
				}
			});
		});
		//endregion

		//region Open Modal
		/**
		 * Open modal by clicking on trigger
		 */
		self.elementRef.find('.ego-callback-trigger-container').click(function () {
			self.openModal();
		});

		$('body').on('open.ego-callback', function () {
			self.openModal();
		});
		//endregion

		//region Close out of box
		self.elementRef
			.find('.ego-callback-form-container, .ego-callback-form-container .close-icon')
			.click(function () {
				self.closeModal();
			});

		$('.ego-callback-form-container > .wrap').click(function (e) {
			e.stopPropagation();
		});
		//endregion
	}
	//endregion

	return self;
}

CallbackComponent.prototype = Object.create(BaseComponent.prototype);
CallbackComponent.prototype.constructor = CallbackComponent;
