const setHeaderCartCounter = count => {
  const headerCartCounter = document.getElementById('headerCartCounter');
  if (!headerCartCounter) return;
  if (count) {
    headerCartCounter.textContent = count;
    headerCartCounter.classList.remove('btn-header-action__counter--hidden');
  } else {
    headerCartCounter.classList.add('btn-header-action__counter--hidden');
  }
};

// ===================================================================

const initCart = () => {
  const cart = document.getElementById('cart');
  if (!cart) return;
  const onCartClose = () => document.body.classList.remove('body--cart-open');
  cart.addEventListener('click', evt => evt.target === evt.currentTarget && onCartClose());

  const cartClose = cart.querySelector('.cart__btn-close');
  cartClose.addEventListener('click', onCartClose);

  // ===================================================================

  const cartContainer = cart.querySelector('.cart__container');
  const cartWrapper = cart.querySelector('.cart__wrapper');

  const setCartContainerHeight = height => cartContainer.style.setProperty('--height', height);
  const onResizeCart = () => setCartContainerHeight(`${cartWrapper.scrollHeight}px`);
  const onResizeCartThrottle = window.shared.throttle(onResizeCart, 500);

  window.cartOpen = () => {
    document.body.classList.toggle('body--cart-open');
    if (document.body.classList.contains('body--cart-open')) {
      onResizeCartThrottle();
      window.addEventListener('resize', onResizeCartThrottle);
    } else {
      window.removeEventListener('resize', onResizeCartThrottle);
    }
  };

  // ===================================================================

  const cartClear = cart.querySelector('.cart__clear');
  if (cartClear) {
    const onClickCartClear = async evt => {
      evt.target.disabled = true;

      const url = '/index.php?route=shared/cart/clear';
      try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`${response.status} ${response.statusText}`);
        window.cartGet();
      } catch (err) {
        console.error(err.message);
      }

      setHeaderCartCounter(0);
      onCartClose();
      evt.target.disabled = false;
    };

    cartClear.addEventListener('click', onClickCartClear);
  }

  // ============================================================================

  const cartTotalSumUAH = document.getElementById('cartTotalSumUAH');
  const cartTotalSumUSD = document.getElementById('cartTotalSumUSD');

  const onClickCartItemRemove = async evt => {
    evt.target.disabled = true;
    const item = evt.target.closest('.cart__item');
    const body = JSON.stringify({ id: item.dataset.id });
    const url = '/index.php?route=shared/cart/remove';

    try {
      const response = await fetch(url, { method: 'POST', body });
      if (response.ok) {
        const responseJSON = await response.json();
        setHeaderCartCounter(responseJSON.totalQuantity);
        if (!responseJSON.totalQuantity) {
          onCartClose();
          window.cartGet();
          return;
        }
        cartTotalSumUAH.textContent = responseJSON.totalUAH;
        cartTotalSumUSD.textContent = responseJSON.totalUSD;
        item.remove();
        return;
      }
      throw new Error(`${response.status} ${response.statusText}`);
    } catch (err) {
      console.error(err.message);
    }
  };

  const cartItemRemoveList = cart.querySelectorAll('.cart__item-remove');
  if (cartItemRemoveList) cartItemRemoveList.forEach(el => el.addEventListener('click', onClickCartItemRemove));

  // ============================================================================

  const cartItemQuantityBtnList = cart.querySelectorAll('.cart__item-quantity-btn');
  if (cartItemQuantityBtnList) {
    const onClickCartItemQuntityBtn = async ({ target }) => {
      target.disabled = true;
      const item = target.closest('.cart__item');
      const btnMinus = item.querySelector('.cart__item-quantity-btn--minus');
      const btnPlus = item.querySelector('.cart__item-quantity-btn--plus');
      const isMinus = target.classList.contains('cart__item-quantity-btn--minus');

      const quantity = isMinus ? -1 : 1;
      const cartResponse = await window.cartAdd(item.dataset.id, quantity);

      cartTotalSumUAH.textContent = cartResponse.totalUAH;
      cartTotalSumUSD.textContent = cartResponse.totalUSD;
      btnMinus.disabled = cartResponse.quantity === 1;
      btnPlus.disabled = cartResponse.isMaxQuantity;
      item.querySelector('.cart__item-quantity-input').value = cartResponse.quantity;
    };

    cartItemQuantityBtnList.forEach(el => el.addEventListener('click', onClickCartItemQuntityBtn));
  }
};

initCart();

// ============================================================================

window.cartGet = async () => {
  const url = '/index.php?route=shared/cart/get';

  try {
    const response = await fetch(url);
    const responseText = await response.text();
    if (response.ok) {
      document.getElementById('cart').remove();
      document.body.insertAdjacentHTML('beforeend', responseText);
      initCart();
      return;
    }
    throw new Error(`${response.status} ${response.statusText}`);
  } catch (err) {
    console.error(err);
  }
};

// ============================================================================

window.cartAdd = async (id, quantity = 1) => {
  const url = '/index.php?route=shared/cart/add';
  const body = JSON.stringify({ id, quantity });

  let errorText;
  try {
    const response = await fetch(url, { method: 'POST', body });
    if (response.ok) {
      const responseJSON = await response.json();
      setHeaderCartCounter(responseJSON.totalQuantity);
      return responseJSON;
    }

    const responseText = await response.text();
    if (response.status === 400 && responseText === 'MAX_QUANTITY') {
      errorText = 'Недостатньо товарів на складі';
    } else {
      throw new Error(`${response.status} ${response.statusText}`);
    }
  } catch (err) {
    errorText = err.message;
  }

  new window.ModalWindow('Помилка', document.createTextNode(errorText));
  return false;
};
