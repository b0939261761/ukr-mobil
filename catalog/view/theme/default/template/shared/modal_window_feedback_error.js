window.modalWindowFeedbackError = () => {
  const form = document.createElement('form');
  form.classList.add('modal-window__form');

  const formHiddenType = window.ModalWindow.createFormHidden('type', 'error');
  form.appendChild(formHiddenType);

  const formInputName = window.ModalWindow.createFormInput({
    title: 'Ім\'я', name: 'name', placeholder: 'Iван', required: true
  });
  form.appendChild(formInputName);

  const formInputPhone = window.ModalWindow.createFormInput({
    title: 'Телефон', name: 'phone', placeholder: '+38(___)___-__-__', required: true, type: 'tel'
  });
  form.appendChild(formInputPhone);

  const formInputEmail = window.ModalWindow.createFormInput({
    title: 'Email', name: 'email', placeholder: 'user@example.com', type: 'email'
  });
  form.appendChild(formInputEmail);

  const formInputDescription = window.ModalWindow.createFormInput({
    title: 'Суть проблеми', name: 'description', placeholder: 'Опис проблеми...', required: true, element: 'textarea'
  });
  form.appendChild(formInputDescription);

  const formBtn = window.ModalWindow.createFormBtn();
  form.appendChild(formBtn);

  const onSubmit = async evt => {
    evt.preventDefault();
    evt.target.querySelector('.form-btn').disabled = true;

    const body = JSON.stringify({
      type: form.type.value,
      name: form.name.value,
      phone: form.phone.value,
      email: form.email.value,
      description: form.description.value
    });

    let mwResponseText = 'Повідомлення успішно відправлено';
    let mwResponseClass = 'success';
    const url = '/index.php?route=api/feedback';
    try {
      const response = await fetch(url, { method: 'POST', body });
      if (!response.ok) throw new Error(`${response.status} ${response.statusText}`);
    } catch (err) {
      mwResponseText = `Помилка відправлення: ${err.message}`;
      mwResponseClass = 'error';
    }

    const mwResponse = document.createElement('div');
    mwResponse.classList.add('modal-window__response', `modal-window__response--${mwResponseClass}`);
    mwResponse.textContent = mwResponseText;

    const modalWindowBody = evt.target.closest('.modal-window__body');
    evt.target.remove();
    modalWindowBody.appendChild(mwResponse);
  };

  form.addEventListener('submit', onSubmit);

  new window.ModalWindow('Повідомлення про помилку', form);
};
