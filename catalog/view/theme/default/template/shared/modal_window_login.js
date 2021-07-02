window.modalWindowLogin = () => {
  let modalWindow;

  const createFormRegister = () => {
    const form = window.ModalWindow.createFormElement();

    const formInputFirstName = window.ModalWindow.createFormInput({
      title: 'Ім\'я', name: 'firstName', placeholder: 'Iван', required: true, maxLength: 32
    });
    form.appendChild(formInputFirstName);

    const formInputLastName = window.ModalWindow.createFormInput({
      title: 'Прізвище', name: 'lastName', placeholder: 'Iванов', required: true, maxLength: 32
    });
    form.appendChild(formInputLastName);

    const phone = window.ModalWindow.createFormInputPhone({ title: 'Телефон', name: 'phone', required: true });
    const formInputPhone = phone.element;
    form.appendChild(formInputPhone);

    const formInputEmail = window.ModalWindow.createFormInput({
      title: 'Email', name: 'email', placeholder: 'user@example.com', type: 'email', required: true, maxLength: 96
    });
    form.appendChild(formInputEmail);

    const formInputPasswordConfirm = window.ModalWindow.createFormInputPassword({
      title: 'Повторіть пароль', name: 'confirm', isConfirm: true, required: true
    });

    const formInputPassword = window.ModalWindow.createFormInputPassword({
      title: 'Пароль',
      name: 'password',
      required: true,
      confirmElement: formInputPasswordConfirm.querySelector('.form-input__input'),
      minLength: 4,
      maxLength: 20
    });

    form.appendChild(formInputPassword);
    form.appendChild(formInputPasswordConfirm);

    const recaptchaScript = document.createElement('script');
    recaptchaScript.src = '//www.google.com/recaptcha/api.js?hl=ru';
    form.appendChild(recaptchaScript);

    const recaptcha = document.createElement('div');
    recaptcha.classList.add('g-recaptcha', 'modal-window-login__recaptcha');
    recaptcha.dataset.sitekey = '6Le97jQbAAAAAPRfGkHDjfUe6vPdmKlaNnmsTtCI';
    form.appendChild(recaptcha);

    const termToAgree = document.createElement('div');
    termToAgree.classList.add('modal-window-login__term-to-agree');
    termToAgree.appendChild(document.createTextNode('Реєструючись, ви погоджуєтеся з '));
    form.appendChild(termToAgree);

    const linkTermToAgree = document.createElement('a');
    linkTermToAgree.classList.add('modal-window-login__term-to-link');
    linkTermToAgree.textContent = 'угодою користувача';
    linkTermToAgree.href = '/agree_to_terms';
    linkTermToAgree.target = '_blank';
    termToAgree.appendChild(linkTermToAgree);

    const formBtn = window.ModalWindow.createFormBtn('Реєстрація');
    form.appendChild(formBtn);

    const onSubmit = async evt => {
      evt.preventDefault();

      formBtn.disabled = true;
      recaptcha.classList.remove('modal-window-login__recaptcha--error');

      const url = '/index.php?route=api/register';
      const body = JSON.stringify({
        firstName: form.firstName.value,
        lastName: form.lastName.value,
        email: form.email.value,
        phone: phone.mask.unmaskedValue,
        password: form.password.value,
        captcha: window.grecaptcha.getResponse()
      });

      let errorText;
      try {
        const response = await fetch(url, { method: 'POST', body });
        const responseText = await response.text();
        if (response.ok) {
          window.location = responseText;
          return;
        }
        if (response.status === 400 && responseText === 'INVALID') {
          errorText = 'Помилка валідації';
        } else if (response.status === 400 && responseText === 'CAPTCHA') {
          errorText = 'Помилка капчі';
          window.grecaptcha.reset();
          recaptcha.classList.add('modal-window-login__recaptcha--error');
        } else if (response.status === 400 && responseText === 'USER_EXISTS') {
          errorText = 'Данний email вже зареєстрований';
        } else {
          throw new Error(`${response.status} ${response.statusText}`);
        }
      } catch (err) {
        errorText = `Помилка відправлення: ${err.message}`;
      }

      const modalWindowResponse = form.querySelector('.modal-window__response');
      if (modalWindowResponse) modalWindowResponse.remove();
      const responseElement = window.ModalWindow.createResponse(errorText, 'error');
      form.appendChild(responseElement);
      formBtn.disabled = false;
    };

    form.addEventListener('submit', onSubmit);
    return form;
  };

  const createFormLogin = () => {
    const form = window.ModalWindow.createFormElement();

    const formInputEmail = window.ModalWindow.createFormInput({
      title: 'Email', name: 'email', placeholder: 'user@example.com', type: 'email', required: true
    });
    form.appendChild(formInputEmail);

    const formInputPassword = window.ModalWindow.createFormInputPassword({
      title: 'Пароль', name: 'password', required: true
    });
    form.appendChild(formInputPassword);

    const wrapperBtnForgotten = document.createElement('div');
    wrapperBtnForgotten.classList.add('modal-window-login__wrapper-btn-forgotten');
    form.appendChild(wrapperBtnForgotten);

    const btnForgotten = document.createElement('button');
    btnForgotten.type = 'button';
    btnForgotten.classList.add('modal-window-login__btn-forgotten');
    btnForgotten.textContent = 'Забули пароль? Відновити.';
    wrapperBtnForgotten.appendChild(btnForgotten);

    const onClickRecovery = () => {
      modalWindow.close();
      window.modalWindowRecovery();
    };
    btnForgotten.addEventListener('click', onClickRecovery);

    const formBtn = window.ModalWindow.createFormBtn('Авторизуватися');
    form.appendChild(formBtn);

    const onSubmit = async evt => {
      evt.preventDefault();
      formBtn.disabled = true;

      const url = '/index.php?route=api/login';
      const body = JSON.stringify({
        email: form.email.value,
        password: form.password.value
      });

      let errorText;
      try {
        const response = await fetch(url, { method: 'POST', body });
        if (response.ok) return window.location.reload();

        const responseText = await response.text();
        if (response.status === 400 && responseText === 'ATTEMPTS') {
          errorText = 'Ви перевищили максимальну кількість спроб авторизації.'
            + ' Будь ласка, спробуйте авторизації на сайті через 1 годину';
        } else if (response.status === 401) {
          errorText = 'Неправильний email або пароль';
        } else {
          throw new Error(`${response.status} ${response.statusText}`);
        }
      } catch (err) {
        errorText = `Помилка відправлення: ${err.message}`;
      }

      const modalWindowResponse = form.querySelector('.modal-window__response');
      if (modalWindowResponse) modalWindowResponse.remove();
      const responseElement = window.ModalWindow.createResponse(errorText, 'error');
      form.appendChild(responseElement);
      formBtn.disabled = false;
    };

    form.addEventListener('submit', onSubmit);
    return form;
  };

  const content = document.createElement('div');

  const tabs = document.createElement('div');
  tabs.classList.add('modal-window-login__tabs');
  content.appendChild(tabs);

  const btnRegister = document.createElement('button');
  btnRegister.type = 'button';
  btnRegister.classList.add('modal-window-login__tab');
  btnRegister.textContent = 'Реєстрація';
  tabs.appendChild(btnRegister);

  const btnLogin = document.createElement('button');
  btnLogin.type = 'button';
  btnLogin.classList.add('modal-window-login__tab', 'modal-window-login__tab--active');
  btnLogin.textContent = 'Авторизація';
  tabs.appendChild(btnLogin);

  let formRegister;
  const formLogin = createFormLogin();

  const onClickTab = ({ target }) => {
    if (target.classList.contains('modal-window-login__tab--active')) return;

    btnRegister.classList.toggle('modal-window-login__tab--active');
    btnLogin.classList.toggle('modal-window-login__tab--active');

    if (!formRegister) {
      formRegister = createFormRegister();
      content.appendChild(formRegister);
    } else {
      formRegister.classList.toggle('modal-window__form--hidden');
    }

    formLogin.classList.toggle('modal-window__form--hidden');
  };

  btnRegister.addEventListener('click', onClickTab);
  btnLogin.addEventListener('click', onClickTab);

  content.appendChild(formLogin);

  modalWindow = new window.ModalWindow('', content);
};
