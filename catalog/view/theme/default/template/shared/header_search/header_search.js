const onSubmitHeaderSearch = evt => {
  evt.preventDefault();
  const search = evt.target.search.value.trim();
  if (!search) return;
  window.location = `/index.php?route=search&search=${search}`;
};

const headerSearch = document.getElementById('headerSearch');
headerSearch.addEventListener('submit', onSubmitHeaderSearch);

headerSearch.addEventListener('focusin', () => document.body.classList.add('body--header-search-focus'));
headerSearch.addEventListener('focusout', () => document.body.classList.remove('body--header-search-focus'));

// ============================================================================

const headerSearchInputInput = headerSearch.querySelector('.header-search__input-input');

const headerSearchCatalogs = headerSearch.querySelector('.header-search__catalogs');
const headerSearchProducts = headerSearch.querySelector('.header-search__products');

const headerSearchWrapperDrop = headerSearch.querySelector('.header-search__wrapper-drop');
const headerSearchDrop = headerSearch.querySelector('.header-search__drop');

const onResizeHeaderSearchWrapperDrop = () => {
  headerSearchWrapperDrop.style.setProperty('--height', `${headerSearchDrop.scrollHeight}px`);
};
const onResizeHDWrapperDropThrottle = window.shared.throttle(onResizeHeaderSearchWrapperDrop, 16);

// ============================================================================

const headerSearchClose = () => {
  document.body.classList.remove('body--header-search-open');
  window.removeEventListener('resize', onResizeHDWrapperDropThrottle);
};

const headerSearcBackdrop = headerSearch.querySelector('.header-search__backdrop');
headerSearcBackdrop.addEventListener('click', headerSearchClose);

// ============================================================================

const getHeaderSearchCatalogsItem = (search, catalog) => {
  const main = document.createElement('li');

  const item = document.createElement('a');
  item.href = catalog.categoryUrl;
  item.classList.add('header-search__catalogs-item');
  main.appendChild(item);

  const itemSearch = document.createElement('div');
  itemSearch.classList.add('header-search__catalogs-item-search');
  item.appendChild(itemSearch);

  const xmlns = 'http://www.w3.org/2000/svg';
  const xlink = 'http://www.w3.org/1999/xlink';
  const itemSearchImg = document.createElementNS(xmlns, 'svg');
  itemSearchImg.classList.add('header-search__catalogs-item-search-img');
  itemSearch.appendChild(itemSearchImg);

  const itemSearchImgUse = document.createElementNS(xmlns, 'use');
  itemSearchImgUse.setAttributeNS(xlink, 'href', '/resourse/images/shared-sprite-icons.svg#icon-search');
  itemSearchImg.appendChild(itemSearchImgUse);

  itemSearch.appendChild(document.createTextNode(search));

  const itemImgArrow = document.createElementNS(xmlns, 'svg');
  itemImgArrow.classList.add('header-search__catalogs-item-img-arrow');
  item.appendChild(itemImgArrow);

  const itemImgArrowUse = document.createElementNS(xmlns, 'use');
  itemImgArrowUse.setAttributeNS(xlink, 'href', '/resourse/images/shared-sprite-icons.svg#icon-arrow-right');
  itemImgArrow.appendChild(itemImgArrowUse);

  const itemTitle = document.createElement('span');
  itemTitle.classList.add('header-search__catalogs-item-title');
  itemTitle.textContent = 'В категорії';
  item.appendChild(itemTitle);

  const itemValue = document.createElement('span');
  itemValue.classList.add('header-search__catalogs-item-value');
  itemValue.textContent = catalog.categoryName;
  item.appendChild(itemValue);

  item.appendChild(itemImgArrow.cloneNode(true));

  const itemValue0 = document.createElement('span');
  itemValue0.classList.add('header-search__catalogs-item-value', 'header-search__catalogs-item-value--0');
  itemValue0.textContent = catalog.categoryName0;
  item.appendChild(itemValue0);

  return main;
};

// ============================================================================

const getHeaderSearchProductsItem = product => {
  const item = document.createElement('li');
  item.classList.add('header-search__products-item');

  const itemLink = document.createElement('a');
  itemLink.href = product.productUrl;
  itemLink.classList.add('header-search__products-item-link');
  item.appendChild(itemLink);

  const itemImg = document.createElement('img');
  itemImg.classList.add('header-search__products-item-link-img');
  itemImg.src = product.image;
  itemLink.appendChild(itemImg);

  const itemName = document.createElement('div');
  itemName.classList.add('header-search__products-item-link-name');
  itemName.textContent = product.productName;
  itemLink.appendChild(itemName);

  const itemPrice = document.createElement('div');
  itemPrice.classList.add('header-search__products-item-link-price');
  itemLink.appendChild(itemPrice);

  if (product.isPromotions) {
    const itemPriceOld = document.createElement('div');
    itemPriceOld.classList.add('header-search__products-item-link-price-old');
    itemPriceOld.textContent = `${product.priceOldUAH} грн`;
    itemPrice.appendChild(itemPriceOld);
  }

  const itemPriceCurrent = document.createElement('div');
  itemPriceCurrent.classList.add('header-search__products-item-link-price-current');
  itemPrice.appendChild(itemPriceCurrent);

  const itemPriceUAH = document.createElement('div');
  itemPriceUAH.classList.add('header-search__products-item-link-price-uah');
  itemPriceUAH.textContent = `${product.priceUAH} грн`;
  itemPriceCurrent.appendChild(itemPriceUAH);

  itemPriceCurrent.appendChild(document.createTextNode(`$ ${product.priceUSD}`));
  return item;
};

// ============================================================================

const onHeaderSearchOpen = async ({ target }) => {
  let data = [];
  target.value = target.value.replace(/\\/g, '');
  const search = target.value.trim();

  if (search.length > 1) {
    const url = '/index.php?route=shared/header_search/search';
    const body = JSON.stringify({ search });

    try {
      const response = await fetch(url, { method: 'POST', body });
      if (response.ok) {
        data = await response.json();
      } else {
        throw new Error(`${response.status} ${response.statusText}`);
      }
    } catch (err) {
      console.error(err);
    }
  }

  headerSearchCatalogs.innerHTML = '';
  headerSearchProducts.innerHTML = '';

  if (data.length) {
    const headerSearchDropInsert = el => {
      headerSearchCatalogs.appendChild(getHeaderSearchCatalogsItem(search, el));
      headerSearchProducts.appendChild(getHeaderSearchProductsItem(el));
    };

    setTimeout(() => {
      onResizeHeaderSearchWrapperDrop();
      document.body.classList.add('body--header-search-open');
    }, 1);

    data.forEach(headerSearchDropInsert);
    window.addEventListener('resize', onResizeHDWrapperDropThrottle);
  } else {
    headerSearchClose();
  }
};

const onHeaderSearchOpenDebounce = window.shared.debounce(onHeaderSearchOpen, 500);

headerSearchInputInput.addEventListener('input', onHeaderSearchOpenDebounce);
headerSearchInputInput.addEventListener('focus', onHeaderSearchOpen);
