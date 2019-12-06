(function() {
  var copyButton = document.getElementById('copy-button');
  if (copyButton) {
    var data = document.getElementById('example-source').textContent;
    new ZeroClipboard(copyButton).on('copy', function(event) {
      event.clipboardData.setData({
        'text/plain': data,
        'text/html': data
      });
    });
  }

  var codepenButton = document.getElementById('codepen-button');
  if (codepenButton) {
    codepenButton.onclick = function(event) {
      event.preventDefault();
      var form = document.getElementById('codepen-form');

      // Doc : https://blog.codepen.io/documentation/api/prefill/

      var resources = form.resources.value.split(',');

      var data = {
        title: form.title.value,
        description: form.description.value,
        layout: 'left',
        html: form.html.value,
        css: form.css.value,
        js: form.js.value,
        css_external: resources.filter(function(resource) {
          return resource.lastIndexOf('.css') === resource.length - 4;
        }).join(';'),
        js_external: resources.filter(function(resource) {
          return resource.lastIndexOf('.js') === resource.length - 3;
        }).join(';')
      };

      // binary flags to display html, css, js and/or console tabs
      data.editors = '' + Number(data.html.length > 0) +
          Number(data.css.length > 0) +
          Number(data.js.length > 0) +
          Number(data.js.indexOf('console') > 0);

      form.data.value = JSON.stringify(data);

      form.submit();
    };
  }

  if (window.location.host === 'localhost:3000') {
    return;
  }

  var container = document.getElementById('navbar-logo-container');
  if (!container) {
    return;
  }

  var form = document.createElement('form');
  var select = document.createElement('select');
  var possibleModes = {
    'raw' : 'Development',
    'advanced': 'Production'
  };
  var urlMode = window.location.href.match(/mode=([a-z0-9\-]+)\&?/i);
  var curMode = urlMode ? urlMode[1] : 'advanced';

  for (var mode in possibleModes) {
    if (possibleModes.hasOwnProperty(mode)) {
      var option = document.createElement('option');
      var modeTxt = possibleModes[mode];
      option.value = mode;
      option.innerHTML = modeTxt;
      option.selected = curMode === mode;
      select.appendChild(option);
    }
  }

  select.onchange = function(event) {
    var newMode = event.target.value;
    var search = window.location.search.substring(1);
    var baseUrl = window.location.href.split('?')[0];
    var chunks = search ? search.split('&') : [];
    var pairs = [];
    var modeFound = false;
    for (var i = chunks.length - 1; i >= 0; --i) {
      var pair = chunks[i].split('=');
      if (pair[0].toLowerCase() === 'mode') {
        pair[1] = newMode;
        modeFound = true;
      }
      var adjusted = encodeURIComponent(pair[0]);
      if (typeof pair[1] !== undefined) {
        adjusted += '=' + encodeURIComponent(pair[1] || '');
      }
      pairs.push(adjusted);
    }
    if (!modeFound) {
      pairs.push('mode=' + encodeURIComponent(newMode));
    }
    location.href = baseUrl + '?' + pairs.join('&');
  };

  select.className = 'input-medium';

  form.className = 'navbar-form version-form';
  form.appendChild(select);

  container.appendChild(form);
})();
