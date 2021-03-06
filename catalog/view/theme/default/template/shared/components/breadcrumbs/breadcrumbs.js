const addBreadcrumbsModileItem = (title, link) => {
  const item = document.createElement('li');
  item.classList.add('modal-window-breadcrumbs__item');

  if (link) {
    item.classList.add('modal-window-breadcrumbs__item--link');

    const linkItem = document.createElement('a');
    linkItem.classList.add('modal-window-breadcrumbs__link');
    linkItem.textContent = title;
    linkItem.href = link;

    item.appendChild(linkItem);
  } else {
    item.classList.add('modal-window-breadcrumbs__item--active');
    item.textContent = title;
  }

  return item;
};

const onClickBreadcrumbsBtnMobile = ({ target }) => {
  const breadcrumbs = target.closest('.breadcrumbs');

  const mwBreadcrumbs = document.createElement('ul');
  mwBreadcrumbs.classList.add('modal-window-breadcrumbs');

  mwBreadcrumbs.appendChild(addBreadcrumbsModileItem('Головна', '/'));

  breadcrumbs
    .querySelectorAll('.breadcrumbs__item--desktop > .breadcrumbs__link')
    .forEach(el => mwBreadcrumbs.appendChild(addBreadcrumbsModileItem(el.textContent, el.href)));

  const activeText = breadcrumbs.querySelector('.breadcrumbs__item--active').textContent;

  mwBreadcrumbs.appendChild(addBreadcrumbsModileItem(activeText));

  new window.ModalWindow('', mwBreadcrumbs);
};

const breadcrumbsBtnMobile = document.getElementById('breadcrumbsBtnMobile');
if (breadcrumbsBtnMobile) breadcrumbsBtnMobile.addEventListener('click', onClickBreadcrumbsBtnMobile);
