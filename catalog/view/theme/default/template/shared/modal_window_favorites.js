window.modalWindowFavorites = async productId => {
  const createBtn = (isInsert, id) => {
    const onClickBtn = async ({ target }) => {
      target.disabled = true;
      const { type } = target.dataset;

      try {
        const url = `/index.php?route=api/${type}FavoriteProduct`;
        const body = JSON.stringify({ favoriteId: target.dataset.id, productId });

        const response = await fetch(url, { method: 'POST', body });
        if (response.ok) {
          target.parentElement.appendChild(createBtn(type === 'remove' ? 0 : 1, target.dataset.id));
          target.remove();
        } else {
          throw new Error(`${response.status} ${response.statusText}`);
        }
      } catch (err) {
        console.error(err);
      }
    };

    const itemBtn = document.createElement('button');
    itemBtn.classList.add('modal-window-favorites__item-btn');
    itemBtn.title = isInsert ? 'Видалити з обраного' : 'Додати до обраного';
    itemBtn.dataset.id = id;
    itemBtn.dataset.type = isInsert ? 'remove' : 'add';
    itemBtn.addEventListener('click', onClickBtn);

    const xmlns = 'http://www.w3.org/2000/svg';
    const xlink = 'http://www.w3.org/1999/xlink';
    const itemBtnImg = document.createElementNS(xmlns, 'svg');
    const iconName = isInsert ? 'minus' : 'plus';
    const itemBtnImgClass = `modal-window-favorites__item-btn-img--${iconName}`;
    itemBtnImg.classList.add('modal-window-favorites__item-btn-img', itemBtnImgClass);
    itemBtn.appendChild(itemBtnImg);

    const itemBtnImgUse = document.createElementNS(xmlns, 'use');
    itemBtnImgUse.setAttributeNS(xlink, 'href', `/resourse/images/shared-sprite-icons.svg#icon-${iconName}`);
    itemBtnImg.appendChild(itemBtnImgUse);

    itemBtn.appendChild(document.createTextNode(isInsert ? 'Видалити' : 'Додати'));
    return itemBtn;
  };

  const createItem = responseItem => {
    const item = document.createElement('li');
    item.classList.add('modal-window-favorites__item');

    const itemTitle = document.createElement('span');
    itemTitle.classList.add('modal-window-favorites__item-title');
    itemTitle.textContent = responseItem.name;
    item.appendChild(itemTitle);
    item.appendChild(createBtn(+responseItem.isInsert, responseItem.id));
    return item;
  };

  let main;
  let errorText;

  try {
    const url = '/index.php?route=api/getFavorites';
    const body = JSON.stringify({ productId });

    const response = await fetch(url, { method: 'POST', body });
    if (response.ok) {
      main = document.createElement('ul');
      main.classList.add('modal-window-favorites__list');
      const data = await response.json();

      if (data.length) {
        data.forEach(el => main.appendChild(createItem(el)));
      } else {
        main = document.createElement('div');
        main.classList.add('modal-window__response', 'modal-window__response--success');
        main.appendChild(document.createTextNode('Список обраного відсутній. '));
        const erroLink = document.createElement('a');
        erroLink.classList = 'modal-window-favorites__error-link';
        erroLink.textContent = 'Додати';
        erroLink.href = '/index.php?route=account#favorites';
        main.appendChild(erroLink);
        main.appendChild(document.createTextNode(' новий список'));
      }
    } else {
      const responseText = await response.text();
      if (response.status === 400 && responseText === 'INVALID') {
        errorText = 'Помилка валідації';
      } else {
        throw new Error(`${response.status} ${response.statusText}`);
      }
    }
  } catch (err) {
    errorText = `Помилка відправлення: ${err.message}`;
  }

  if (errorText) main = window.ModalWindow.createResponse(errorText, 'error');
  new window.ModalWindow('Виберіть список обраного', main);
};
