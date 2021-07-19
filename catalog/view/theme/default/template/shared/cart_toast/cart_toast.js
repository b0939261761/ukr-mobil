window.cartToast = imageSrc => {
  const main = document.createElement('div');

  const close = () => {
    main.classList.remove('cart-toast--open');
    setTimeout(() => main.remove(), 500);
  };

  main.classList.add('cart-toast');

  const image = document.createElement('img');
  image.classList.add('cart-toast__image');
  image.src = imageSrc;
  main.appendChild(image);

  const text = document.createElement('div');
  text.classList.add('cart-toast__text');
  main.appendChild(text);
  text.appendChild(document.createTextNode('Товар додано до '));

  const link = document.createElement('a');
  link.classList.add('cart-toast__text-link');
  link.href = '/index.php?route=checkout';
  link.textContent = 'кошика';
  text.appendChild(link);

  main.appendChild(text);

  const header = document.createElement('div');
  header.classList.add('modal-window__header');
  text.appendChild(header);

  const btnClose = document.createElement('button');
  btnClose.classList.add('cart-toast__btn-close');
  btnClose.title = 'Закрити';
  btnClose.addEventListener('click', close);
  main.appendChild(btnClose);

  const xmlns = 'http://www.w3.org/2000/svg';
  const xlink = 'http://www.w3.org/1999/xlink';
  const btnCloseImg = document.createElementNS(xmlns, 'svg');
  btnCloseImg.classList.add('cart-toast__btn-close-icon');
  btnClose.appendChild(btnCloseImg);

  const btnCloseImgUse = document.createElementNS(xmlns, 'use');
  btnCloseImgUse.setAttributeNS(xlink, 'href', '/resourse/images/shared-sprite-icons.svg#icon-close');
  btnCloseImg.appendChild(btnCloseImgUse);

  document.body.appendChild(main);

  setTimeout(() => main.classList.add('cart-toast--open'), 10);
  setTimeout(close, 2000);
};
