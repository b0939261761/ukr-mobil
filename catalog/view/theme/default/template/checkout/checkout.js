/* eslint-disable no-return-assign */

const setCheckoutInputInvalid = (elem, text) => {
  if (text) elem.setCustomValidity(text);
  elem.classList.add('checkout-text-input__input--invalid');
};

const clearCheckoutInputInvalid = elem => {
  elem.setCustomValidity('');
  elem.classList.remove('checkout-text-input__input--invalid');
};

const checkoutFirstName = document.getElementById('checkoutFirstName');
const checkoutLastName = document.getElementById('checkoutLastName');
const checkoutPhone = document.getElementById('checkoutPhone');
const checkoutEmail = document.getElementById('checkoutEmail');

const checkoutBlockRegion = document.getElementById('checkoutBlockRegion');
const checkoutBlockCity = document.getElementById('checkoutBlockCity');
const checkoutBlockWarehouse = document.getElementById('checkoutBlockWarehouse');
const checkoutBlockAddress = document.getElementById('checkoutBlockAddress');
const checkoutRegion = checkoutBlockRegion.querySelector('select');
const checkoutCity = checkoutBlockCity.querySelector('select');
const checkoutWarehouse = checkoutBlockWarehouse.querySelector('select');
const checkoutAddress = checkoutBlockAddress.querySelector('input');

const addInputValidation = el => {
  el.addEventListener('invalid', () => setCheckoutInputInvalid(el));
  el.addEventListener('input', () => clearCheckoutInputInvalid(el));
};

[checkoutFirstName, checkoutLastName, checkoutEmail, checkoutAddress].forEach(addInputValidation);

const setCheckoutSelectInvalid = elem => elem.classList.add('custom-select__select--invalid');
const clearCheckoutSelectInvalid = elem => elem.classList.remove('custom-select__select--invalid');

const addSelectValidation = el => {
  el.addEventListener('invalid', () => setCheckoutSelectInvalid(el));
  el.addEventListener('input', () => clearCheckoutSelectInvalid(el));
};

[checkoutRegion, checkoutCity, checkoutWarehouse].forEach(addSelectValidation);

// ============================================================================

const phoneValue = checkoutPhone.value;
const checkoutPhoneMaskOptions = { mask: '+38(\\000)000-00-00', lazy: false };
const checkoutPhoneMask = IMask(checkoutPhone, checkoutPhoneMaskOptions);
checkoutPhoneMask.unmaskedValue = phoneValue;

const checkoutPhoneInvalidText = 'Неправильний номер телефону. Формат 38(0xx)xxx-xx-xx';

checkoutPhone.addEventListener('invalid', () => setCheckoutInputInvalid(checkoutPhone, checkoutPhoneInvalidText));
checkoutPhone.addEventListener('input', () => clearCheckoutInputInvalid(checkoutPhone));

// ============================================================================

window.newPostInit(checkoutBlockRegion, checkoutBlockCity, checkoutBlockWarehouse);

const checkoutPaymentDescriptionItemCashDelivery = document.getElementById('checkoutPaymentDescriptionItemCashDelivery');

const isCheckoutEmptyCart = () => !document.getElementById('checkoutCart');
const getCheckoutShippingMethod = () => document.querySelector('input[name="shipping-method"]:checked').value;
const getCheckoutPaymentMethod = () => document.querySelector('input[name="payment-method"]:checked').value;

// ============================================================================

const setCheckoutToPayUAH = () => {
  const checkoutCartTableTotalCommissionRow = document.getElementById('checkoutCartTableTotalCommissionRow');
  const checkoutTotalUAH = document.getElementById('checkoutTotalUAH');
  const checkoutCommissionUAH = document.getElementById('checkoutCommissionUAH');
  const checkoutToPay = document.getElementById('checkoutToPay');

  let toPay = +checkoutTotalUAH.textContent;
  checkoutCartTableTotalCommissionRow.classList.add('checkout__cart-table-total-row-hide');

  checkoutPaymentDescriptionItemCashDelivery.classList.add('checkout-payment-description-item--hidden');
  if (getCheckoutPaymentMethod() === 'Наложений платіж') {
    checkoutPaymentDescriptionItemCashDelivery.classList.remove('checkout-payment-description-item--hidden');
    checkoutCartTableTotalCommissionRow.classList.remove('checkout__cart-table-total-row-hide');
    toPay += +checkoutCommissionUAH.textContent;
  }
  checkoutToPay.textContent = toPay;
};

const checkoutCartUpdate = data => {
  if (isCheckoutEmptyCart()) return;

  const checkoutNotification = document.getElementById('checkoutNotification');
  const checkoutNotificationList = document.getElementById('checkoutNotificationList');
  checkoutNotification.classList.add('checkout__notification--hide');
  checkoutNotificationList.innerHTML = '';

  const addWarning = (el, city) => {
    el.classList.add('checkout-block-item--hide');
    if (el.querySelector('input[name="shipping-method"]:checked')) {
      document.getElementById('checkoutShippingMethodNewPost').checked = true;
    }

    checkoutNotification.classList.remove('checkout__notification--hide');
    const textWarning = document.createElement('span');
    textWarning.textContent = `Самовивіз з м. ${city} недоступний через недостатню кількість товару на складі`;
    checkoutNotificationList.appendChild(textWarning);
  };

  const checkoutItemShippingMethodStore1 = document.getElementById('checkoutItemShippingMethodStore1');
  const checkoutItemShippingMethodStore2 = document.getElementById('checkoutItemShippingMethodStore2');
  checkoutItemShippingMethodStore1.classList.remove('checkout-block-item--hide');
  checkoutItemShippingMethodStore2.classList.remove('checkout-block-item--hide');
  if (!+data.enoughQuantityStore1) addWarning(checkoutItemShippingMethodStore1, 'Рівне');
  if (!+data.enoughQuantityStore2) addWarning(checkoutItemShippingMethodStore2, 'Чернівці');

  // ==========================================================================

  const checkoutQuantity = document.getElementById('checkoutQuantity');
  const checkoutTotalUAH = document.getElementById('checkoutTotalUAH');
  const checkoutTotalUSD = document.getElementById('checkoutTotalUSD');
  const checkoutCommissionUAH = document.getElementById('checkoutCommissionUAH');

  checkoutQuantity.textContent = data.totalQuantity;
  checkoutTotalUAH.textContent = data.totalUAH;
  checkoutTotalUSD.textContent = data.totalUSD;
  checkoutCommissionUAH.textContent = data.commissionUAH;
  setCheckoutToPayUAH();
};

// ============================================================================

const checkoutCartItemRemoveList = document.querySelectorAll('.checkout__cart-item-remove');
if (checkoutCartItemRemoveList) {
  const onClickCheckoutCartItemRemove = async evt => {
    evt.target.disabled = true;
    const item = evt.target.closest('.checkout__cart-item');
    const body = JSON.stringify({ id: item.dataset.id });
    const url = '/index.php?route=shared/cart/remove';

    try {
      const response = await fetch(url, { method: 'POST', body });
      if (response.ok) {
        const responseJSON = await response.json();
        checkoutCartUpdate(responseJSON);

        if (!+responseJSON.totalQuantity) {
          document.getElementById('checkoutCart').remove();
          const checkoutCartEmpty = document.createElement('div');
          checkoutCartEmpty.classList.add('checkout__cart-empty');
          checkoutCartEmpty.textContent = 'Ваш кошик пустий!';
          document.getElementById('checkoutSectionCart').appendChild(checkoutCartEmpty);
          return;
        }

        item.remove();
        return;
      }
      throw new Error(`${response.status} ${response.statusText}`);
    } catch (err) {
      console.error(err.message);
    }
  };

  checkoutCartItemRemoveList.forEach(el => el.addEventListener('click', onClickCheckoutCartItemRemove));
}

// ============================================================================

const checkoutCartItemQuantityBtnList = document.querySelectorAll('.checkout__cart-item-quantity-btn');
if (checkoutCartItemQuantityBtnList) {
  const onClickCheckoutCartItemQuntityBtn = async ({ target }) => {
    target.disabled = true;
    const item = target.closest('.checkout__cart-item');
    const btnMinus = item.querySelector('.checkout__cart-item-quantity-btn--minus');
    const btnPlus = item.querySelector('.checkout__cart-item-quantity-btn--plus');
    const totalUAH = item.querySelector('.checkout__cart-item-price-uah-value');
    const totalUSD = item.querySelector('.checkout__cart-item-price-usd-value');

    const isMinus = target.classList.contains('checkout__cart-item-quantity-btn--minus');

    const quantity = isMinus ? -1 : 1;
    const response = await window.cartAdd(item.dataset.id, quantity);

    totalUAH.textContent = response.productTotalUAH;
    totalUSD.textContent = response.productTotalUSD;
    checkoutCartUpdate(response);
    btnMinus.disabled = response.quantity === 1;
    btnPlus.disabled = response.isMaxQuantity;
    item.querySelector('.checkout__cart-item-quantity-input').value = response.quantity;
  };

  checkoutCartItemQuantityBtnList.forEach(el => el.addEventListener('click', onClickCheckoutCartItemQuntityBtn));
}

// ============================================================================

const checkoutPaymentMethodList = document.querySelectorAll('[name="payment-method"]');
checkoutPaymentMethodList.forEach(el => el.addEventListener('change', setCheckoutToPayUAH));

// ============================================================================

const checkoutShippingDescriptionRivne = document.getElementById('checkoutShippingDescriptionRivne');
const checkoutShippingDescriptionChernivtsi = document.getElementById('checkoutShippingDescriptionChernivtsi');
const checkoutSectionPayment = document.getElementById('checkoutSectionPayment');

const onChangeShippingMethod = () => {
  checkoutShippingDescriptionRivne.classList.add('checkout-shipping-description-car--hidden');
  checkoutShippingDescriptionChernivtsi.classList.add('checkout-shipping-description-car--hidden');

  checkoutBlockRegion.classList.add('custom-select--hidden');
  checkoutBlockCity.classList.add('custom-select--hidden');
  checkoutBlockWarehouse.classList.add('custom-select--hidden');
  checkoutBlockAddress.classList.add('checkout-text-input--hidden');
  checkoutRegion.required = false;
  checkoutCity.required = false;
  checkoutWarehouse.required = false;
  checkoutAddress.required = false;

  checkoutSectionPayment.classList.remove('checkout__section--hide');

  switch (getCheckoutShippingMethod()) {
    case 'Самовивіз з м. Рівне':
      checkoutShippingDescriptionRivne.classList.remove('checkout-shipping-description-car--hidden');
      checkoutSectionPayment.classList.add('checkout__section--hide');
      break;
    case 'Самовивіз з м. Чернівці':
      checkoutShippingDescriptionRivne.classList.remove('checkout-shipping-description-car--hidden');
      checkoutSectionPayment.classList.add('checkout__section--hide');
      break;
    case 'Кур\'єрська доставка м. Рівне':
      checkoutSectionPayment.classList.remove('checkout__section--hide');
      checkoutBlockAddress.classList.remove('checkout-text-input--hidden');
      checkoutAddress.required = true;
      break;
    case 'Кур\'єрська доставка "Нова пошта"':
      checkoutBlockRegion.classList.remove('custom-select--hidden');
      checkoutBlockCity.classList.remove('custom-select--hidden');
      checkoutBlockAddress.classList.remove('checkout-text-input--hidden');
      checkoutRegion.required = false;
      checkoutCity.required = false;
      checkoutAddress.required = false;
      break;
    case 'Нова Пошта':
      checkoutBlockRegion.classList.remove('custom-select--hidden');
      checkoutBlockCity.classList.remove('custom-select--hidden');
      checkoutBlockWarehouse.classList.remove('custom-select--hidden');
      checkoutRegion.required = false;
      checkoutCity.required = false;
      checkoutWarehouse.required = false;
      break;
    default: break;
  }
};

const shippingMethodList = document.querySelectorAll('[name="shipping-method"]');
shippingMethodList.forEach(el => el.addEventListener('change', onChangeShippingMethod));

// ============================================================================

const goToLiqpay = ({ data, signature }) => {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'https://www.liqpay.ua/api/3/checkout';

  const elData = document.createElement('input');
  elData.type = 'hidden';
  elData.name = 'data';
  elData.value = data;
  form.appendChild(elData);

  const elSigature = document.createElement('input');
  elSigature.type = 'hidden';
  elSigature.name = 'signature';
  elSigature.value = signature;
  form.appendChild(elSigature);

  document.body.appendChild(form);
  form.submit();
};

// ============================================================================

const onSubmitCheckoutForm = async event => {
  event.preventDefault();
  let elemToScroll;
  let modalWindow;

  const bodyError = document.createElement('div');
  bodyError.classList.add('checkout__modal-window-body');

  const phone = checkoutPhoneMask.unmaskedValue;
  const isValidPhone = phone.length === 9;
  if (!isValidPhone) {
    const textError = document.createElement('div');
    textError.textContent = 'Неправильний телефон отримувача';
    bodyError.appendChild(textError);
    elemToScroll = checkoutPhone.closest('label');
  }

  const email = checkoutEmail.value.trim();
  const isValidEmail = window.shared.checkEmail(email);
  if (!isValidEmail) {
    const textError = document.createElement('div');
    textError.textContent = 'Неправильний email';
    bodyError.appendChild(textError);
    if (!elemToScroll) elemToScroll = checkoutEmail.closest('label');
  }

  const shippingMethod = getCheckoutShippingMethod();

  let shippingAddress = '';
  let regionId;
  let cityId;
  let warehouseId;

  const regionText = checkoutRegion.options[checkoutRegion.selectedIndex].text;
  const cityText = checkoutCity.options[checkoutCity.selectedIndex].text;
  const warehouseText = checkoutWarehouse.options[checkoutWarehouse.selectedIndex].text;

  let paymentMethod = '';
  switch (shippingMethod) {
    case 'Нова Пошта':
      shippingAddress = `${regionText} - ${cityText} - ${warehouseText}`;
      regionId = checkoutRegion.value;
      cityId = checkoutCity.value;
      warehouseId = checkoutWarehouse.value;
      paymentMethod = getCheckoutPaymentMethod();
      break;
    case 'Кур\'єрська доставка "Нова пошта"':
      shippingAddress = `${regionText} - ${cityText} - ${checkoutAddress.value}`;
      paymentMethod = getCheckoutPaymentMethod();
      break;
    case 'Кур\'єрська доставка м. Рівне': shippingAddress = checkoutAddress.value; break;
    default: break;
  }

  let comment = document.getElementById('checkoutComment').value;
  if (document.getElementById('checkoutNotCallback').checked) comment = `Не перезванивать.\n${comment}`;

  const body = JSON.stringify({
    firstName: checkoutFirstName.value,
    lastName: checkoutLastName.value,
    phone,
    isValidPhone,
    email,
    isValidEmail,
    shippingMethod,
    shippingAddress,
    paymentMethod,
    comment,
    region: regionId,
    city: cityId,
    warehouse: warehouseId
  });

  try {
    const response = await fetch('/index.php?route=checkout/order', { method: 'POST', body });
    const responseType = response.headers.get('Content-Type') === 'application/json' ? 'json' : 'text';
    const responseData = await response[responseType]();

    if (response.ok) {
      if (isValidPhone && isValidEmail) {
        if (paymentMethod === 'Оплата карткою онлайн (LiqPay)') return goToLiqpay(responseData);
        return window.location = responseData;
      }
    } else if (response.status === 400 && responseData === 'NOT_ENOUGH_QUANTITY') {
      const textError = document.createElement('div');
      textError.textContent = 'Товар на складе відсутній';
      bodyError.appendChild(textError);
      setTimeout(() => window.location = '/', 3000);
    } else if (response.status === 400 && responseData === 'USER_EXISTS') {
      const textError = document.createElement('div');
      textError.textContent = 'Користувач з данним email зареєстрований на сайті!';
      bodyError.appendChild(textError);

      const wrapperBtn = document.createElement('div');
      wrapperBtn.classList.add('checkout__modal-window-wrapper-btn');
      bodyError.appendChild(wrapperBtn);

      const formBtn = window.ModalWindow.createFormBtn('Ввійти в аккаунт');
      wrapperBtn.appendChild(formBtn);
      formBtn.addEventListener('click', () => {
        modalWindow.close();
        window.modalWindowLogin();
      });
    } else {
      throw new Error(`${response.status} ${response.statusText}`);
    }
  } catch (err) {
    const textError = document.createElement('div');
    textError.textContent = `Помилка відправлення: ${err.message}`;
    bodyError.appendChild(textError);
  }

  if (elemToScroll) elemToScroll.scrollIntoView({ behavior: 'smooth' });
  modalWindow = new window.ModalWindow('Помилка', bodyError);
};

const checkoutForm = document.getElementById('checkoutForm');
checkoutForm.addEventListener('submit', onSubmitCheckoutForm);
