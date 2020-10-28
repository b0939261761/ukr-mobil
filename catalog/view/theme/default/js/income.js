var btn = document.getElementById('btn-subscribe');

if (btn) {
  var onSubscribe = function() {
    fetch('index.php?route=information/expected_income/newsletter')
    .then(function() { 
      btn.remove(); 
      window.uiService.popup
        .setHeader(translate.get('popup.title.info'))
        .setBody(translate.get('module.newsletter.popup.success.body'))
        .hideFooter()
        .open();
    })
    .catch(function(error) { console.error(error)});
  };

  btn.addEventListener('click', onSubscribe);
};

