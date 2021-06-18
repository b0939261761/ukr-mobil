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

const addInputValidation = el => {
  el.addEventListener('invalid', () => setCheckoutInputInvalid(el));
  el.addEventListener('input', () => clearCheckoutInputInvalid(el));
};

[checkoutFirstName, checkoutLastName, checkoutEmail].forEach(addInputValidation);

// ---------------------------------

const phoneValue = checkoutPhone.value;
const checkoutPhoneMaskOptions = { mask: '+38(\\000)000-00-00', lazy: false };
const checkoutPhoneMask = IMask(checkoutPhone, checkoutPhoneMaskOptions);
checkoutPhoneMask.unmaskedValue = phoneValue;

const checkoutPhoneInvalidText = 'Неправильний номер телефону. Формат 38(0xx)xxx-xx-xx';

checkoutPhone.addEventListener('invalid', () => setCheckoutInputInvalid(checkoutPhone, checkoutPhoneInvalidText));
checkoutPhone.addEventListener('input', () => clearCheckoutInputInvalid(checkoutPhone));

// ---------------------------------

const onSubmitCheckoutForm = async evt => {
  evt.preventDefault();
  console.log(11111);

  // const url = '/index.php?route=tracking/getStatus';
  const body = JSON.stringify({
    firstName: checkoutFirstName.value,
    lastName: checkoutLastName.value,
    phone: `380${checkoutPhoneMask.unmaskedValue}`,
    value: checkoutEmail.value
  });

  console.log(body);
  // try {
  //   const response = await fetch(url, { method: 'POST', body });
  //   const order = await response.json();
  // } catch (err) {
  //   setTrackingError(err.message);
  // }
};

const checkoutForm = document.getElementById('checkoutForm');
checkoutForm.addEventListener('submit', onSubmitCheckoutForm);

// ---------------------------------
