const productSlideBtnOpenOptionsList = document.querySelectorAll('.product-slide__options-btn-open');

if (productSlideBtnOpenOptionsList) {
  const onClickproductSlideBtnOpenOptions = ({ target }) => {
    const options = target.parentElement;
    const wrapperCharacteristicList = options.querySelector('.product-slide__wrapper-characteristic-list');
    const maxHeight = wrapperCharacteristicList.style.getPropertyValue('--max-height') ? null : `${options.offsetTop}px`;
    wrapperCharacteristicList.style.setProperty('--max-height', maxHeight);
    target.firstElementChild.classList.toggle('product-slide__options-btn-open-img--open');
  };

  productSlideBtnOpenOptionsList.forEach(el => el.addEventListener('click', onClickproductSlideBtnOpenOptions));
}
