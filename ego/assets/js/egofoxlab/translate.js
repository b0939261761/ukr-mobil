function Translate() {
	var self = this,
		langCode = 'en';

	/**
	 * Translate list
	 *
	 * @type {Object}
	 */
	var translateList = {
		ru: {
			callback: {
				form: {
					title: 'Заказать обратный звонок',
					btn_wait_call: 'Жду звонка',
					placeholder_phone_number: 'Номер телефона'
				},
				success: {
					popup: {
						title: 'Сообщение',
						body: 'Заявка на звонок отправлена. Ожидайте звонка.'
					}
				}
			},
			pages: {
				account: {
					account: {
						profile: {
							popup: {
								save: {
									success: {
										body: 'Профиль успешно обновлен'
									},
									error: {
										body: 'Ошибка обновление профиля'
									}
								}
							}
						}
					}
				},
				product: {
					product: {
						popup: {
							onlyAuthorizedUsers: 'Только авторизированные пользователи могут делать данную операцию.',
							addedToWishlist: 'Продукт добавлен! Мы известим Вас как только он появится у нас!'
						}
					}
				}
			},
			module: {
				newsletter: {
					popup: {
						success: {
							body: 'Вы успешно подписались на новости!'
						}
					}
				}
			},
			popup: {
				title: {
					info: 'Информация',
					warning: 'Внимание',
					error: 'Ошибка'
				}
			},
			auth: {
				login: 'Войти',
				register: 'Регистрация'
			}
		}
	};

	/**
	 * Set lang code
	 *
	 * @param langCode_
	 * @returns {Translate}
	 */
	self.setLangCode = function (langCode_) {
		langCode = langCode_;

		return self;
	};

	/**
	 * Return translation
	 *
	 * @param key
	 * @returns {*}
	 */
	self.get = function (key) {
		var keyList = key.split('.');

		if (empty(keyList)) {
			return null;
		}

		var text = translateList[langCode];

		for (var itemKey in keyList) {
			var item = keyList[itemKey];

			if (!empty(text[item])) {
				text = text[item];
			}
		}

		return text;
	};

	return self;
}

window.translate = new Translate();
