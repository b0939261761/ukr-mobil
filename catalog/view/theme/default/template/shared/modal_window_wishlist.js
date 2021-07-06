window.modalWindowWishlist = async productId => {
  let textResult;
  let hasError = true;

  try {
    const url = '/index.php?route=api/wishlist';
    const body = JSON.stringify({ productId });

    const response = await fetch(url, { method: 'POST', body });
    if (response.ok) {
      textResult = 'Товар додано! Надішлемо email, при появі товара на складі!';
      hasError = false;
    } else {
      const responseText = await response.text();
      if (response.status === 400 && responseText === 'INVALID') {
        textResult = 'Помилка валідації';
      } else {
        throw new Error(`${response.status} ${response.statusText}`);
      }
    }
  } catch (err) {
    textResult = `Помилка відправлення: ${err.message}`;
  }

  const main = window.ModalWindow.createResponse(textResult, hasError ? 'error' : 'success');
  new window.ModalWindow('Повідомити про наявність', main);
};
