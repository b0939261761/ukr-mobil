const clickTab = () => {
  const hash = window.location.hash.slice(1);
  document.querySelectorAll('details[open]').forEach(el => el.open = false);

  if (!hash) return;
  const activeDetails = document.getElementById(`${hash}-details`);
  if (activeDetails) activeDetails.open = true;
}

clickTab();

window.onhashchange = clickTab;