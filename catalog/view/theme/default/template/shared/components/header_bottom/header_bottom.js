const headerLogin = document.getElementById('headerLogin');
if (headerLogin) headerLogin.addEventListener('click', () => window.modalWindowLogin());

// ==============================================

const headerCart = document.getElementById('headerCart');
headerCart.addEventListener('click', () => window.cartOpen());