window.modalWindowBuy = productId => {
  const form = window.ModalWindow.createFormElement();

  const phone = window.ModalWindow.createFormInputPhone({
    title: 'Телефон', name: 'phone', required: true, value: window.customerPhone
  });
  const formInputPhone = phone.element;
  form.appendChild(formInputPhone);

  const formBtn = window.ModalWindow.createFormBtn('Надіслати');
  form.appendChild(formBtn);

  const onSubmit = async evt => {
    evt.preventDefault();
    formBtn.disabled = true;

    const url = '/index.php?route=api/buy';
    const body = JSON.stringify({
      phone: phone.mask.unmaskedValue,
      productId
    });

    let errorText;
    try {
      const response = await fetch(url, { method: 'POST', body });
      if (response.ok) {
        const responseText = 'Дкуємо за покупку. Протягом 15 хвилин з вами зв\'яжуться для оформлення замовлення';
        const responseElement = window.ModalWindow.createResponse(responseText, 'success');
        evt.target.parentElement.appendChild(responseElement);
        evt.target.remove();
        return;
      }

      const responseText = await response.text();
      if (response.status === 400 && responseText === 'INVALID') {
        errorText = 'Помилка валідації';
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
  new window.ModalWindow('Купити в 1 клік', form);
};
