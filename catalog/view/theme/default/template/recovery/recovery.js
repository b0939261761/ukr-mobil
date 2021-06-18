const pageRecoveryForm = document.getElementById('pageRecoveryForm');

if (pageRecoveryForm) {
  const pageRecoveryPassword = document.getElementById('pageRecoveryPassword');
  const pageRecoveryConfirm = document.getElementById('pageRecoveryConfirm');
  const pageRecoverySubmit = document.getElementById('pageRecoverySubmit');
  const pageRecoveryResponseError = document.getElementById('pageRecoveryError');

  const customInputBtnVisiblePasswordList = document.querySelectorAll('.custom-input__btn-visible-password');

  const onClickCustomInputBtnVisiblePassword = ({ target }) => {
    target.classList.toggle('custom-input__btn-visible-password--active');
    const isActive = target.classList.contains('custom-input__btn-visible-password--active');
    target.previousElementSibling.type = isActive ? 'text' : 'password';
  };
  customInputBtnVisiblePasswordList.forEach(el => el.addEventListener('click', onClickCustomInputBtnVisiblePassword));

  const setCheckoutInputInvalid = (elem, text) => {
    if (text) elem.setCustomValidity(text);
    elem.classList.add('custom-input__input--invalid');
  };

  const clearCheckoutInputInvalid = elem => {
    elem.setCustomValidity('');
    elem.classList.remove('custom-input__input--invalid');
  };

  pageRecoveryPassword.addEventListener('invalid', () => {
    setCheckoutInputInvalid(pageRecoveryPassword, 'Пароль повинен бути від 4 до 20 символів');
  });

  const onInputPageRecoveryPassword = ({ target }) => {
    pageRecoveryConfirm.pattern = window.shared.escapeRegExp(target.value);
    clearCheckoutInputInvalid(pageRecoveryPassword);
  };
  pageRecoveryPassword.addEventListener('input', onInputPageRecoveryPassword);

  pageRecoveryConfirm.addEventListener('invalid', () => {
    setCheckoutInputInvalid(pageRecoveryConfirm, 'Паролі неспівпадають');
  });

  pageRecoveryConfirm.addEventListener('input', () => clearCheckoutInputInvalid(pageRecoveryConfirm));

  const onSubmitPageRecoveryForm = async evt => {
    evt.preventDefault();
    pageRecoverySubmit.disabled = true;

    const url = '/index.php?route=recovery/recovery';
    const body = JSON.stringify({ password: pageRecoveryPassword.value });

    try {
      const response = await fetch(url, { method: 'POST', body });
      if (response.ok) {
        window.location = await response.text();
        return;
      }
    } catch (err) {}

    pageRecoveryResponseError.classList.remove('page-recovery__response-error--hide');
    pageRecoverySubmit.disabled = false;
  };

  pageRecoveryForm.addEventListener('submit', onSubmitPageRecoveryForm);
}
