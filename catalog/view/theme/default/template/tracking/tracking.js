/* eslint-disable no-return-assign */

const setTrackingInputInvalid = (elem, text) => {
  elem.setCustomValidity(text);
  elem.classList.add('tracking__input--invalid');
};

const clearTrackingInputInvalid = elem => {
  elem.setCustomValidity('');
  elem.classList.remove('tracking__input--invalid');
};

const trackingOrderId = document.getElementById('trackingOrderId');
const trackingOrderIdMaskOptions = { mask: '00000 0000' };
const trackingOrderIdMask = IMask(trackingOrderId, trackingOrderIdMaskOptions);

const trackingOrderIdInvalidText = 'Неправильний номер квитанції. Формат xxxxx xxxx';

trackingOrderId.addEventListener('invalid', () => setTrackingInputInvalid(trackingOrderId, trackingOrderIdInvalidText));
trackingOrderId.addEventListener('input', () => clearTrackingInputInvalid(trackingOrderId));

// ---------------------------------

const trackingPhone = document.getElementById('trackingPhone');
const trackingPhoneMaskOptions = { mask: '+38(\\000)000-00-00', lazy: false };
const trackingPhoneMask = IMask(trackingPhone, trackingPhoneMaskOptions);

const trackingPhoneInvalidText = 'Неправильний номер телефону. Формат 38(0xx)xxx-xx-xx';

trackingPhone.addEventListener('invalid', () => setTrackingInputInvalid(trackingPhone, trackingPhoneInvalidText));
trackingPhone.addEventListener('input', () => clearTrackingInputInvalid(trackingPhone));

// ---------------------------------

const trackingSuccess = document.getElementById('trackingSuccess');
const trackingError = document.getElementById('trackingError');

const setTrackingError = text => {
  trackingError.textContent = text;
  trackingError.classList.add('tracking__error--open');
};

const onSubmitTrackingForm = async evt => {
  evt.preventDefault();
  trackingSuccess.classList.remove('tracking__success--open');
  trackingError.classList.remove('tracking__error--open');

  const orderIdValue = trackingOrderIdMask.unmaskedValue;
  const phoveValue = `+380${trackingPhoneMask.unmaskedValue}`;
  const url = '/index.php?route=tracking/getStatus';
  const body = JSON.stringify({ orderId: orderIdValue, phone: phoveValue });
  try {
    const response = await fetch(url, { method: 'POST', body });
    const order = await response.json();
    if (order.states.length) {
      const nameList = document.querySelectorAll('.sevice-name');
      nameList.forEach(el => el.textContent = order.name);

      const price = (+order.price).toLocaleString('ru-RU');
      const priceList = document.querySelectorAll('.sevice-price');
      priceList.forEach(el => el.textContent = price);

      const createStatusItems = (state, trackingStatus) => {
        const trackingStatusItem = document.createElement('li');
        trackingStatusItem.classList.add('tracking__status-item');
        trackingStatusItem.appendChild(document.createTextNode(state.name));

        const trackingStatusDate = document.createElement('span');
        trackingStatusDate.classList.add('tracking__status-date');
        trackingStatusDate.appendChild(document.createTextNode(state.dateFormat));

        trackingStatusItem.appendChild(trackingStatusDate);
        trackingStatus.appendChild(trackingStatusItem);
      };

      const statusListAppend = el => {
        el.innerHTML = '';
        order.states.forEach(state => createStatusItems(state, el));
      };

      const statusList = document.querySelectorAll('.tracking__status');
      statusList.forEach(statusListAppend);
      trackingSuccess.classList.add('tracking__success--open');
    } else {
      setTrackingError('Замовлення не знайдене');
    }
  } catch (err) {
    setTrackingError(err.message);
  }
};

const trackingForm = document.getElementById('trackingForm');
trackingForm.addEventListener('submit', onSubmitTrackingForm);
