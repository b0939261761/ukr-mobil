window.modalWindowSubscribe = () => {
  const form = window.ModalWindow.createFormElement();

  const formInputEmail = window.ModalWindow.createFormInput({
    title: 'Email', name: 'email', placeholder: 'user@example.com', type: 'email', required: true
  });
  form.appendChild(formInputEmail);

  const formBtn = window.ModalWindow.createFormBtn('Надіслати');
  form.appendChild(formBtn);

  const onSubmit = async evt => {
    evt.preventDefault();
    formBtn.disabled = true;

    const url = '/index.php?route=api/subscribe';
    const body = JSON.stringify({ email: form.email.value });

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

  new window.ModalWindow('Підписатись на новини', form);
};
