window.addEventListener('load', () => {
  const baseUrl = '/index.php?route=checkout/cart/';

  const http = async ({ url, method = 'POST', body }) => {
    try {
      const response = await fetch(url, { method, body: JSON.stringify(body) });
      const contentType = response.headers.get('Content-Type');

      const responseData = contentType === 'application/json'
          ? await response.json()
          : await response.text();

      if (response.ok) return responseData;
      const error = new Error(`HTTP ${response.status}`);
      error.payload = responseData
      throw error;
    } catch (err) {
      throw err;
    };
  }

  const openPopup = (headerText, bodyText) => {
    uiService.popup
      .setHeader(headerText)
      .setBody(bodyText)
      .hideFooter()
      .open();
  }

  const checkEmail = email => {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  };

  // -------------------------------------------------------------

  const phone = document.getElementById('phone');
  const region = document.getElementById('npRegion');
  const city = document.getElementById('npCity');
  const warehouse = document.getElementById('npWarehouse');
  const address = document.getElementById('address');
  const tBodyCart = document.getElementById('checkoutCartProductsTable').tBodies[0];

  const getShippingMethod = () => document.querySelector('input[name="shipping-method"]:checked').value;
  const getPaymentMethod = () => document.querySelector('input[name="payment-method"]:checked').value;

  // -------------------------------------------------------------

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
  }

  // -------------------------------------------------------------

  const onFormOrder = async event => {
    event.preventDefault();
    const email = document.getElementById('email');
    let elemToScroll;
    let bodyText = '';

    const phoneValue = phone.value.replace(/\(|\)|-|\+|_/g, '');
    const isValidPhone = phoneValue.length === 12;
    if (!isValidPhone) {
      bodyText += '<br>Неверный телефон получателя';
      elemToScroll = phone.closest('.form-group');
    }

    const emailValue = email.value.trim();
    const isValidEmail = checkEmail(emailValue);
    if (!isValidEmail) {
      bodyText += '<br>Неверный email';
      if (!elemToScroll) elemToScroll = email.closest('.form-group');
    }

    const shippingMethod = getShippingMethod();

    let shippingAddress = '';
    let regionId;
    let cityId;
    let warehouseId;
    
    const regionText = region.options[region.selectedIndex].text;
    const cityText = city.options[city.selectedIndex].text;
    const warehouseText = warehouse.options[warehouse.selectedIndex].text;

    let paymentMethod = '';
    switch (shippingMethod) {
      case 'Новая Почта':
        shippingAddress = `${regionText} - ${cityText} - ${warehouseText}`;
        regionId = region.value;
        cityId = city.value;
        warehouseId = warehouse.value;
        paymentMethod = getPaymentMethod();
        break;
      case 'Курьерская доставка "Новая Почта"':
        shippingAddress = `${regionText} - ${cityText} - ${address.value}`;
        paymentMethod = getPaymentMethod();
        break;
      case 'Курьерская доставка г. Ровно': shippingAddress = address.value; break;
    }

    let comment = document.getElementById('comment').value;
    if (document.getElementById('notCallback').checked) comment = `Не перезванивать.\n${comment}`;

    const body = {
      firstName: document.getElementById('firstName').value,
      lastName: document.getElementById('lastName').value,
      phone: phoneValue,
      isValidPhone,
      email: emailValue,
      isValidEmail,
      shippingMethod,
      shippingAddress,
      paymentMethod,
      comment,
      region: regionId,
      city: cityId,
      warehouse: warehouseId
    };

    showSiteLoader(); // main.js

    try {
      const response = await http({ url: `${baseUrl}order`, body });
      if (isValidPhone && isValidEmail) {
        if (paymentMethod === 'Оплата картой онлайн (LiqPay)') return goToLiqpay(response);
        return window.location = response;
      }
    } catch (err) {
      if (err.payload === 'NOT_ENOUGH_QUANTITY') {
        bodyText += `<br>Товар на складе отсуствует`;
        setTimeout(() => window.location = '/', 3000);
      } else if (err.payload === 'USER_EXISTS') {
        bodyText += `
          <br>Пользователь с данным email зарегистрирован на сайте!
          <br>Войдите в аккаунт, пожалуйста!
          <br>
          <a
            style="margin-top: 15px;"
            class="btn-new btn-new--success"
            href="/index.php?route=account/login"
          >Перейти</a>
        `;

        return openPopup('Клиент уже зарегистрирован!', bodyText);
      }
    } finally {
      hideSiteLoader(); // main.js
    }

    if (elemToScroll) elemToScroll.scrollIntoView({ behavior: 'smooth' });
    openPopup('Ошибка', `Ошибка оформления заказа${bodyText}`);
  };

  const formOrder = document.getElementById('formOrder');
  formOrder.addEventListener('submit', onFormOrder);

  // -------------------------------------------------------------

  const onChangeShippingMethod = () => {
    const formRegion = document.getElementById('formRegion');
    const formCity = document.getElementById('formCity');
    const formWarehouse = document.getElementById('formWarehouse');
    const formAddress = document.getElementById('formAddress');

    formRegion.classList.add('hide');
    formCity.classList.add('hide');
    formWarehouse.classList.add('hide');
    formAddress.classList.add('hide');

    region.required = false;
    city.required = false;
    warehouse.required = false;
    address.required = false;

    const wrapperPaymentMethods = document.getElementById('wrapperPaymentMethods');
    const formNonCash = document.getElementById('formNonCash');
    const formCredit = document.getElementById('formCredit');
    const formPrivatBank = document.getElementById('formPrivatBank');
    const formCod = document.getElementById('formCod');

    wrapperPaymentMethods.classList.add('hide');
    formNonCash.classList.add('hide');
    if (formCredit) formCredit.classList.add('hide');
    formPrivatBank.classList.add('hide');
    formCod.classList.add('hide');

    const shippingMethod = getShippingMethod();

    if (shippingMethod === 'Новая Почта') {
      formRegion.classList.remove('hide');
      formCity.classList.remove('hide');
      formWarehouse.classList.remove('hide');

      region.required = true;
      city.required = true;
      warehouse.required = true;

      wrapperPaymentMethods.classList.remove('hide');
      formNonCash.classList.remove('hide');
      if (formCredit) formCredit.classList.remove('hide');
      formPrivatBank.classList.remove('hide');
      formCod.classList.remove('hide');
    } else if (shippingMethod === 'Курьерская доставка "Новая Почта"') {
      formRegion.classList.remove('hide');
      formCity.classList.remove('hide');
      formAddress.classList.remove('hide');

      region.required = true;
      city.required = true;
      address.required = true;

      wrapperPaymentMethods.classList.remove('hide');
      formNonCash.classList.remove('hide');
      if (formCredit) formCredit.classList.remove('hide');
      formPrivatBank.classList.remove('hide');
      formCod.classList.remove('hide');
    } else if (shippingMethod === 'Курьерская доставка г. Ровно') {
      formAddress.classList.remove('hide');
      address.required = true;
    }

    onChangePaymentMethod();
  }


  const shippingMethodList = document.querySelectorAll('[name="shipping-method"]');
  shippingMethodList.forEach(el => el.addEventListener('change', onChangeShippingMethod));

  // -------------------------------------------------------------

  const onChangePaymentMethod = async () => {
    const codAttention = document.getElementById('codAttention');
    codAttention.classList.add('hide');
    if (getPaymentMethod() === 'Наложеный платеж') codAttention.classList.remove('hide');
    updateCart();
  };

  const paymentMethodList = document.querySelectorAll('[name="payment-method"]');
  paymentMethodList.forEach(el => el.addEventListener('change', onChangePaymentMethod));

  // -------------------------------------------------------------

  const onFocusPhone = ({ target }) => {
    const index = target.value.indexOf('_');
    if (index !== -1) target.setSelectionRange(index, index);
  }

  phone.addEventListener('focus', onFocusPhone);
  phone.addEventListener('click', onFocusPhone);

  // -------------------------------------------------------------

  const appendProductCart = product => {
    const row = tBodyCart.insertRow();
    row.dataset.productId = product.product_id;
    row.dataset.cartId = product.cart_id;

    const cellImg = row.insertCell();
    const linkImg = document.createElement('a');
    const img = new Image(36);
    img.src = product.image;
    linkImg.href = product.href;
    linkImg.appendChild(img);
    cellImg.appendChild(linkImg);

    const cellName = row.insertCell();
    const linkName = document.createElement('a');
    linkName.href = product.link;
    linkName.appendChild(document.createTextNode(product.name));
    cellName.appendChild(linkName);

    const cellQuantity = row.insertCell();
    cellQuantity.classList.add('quantity');
    const cellQuantityDiv = document.createElement('div');

    const quantityInput = document.createElement('input');
    quantityInput.value = product.quantity;
    quantityInput.readOnly = true;
    cellQuantityDiv.appendChild(quantityInput);

    const btnSubtract = document.createElement('button');
    btnSubtract.classList.add('btn', 'btn-danger', 'reduce');
    btnSubtract.appendChild(document.createTextNode('-'));
    cellQuantityDiv.appendChild(btnSubtract);

    const btnAdd = document.createElement('button');
    btnAdd.classList.add('btn', 'btn-success', 'increase');
    btnAdd.appendChild(document.createTextNode('+'));
    cellQuantityDiv.appendChild(btnAdd);

    cellQuantity.appendChild(cellQuantityDiv);

    const cellPrice = row.insertCell();
    cellPrice.innerHTML = `${product.priceUAH}&nbsp;грн.<br>$${product.priceUSD}`;

    const cellTotal = row.insertCell();
    cellTotal.innerHTML = `${product.totalUAH}&nbsp;грн.<br>$${product.totalUSD}`;

    const cellDelete = row.insertCell();
    const deleteItem = document.createElement('button');
    deleteItem.classList.add('delete-item');
    deleteItem.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
        <path d="M296 432h16a8 8 0 0 0 8-8V152a8 8 0 0 0-8-8h-16a8 8 0 0 0-8 8v272a8 8 0 0 0 8 8zm-160 0h16a8 8 0 0 0 8-8V152a8 8 0 0 0-8-8h-16a8 8 0 0 0-8 8v272a8 8 0 0 0 8 8zM440 64H336l-33.6-44.8A48 48 0 0 0 264 0h-80a48 48 0 0 0-38.4 19.2L112 64H8a8 8 0 0 0-8 8v16a8 8 0 0 0 8 8h24v368a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V96h24a8 8 0 0 0 8-8V72a8 8 0 0 0-8-8zM171.2 38.4A16.1 16.1 0 0 1 184 32h80a16.1 16.1 0 0 1 12.8 6.4L296 64H152zM384 464a16 16 0 0 1-16 16H80a16 16 0 0 1-16-16V96h320zm-168-32h16a8 8 0 0 0 8-8V152a8 8 0 0 0-8-8h-16a8 8 0 0 0-8 8v272a8 8 0 0 0 8 8z"/>
      </svg>
    `;
    cellDelete.appendChild(deleteItem);
  }

  // -------------------------------------------------------------

  const updateCart = async () => {
    const response = await http({ url: `${baseUrl}products` });

    if (!response.products.length) {
      const url = new URL(`${window.location.protocol}//${window.location.hostname}/index.php`);
      url.searchParams.set('route', 'checkout/empty_cart');
      window.location = url.toString();
      return;
    }

    while (tBodyCart.rows.length) tBodyCart.deleteRow(0);
    response.products.forEach(appendProductCart);

    updateTotalCart(response);
    pickupAttention('Самовывоз из г. Черновцы', response.enoughQuantityStore1);
    pickupAttention('Самовывоз из г. Ровно', response.enoughQuantityStore2);
  }

  // -------------------------------------------------------------

  const updateTotalCart = order => {
    const total = document.getElementById('checkoutCartOrderTotal');
    const parentTotal = total.parentElement;
    const commisssion = document.getElementById('checkoutCartOrderCommisssion');
    const parentCommisssion = commisssion.parentElement;
    const toPay = document.getElementById('checkoutCartOrderToPay');

    parentTotal.classList.add('hide');
    parentCommisssion.classList.add('hide');

    let toPayContent = `${order.totalUAH}&nbsp;грн. ($${order.totalUSD})`;

    if (getPaymentMethod() === 'Наложеный платеж') {
      parentTotal.classList.remove('hide');
      parentCommisssion.classList.remove('hide');
      total.innerHTML = toPayContent;
      commisssion.innerHTML = `${order.commissionUAH}&nbsp;грн.`;
      toPayContent = `${order.toPayUAH}&nbsp;грн. ($${order.toPayUSD})`
    }

    toPay.innerHTML = toPayContent;
  }

  // -------------------------------------------------------------

  const pickupAttention = (shippingMethodValue, enoughQuantity) => {
    const selector = `[name="shipping-method"][value="${shippingMethodValue}"]`;
    const shippingMethod = document.querySelector(selector);
    const parentShippingMethod = shippingMethod.parentElement;
    const attention = document.querySelector(`[data-shipping-method="${shippingMethodValue}"]`);

    parentShippingMethod.classList.add('hide');
    attention.classList.remove('hide');

    if (enoughQuantity) {
      parentShippingMethod.classList.remove('hide');
      attention.classList.add('hide');
    } else {
      if (shippingMethod.checked) document.getElementById('novaPoshta').checked = true;
    }
  };

  // -------------------------------------------------------------

  const onClickCartItem = async evt => {
    const { target } = evt;
    if (target.tagName !== 'BUTTON') return;
    evt.preventDefault();
    const { cartId } = evt.target.closest('tr').dataset;

    if (target.classList.contains('delete-item')) {
      await http({ url: `${baseUrl}remove`, body: { cartId } });
    } else {
      const input = target.parentElement.querySelector('input');
      const quantity = +input.value + (target.classList.contains('increase') ? 1 : -1);
      if (quantity < 1) return;
      const response = await http({ url: `${baseUrl}edit`, body: { cartId, quantity } });
      if (!Boolean(response)) return openPopup('Сообщение', 'Слишком большое количество');
    }

    updateCart();
  };

  const checkoutCartProductsTable = document.getElementById('checkoutCartProductsTable');
  checkoutCartProductsTable.addEventListener('click', onClickCartItem);

  // -------------------------------------------------------------

  (async () => {
    phone.value = phone.value.replace(/^38/, '');
    const mask = ['3', '8','(', /\d/, /\d/, /\d/, ')', /\d/, /\d/, /\d/, '-', /\d/, /\d/, '-', /\d/, /\d/];
    vanillaTextMask.maskInput({ inputElement: phone, mask, showMask: true });
    onChangeShippingMethod();
  })();
});
