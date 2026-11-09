/* ===================================================================
   Admin searchable dropdown
   Progressively enhances any <select class="searchable-select"> into
   a type-to-filter combobox. The original <select> stays in the DOM
   (hidden) so the form still submits normally — this only changes
   how the admin FINDS the right option, not how the value is sent.
   Usage: just add class="searchable-select" to a <select> in an
   admin form. Nothing else needed.
   =================================================================== */
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select.searchable-select').forEach(enhance);
  });

  function enhance(select) {
    var wrap = document.createElement('div');
    wrap.className = 'ss-wrap';
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);
    select.classList.add('ss-hidden-select');

    var input = document.createElement('input');
    input.type = 'text';
    input.className = 'ss-input';
    input.autocomplete = 'off';
    input.placeholder = select.dataset.placeholder || 'Type to search…';
    wrap.appendChild(input);

    var list = document.createElement('div');
    list.className = 'ss-list';
    wrap.appendChild(list);

    var options = Array.prototype.slice.call(select.options).filter(function (o) { return o.value !== ''; });

    function currentLabel() {
      var opt = select.options[select.selectedIndex];
      return opt && opt.value ? opt.textContent.trim() : '';
    }
    input.value = currentLabel();

    function renderList(filter) {
      list.innerHTML = '';
      var term = (filter || '').toLowerCase();
      var matches = options.filter(function (o) { return o.textContent.toLowerCase().indexOf(term) !== -1; });

      if (matches.length === 0) {
        var none = document.createElement('div');
        none.className = 'ss-empty';
        none.textContent = 'No matches';
        list.appendChild(none);
        return;
      }
      matches.forEach(function (o) {
        var item = document.createElement('div');
        item.className = 'ss-item' + (o.value === select.value ? ' active' : '');
        item.textContent = o.textContent.trim();
        item.addEventListener('mousedown', function (e) {
          e.preventDefault();
          select.value = o.value;
          input.value = o.textContent.trim();
          select.dispatchEvent(new Event('change', { bubbles: true }));
          closeList();
        });
        list.appendChild(item);
      });
    }

    function openList() { renderList(input.value === currentLabel() ? '' : input.value); list.classList.add('open'); }
    function closeList() { list.classList.remove('open'); }

    input.addEventListener('focus', function () { input.select(); openList(); });
    input.addEventListener('input', function () { renderList(input.value); list.classList.add('open'); });
    input.addEventListener('blur', function () {
      // slight delay so a click on a list item registers before we reset the label
      setTimeout(function () { closeList(); input.value = currentLabel(); }, 150);
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { closeList(); input.value = currentLabel(); input.blur(); }
    });
  }
})();