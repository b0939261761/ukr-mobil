const headerLogin = document.getElementById('headerLogin');
if (headerLogin) headerLogin.addEventListener('click', () => window.modalWindowLogin());

// ==============================================

const headerFavorites = document.getElementById('headerFavorites');
if (headerFavorites) headerFavorites.addEventListener('click', () => window.modalWindowLogin());

// ==============================================

const headerCart = document.getElementById('headerCart');
headerCart.addEventListener('click', () => window.cartOpen());
