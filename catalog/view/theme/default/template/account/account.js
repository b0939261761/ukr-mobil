const onClickBtnLogout = async () => {
  try {
    await fetch('/index.php?route=api/logout');
  } catch (err) {}
  window.location = '/';
};

const btnLogout = document.getElementById('btnLogout');
btnLogout.addEventListener('click', onClickBtnLogout);
