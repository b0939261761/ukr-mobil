window.modalWindowRecovery = () => {
  const form = window.ModalWindow.createFormElement();

  const title = document.createElement('div');
  title.classList.add('modal-window-recovery__title');
  title.textContent = 'Вкажіть email і ми надішлемо посилання для відновлення пароля';
  form.appendChild(title);

  const formInputEmail = window.ModalWindow.createFormInput({
    title: 'Email', name: 'email', placeholder: 'user@example.com', type: 'email', required: true
  });
  form.appendChild(formInputEmail);

  const formBtn = window.ModalWindow.createFormBtn('Надіслати');
  form.appendChild(formBtn);

  const onSubmit = async evt => {
    evt.preventDefault();
    formBtn.disabled = true;

    const body = JSON.stringify({ email: form.email.value });
    const url = '/index.php?route=api/recovery';

    let errorText;
    let hasError = false;
    try {
      const response = await fetch(url, { method: 'POST', body });
      if (response.ok) {
        const responseText = 'Посилання для відновлення паролю відіслане на email';
        const responseElement = window.ModalWindow.createResponse(responseText, 'success');
        evt.target.parentElement.appendChild(responseElement);
        evt.target.remove();
      } else {
        hasError = true;
        const responseText = await response.text();
        if (response.status === 400 && responseText === 'USER_EXISTS') {
          errorText = 'Email не знайдено, перевірте та спопробуйте ще раз!';
        } else {
          throw new Error(`${response.status} ${response.statusText}`);
        }
      }
    } catch (err) {
      errorText = `Помилка відправлення: ${err.message}`;
    }

    if (hasError) {
      const modalWindowResponse = form.querySelector('.modal-window__response');
      if (modalWindowResponse) modalWindowResponse.remove();
      const responseElement = window.ModalWindow.createResponse(errorText, 'error');
      form.appendChild(responseElement);
      formBtn.disabled = false;
    }
  };

  form.addEventListener('submit', onSubmit);
  new window.ModalWindow('Забули пароль?', form);
};
