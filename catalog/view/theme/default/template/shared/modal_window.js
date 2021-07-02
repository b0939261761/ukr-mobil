window.ModalWindow = class {
  constructor(titleText, bodyInner) {
    this.createModal(titleText, bodyInner);
    document.body.classList.add('body--window-modal-open');
    this.modalWindow.classList.add('modal-window--open');
    this.modalWindow.focus();
  }

  static createResponse(text, type) {
    const response = document.createElement('div');
    response.classList.add('modal-window__response', `modal-window__response--${type}`);
    response.innerHTML = text;
    return response;
  }

  static createFormElement() {
    const form = document.createElement('form');
    form.classList.add('modal-window__form');
    return form;
  }

  // static createFormCheckBox({
  //   title, name, required
  // }) {
  //   const formInput = document.createElement('label');
  //   formInput.classList.add('form-ckeckbox');

  //   const inputNameInput = document.createElement('input');
  //   inputNameInput.classList.add('form-ckeckbox__input');
  //   inputNameInput.type = 'checkbox';
  //   inputNameInput.name = name;
  //   if (required) inputNameInput.required = true;
  //   formInput.appendChild(inputNameInput);

  //   const xmlns = 'http://www.w3.org/2000/svg';
  //   const xlink = 'http://www.w3.org/1999/xlink';
  //   const checkImg = document.createElementNS(xmlns, 'svg');
  //   checkImg.classList.add('form-ckeckbox__check-img');
  //   formInput.appendChild(checkImg);

  //   const checkImgUse = document.createElementNS(xmlns, 'use');
  //   checkImgUse.setAttributeNS(xlink, 'href', '/resourse/images/shared-sprite-icons.svg#icon-check');
  //   checkImg.appendChild(checkImgUse);

  //   const formInputTitle = document.createElement('span');
  //   formInputTitle.classList.add('form-input__title1');
  //   formInput.appendChild(formInputTitle);
  //   formInputTitle.appendChild(document.createTextNode(title));
  //   return formInput;
  // }

  static createFormInput({
    title, name, placeholder, required, type, element = 'input', maxLength
  }) {
    const formInput = document.createElement('label');
    formInput.classList.add('form-input');

    const formInputTitle = document.createElement('div');
    formInputTitle.classList.add('form-input__title');
    formInput.appendChild(formInputTitle);

    formInputTitle.appendChild(document.createTextNode(title));

    if (required) {
      const inputNameTitleRequired = document.createElement('span');
      inputNameTitleRequired.classList.add('form-input__title-required');
      inputNameTitleRequired.textContent = '*';
      formInputTitle.appendChild(inputNameTitleRequired);
    }

    const inputNameInput = document.createElement(element);
    inputNameInput.classList.add(`form-input__${element}`);
    inputNameInput.name = name;
    if (placeholder) inputNameInput.placeholder = placeholder;
    if (required) inputNameInput.required = true;
    if (maxLength) inputNameInput.maxLength = maxLength;
    if (type) inputNameInput.type = type;
    formInput.appendChild(inputNameInput);

    return formInput;
  }

  static createFormInputPhone({
    title, name, required, value
  }) {
    const formInput = document.createElement('label');
    formInput.classList.add('form-input');

    const formInputTitle = document.createElement('div');
    formInputTitle.classList.add('form-input__title');
    formInput.appendChild(formInputTitle);

    formInputTitle.appendChild(document.createTextNode(title));

    if (required) {
      const inputNameTitleRequired = document.createElement('span');
      inputNameTitleRequired.classList.add('form-input__title-required');
      inputNameTitleRequired.textContent = '*';
      formInputTitle.appendChild(inputNameTitleRequired);
    }

    const inputNameInput = document.createElement('input');
    inputNameInput.classList.add('form-input__input');
    inputNameInput.name = name;
    inputNameInput.pattern = '\\+38\\(0\\d{2}\\)\\d{3}-\\d{2}-\\d{2}';
    if (required) inputNameInput.required = true;
    formInput.appendChild(inputNameInput);

    const mask = IMask(inputNameInput, { mask: '+38(\\000)000-00-00', lazy: false });
    const invalidText = 'Неправильний номер телефону. Формат 38(0xx)xxx-xx-xx';

    if (value) mask.unmaskedValue = value;

    inputNameInput.addEventListener('invalid', () => inputNameInput.setCustomValidity(invalidText));
    inputNameInput.addEventListener('input', () => inputNameInput.setCustomValidity(''));

    return { element: formInput, mask };
  }

  static createFormInputPassword({
    title, name, required, confirmElement, isConfirm = false, minLength, maxLength
  }) {
    const formInput = document.createElement('label');
    formInput.classList.add('form-input');

    const formInputTitle = document.createElement('div');
    formInputTitle.classList.add('form-input__title');
    formInput.appendChild(formInputTitle);

    formInputTitle.appendChild(document.createTextNode(title));

    if (required) {
      const inputNameTitleRequired = document.createElement('span');
      inputNameTitleRequired.classList.add('form-input__title-required');
      inputNameTitleRequired.textContent = '*';
      formInputTitle.appendChild(inputNameTitleRequired);
    }

    const inputWrapperInput = document.createElement('div');
    inputWrapperInput.classList.add('form-input__wrapper-input-password');
    formInput.appendChild(inputWrapperInput);

    const inputNameInput = document.createElement('input');
    inputNameInput.classList.add('form-input__input', 'form-input__input--password');
    inputNameInput.name = name;
    if (required) inputNameInput.required = true;
    if (minLength) inputNameInput.minLength = minLength;
    if (maxLength) inputNameInput.maxLength = maxLength;
    inputNameInput.type = 'password';
    inputWrapperInput.appendChild(inputNameInput);

    const onInput = ({ target }) => {
      if (confirmElement) {
        confirmElement.pattern = window.shared.escapeRegExp(target.value);
      }
      inputNameInput.setCustomValidity('');
    };

    const onInvalid = () => {
      if (isConfirm) inputNameInput.setCustomValidity('Паролі неспівпадають');
    };

    inputNameInput.addEventListener('input', onInput);
    inputNameInput.addEventListener('invalid', onInvalid);

    const btnVisible = document.createElement('button');
    btnVisible.classList.add('form-input__btn-visible-password');
    btnVisible.type = 'button';
    inputWrapperInput.appendChild(btnVisible);

    const onClickBtnVisible = () => {
      if (btnVisible.classList.contains('form-input__btn-visible-password--active')) {
        btnVisible.classList.remove('form-input__btn-visible-password--active');
        inputNameInput.type = 'password';
      } else {
        btnVisible.classList.add('form-input__btn-visible-password--active');
        inputNameInput.type = 'text';
      }
    };
    btnVisible.addEventListener('click', onClickBtnVisible);

    const xmlns = 'http://www.w3.org/2000/svg';
    const xlink = 'http://www.w3.org/1999/xlink';
    const btnVisiblePasswordImg = document.createElementNS(xmlns, 'svg');
    btnVisiblePasswordImg.classList.add('form-input__btn-visible-password-img');
    btnVisible.appendChild(btnVisiblePasswordImg);

    const btnVisiblePasswordImgUse = document.createElementNS(xmlns, 'use');
    btnVisiblePasswordImgUse.setAttributeNS(xlink, 'href', '/resourse/images/shared-sprite-icons.svg#icon-eye');
    btnVisiblePasswordImg.appendChild(btnVisiblePasswordImgUse);

    const line = document.createElement('span');
    line.classList.add('form-input__btn-visible-password-line');
    btnVisible.appendChild(line);

    return formInput;
  }

  static createFormBtn(title) {
    const formBtn = document.createElement('button');
    formBtn.classList.add('form-btn');
    formBtn.textContent = title;
    return formBtn;
  }

  static createFormHidden(name, value) {
    const formHidden = document.createElement('input');
    formHidden.type = 'hidden';
    formHidden.value = value;
    formHidden.name = name;
    return formHidden;
  }

  static createFormNote(text) {
    const formNote = document.createElement('div');
    formNote.classList.add('form-note');
    formNote.innerHTML = text;
    return formNote;
  }

  createModal(titleText, bodyInner) {
    const main = document.createElement('div');
    main.classList.add('modal-window');
    main.tabIndex = -1;
    main.addEventListener('click', this.onClickClose.bind(this));
    main.addEventListener('keydown', ({ key }) => key === 'Escape' && this.close());

    const content = document.createElement('div');
    content.classList.add('modal-window__content');
    main.appendChild(content);

    const header = document.createElement('div');
    header.classList.add('modal-window__header');
    content.appendChild(header);

    const btnClose = document.createElement('button');
    btnClose.classList.add('modal-window__btn-close');
    btnClose.title = 'Закрити (Esc)';
    btnClose.addEventListener('click', this.onClickClose.bind(this));
    header.appendChild(btnClose);

    const title = document.createElement('div');
    title.classList.add('modal-window__title');
    title.textContent = titleText;
    header.appendChild(title);

    const xmlns = 'http://www.w3.org/2000/svg';
    const xlink = 'http://www.w3.org/1999/xlink';
    const btnCloseImg = document.createElementNS(xmlns, 'svg');
    btnCloseImg.classList.add('modal-window__btn-close-img');
    btnClose.appendChild(btnCloseImg);

    const btnCloseImgUse = document.createElementNS(xmlns, 'use');
    btnCloseImgUse.setAttributeNS(xlink, 'href', '/resourse/images/shared-sprite-icons.svg#icon-close');
    btnCloseImg.appendChild(btnCloseImgUse);

    const body = document.createElement('div');
    body.classList.add('modal-window__body');
    body.appendChild(bodyInner);
    content.appendChild(body);

    document.body.appendChild(main);

    this.modalWindow = main;
    this.body = body;
  }

  onClickClose(evt) {
    if (evt.target === evt.currentTarget) this.close();
  }

  close() {
    document.body.classList.remove('body--window-modal-open');
    this.modalWindow.classList.remove('modal-window--open');
    setTimeout(() => this.modalWindow.remove(), 500);
  }
};
