window.ModalWindow = class {
  constructor(titleText, bodyInner) {
    this.createModal(titleText, bodyInner);
    document.body.classList.add('body--window-modal-open');
    this.modalWindow.classList.add('modal-window--open');
    this.modalWindow.focus();

    setTimeout(() => {
    },
    50);
  }

  static createFormInput({
    title, name, placeholder, required, type, element = 'input'
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
    inputNameInput.placeholder = placeholder;
    if (required) inputNameInput.required = true;
    if (type) inputNameInput.type = type;
    formInput.appendChild(inputNameInput);

    return formInput;
  }

  static createFormBtn() {
    const formBtn = document.createElement('button');
    formBtn.classList.add('form-btn');
    formBtn.textContent = 'Надіслати повідомлення';
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
