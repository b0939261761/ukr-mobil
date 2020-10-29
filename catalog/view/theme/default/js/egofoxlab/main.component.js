/**
 * Account Account component
 *
 * @extends BaseComponent
 * @param context
 * @returns {MainComponent}
 * @constructor
 */
function MainComponent(context) {
	BaseComponent.call(this, context);

	var self = this;
	var isLogged = false;

	/**
	 * Init component
	 *
	 * @returns {MainComponent}
	 */
	self.init = function () {
		//	Init Actions
		initActions();
		//	Init button "To wishlist"/"Notify me when product appear on site"
		initToWishlist();
		//	Restrict input language, only Russian and Ukrainian
		restrictInputLanguage();
		//	Restrict input only numbers
		restrictInputNumbers();

		return self;
	};

	/**
	 * Set is current user logged into system
	 *
	 * @param {boolean} value
	 * @returns {MainComponent}
	 */
	self.isLogged = function (value) {
		isLogged = value === true;

		return self;
	};

	//region Auxiliary functions
	function initActions() {
		//region Download Price List
		self.elementRef.on('click', '.ego-download-price-list', function (e) {
			e.preventDefault();

			_request({
				url: 'index.php?route=ajax/ego/downloadExcelPriceList',
				success: function (response) {
					if (response.success) {
						var url = response.data.downloadUrl,
							fileName = response.data.fileName;

						var eDownloadLink = $('\
							<a \
								href="' + url + '"\
								download="' + fileName + '"\
								class="ego-download-price-list-link"\
							></a>\
							');

						$(e.target)
							.parent()
							.append(eDownloadLink);
						eDownloadLink
							.get(0)
							.click();
						eDownloadLink.remove();
					}
				}
			});
		});
		//endregion
	}
	/**
	 * Init button "To wishlist"/"Notify me when product appear on site"
	 */
	function initToWishlist() {
		$('body').on('click', '.to-wishlist, #btnSubscribeWishlist', function (e) {
			var target = $(e.target).closest('.to-wishlist');
			var productId = parseInt(target.attr('data-product-id'));

			_request({
				url: 'index.php?route=product/product/addToWishlist',
				data: {
					transferData: {
						productId: productId
					}
				},
				success: function (response) {
					//	Only authorized users
					if (response.code === 401) {
						window.uiService.popup
							.setHeader('Ошибка')
							.setBody('\
								<div class="add-to-wishlist">\
									<div class="add-to-wishlist__message">\
										' + 'Только авторизированные пользователи могут делать данную операцию.' + '\
									</div>\
									<div class="login-or-register">\
										<div>\
											<a href="/index.php?route=account/login">\
												' + 'Войти' + '\
											</a>\
										</div>\
										<div>\
											<a href="/index.php?route=account/register">\
												' + 'Регистрация' + '\
											</a>\
										</div>\
									</div>\
								</div>\
							')
							.hideFooter()
							.open();

						return;
					}

					//	Another errors
					if (response.code !== 200) {
						console.error(response.message);
						return;
					}

					//	Notify user about success
					window.uiService.popup
						.setHeader('Информация')
						.setBody('Продукт добавлен! Мы известим Вас как только он появится у нас!')
						.hideFooter()
						.open();

					//	Remove button
					target.remove();
				}
			});
		});
	}

	/**
	 * Restrict input language, only Russian and Ukrainian
	 */
	function restrictInputLanguage() {
		$('body').on('keydown keyup', '.restrict-language', function (e) {
			var eTarget = $(e.target);

			eTarget.val(eTarget.val().replace(/[^абвгдеёжзийклмнопрстуфхцчшщъыьэюяіїґє\- 0-9]/ig, ''));
		});
	}

	/**
	 * Restrict input only numbers
	 */
	function restrictInputNumbers() {
		$('body').on('keydown keyup', '.restrict-numbers', function (e) {
			var eTarget = $(e.target);

			eTarget.val(eTarget.val().replace(/[^+-\.,0-9]/ig, ''));
		});
	}
	//endregion

	return self;
}

MainComponent.prototype = Object.create(BaseComponent.prototype);
MainComponent.prototype.constructor = MainComponent;
