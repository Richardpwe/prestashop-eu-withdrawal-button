(function () {
  document.addEventListener('click', function (event) {
    var button = event.target.closest && event.target.closest('.euwb-order-select');
    if (!button) {
      return;
    }

    var reference = button.getAttribute('data-reference');
    var field = document.getElementById('euwb-order-reference');
    if (field && reference) {
      field.value = reference;
      field.focus();
    }
  });
})();

