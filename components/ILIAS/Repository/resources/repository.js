/* global il, $ */

il = il || {};
il.repository = il.repository || {};

il.repository.ui = (function (il, $) {
  // All functions now have direct access to each other

  const sendAsync = function (form, replace = null) {
    const data = new URLSearchParams();
    for (const pair of new FormData(form)) {
      data.append(pair[0], pair[1]);
    }
    fetch(form.action, {
      method: 'POST',
      mode: 'same-origin',
      cache: 'no-cache',
      credentials: 'same-origin',
      redirect: 'follow',
      referrerPolicy: 'same-origin',
      body: data
    }).then(response => {
      response.text().then(text => {
          if (replace) {
            // this keeps the dialog open (full replacement would close)
            const marker = "component";
            var $new_content = $("<div>" + text + "</div>");
            var $marked_new_content = $new_content.find("[data-replace-marker='" + marker + "']").first();
            if ($marked_new_content.length == 0) {
              // if marker does not come with the new content, we put the new content into the existing element
              $(replace).html(text);
            } else {

              // get new id of the root and set it
              const tpl = document.createElement('template');
              tpl.innerHTML = text.trim();
              const id = tpl.content.firstElementChild?.id ?? null;
              if (id) {
                replace.id = id;
              }

              // if marker is in new content, we replace the complete old node with the marker
              // with the new marked node
              $(replace).find("[data-replace-marker='" + marker + "']").first()
              .replaceWith($marked_new_content);

            // append included script (which will not be part of the marked node
            $(replace).find(`[data-replace-marker='${marker}']`).first()
              .after($new_content.find("[data-replace-marker='script']"));
          }
        }
      });
    });
  };

  const initForms = function () {
  };

  const initModal = function (id) {
    const modal = document.getElementById(id);
    const buttons = modal.querySelectorAll('.modal-footer button');
    if (buttons.length >= 2) {
      const penultimate = buttons[buttons.length - 2];
      penultimate.remove();
    }
    modal.dataset.modalInitialised = '1';
  };

  const init = function () {
    initForms();
  };

  const submitModalForm = function(event, sentAsync) {
    const f = event.target.closest(".c-modal").querySelector(".modal-body").querySelector("form");
    const modal = f.closest(".c-modal");
    if (sentAsync) {
      sendAsync(f, modal);
    } else {
      f.submit();
    }
  };

  return {
    init: init,
    submitModalForm: submitModalForm,
    initModal: initModal,
  };
}(il, $));

il.repository.core = (function () {
  let httpPath = '';

  const init = function (path) {
    httpPath = path;
  };

  // set inner html and execute script tags
  function setInnerHTML(el, html) {
    el.innerHTML = html;

    Array.from(el.querySelectorAll('script'))
      .forEach((oldScriptEl) => {
        const newScriptEl = document.createElement('script');

        Array.from(oldScriptEl.attributes).forEach((attr) => {
          newScriptEl.setAttribute(attr.name, attr.value);
        });

        const scriptText = document.createTextNode(oldScriptEl.innerHTML);
        newScriptEl.appendChild(scriptText);

        oldScriptEl.parentNode.replaceChild(newScriptEl, oldScriptEl);
      });
  }

  function setOuterHTML(el_id, html) {
    let el = document.getElementById(el_id);
    el.outerHTML = html;
    el = document.getElementById(el_id);

    Array.from(el.querySelectorAll('script'))
      .forEach((oldScriptEl) => {
        const newScriptEl = document.createElement('script');

        Array.from(oldScriptEl.attributes).forEach((attr) => {
          newScriptEl.setAttribute(attr.name, attr.value);
        });

        const scriptText = document.createTextNode(oldScriptEl.innerHTML);
        newScriptEl.appendChild(scriptText);

        oldScriptEl.parentNode.replaceChild(newScriptEl, oldScriptEl);
      });
  }

  function trigger(name, el = null, details = null) {
    const ev = new CustomEvent(name, {
      detail: details,
      bubbles: true,
      cancelable: true,
      composed: false,
    });
    if (!el) {
      el = document.documentElement;
    }
    el.dispatchEvent(ev);
  }

  function fetchJson(url = '', params = {}) {
    const fetch_url = getFetchUrl(url);
    const url_params = new URLSearchParams(fetch_url.search.slice(1));
    for (const [key, value] of Object.entries(params)) {
      url_params.append(key, value);
    }
    fetch_url.search = url_params;

    return fetch(fetch_url.href, {
      method: 'GET',
      mode: 'same-origin',
      cache: 'no-cache',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
      },
      redirect: 'follow',
      referrerPolicy: 'same-origin',
    });
  }

  /**
   * @param {string} url
   * @returns {URL}
   */
  function getFetchUrl(url) {
    let fetch_url;
    try {
      fetch_url = new URL(url);
    } catch (error) {
      // relative paths
      fetch_url = new URL(`${httpPath}/${url}`);
    }
    return fetch_url;
  }

  function fetchHtml(url = '', params = {}, post = false) {
    const fetch_url = getFetchUrl(url);
    let formData;
    const url_params = new URLSearchParams(fetch_url.search.slice(1));
    if (!post) {
      for (const [key, value] of Object.entries(params)) {
        url_params.append(key, value);
      }
    } else {
      formData = new FormData();
      for (const [key, value] of Object.entries(params)) {
        formData.append(key, value);
      }
    }
    fetch_url.search = url_params;

    const method = (post) ? 'POST' : 'GET';
    const config = {
      method,
      mode: 'same-origin',
      cache: 'no-cache',
      credentials: 'same-origin',
      redirect: 'follow',
      referrerPolicy: 'same-origin',
    };
    if (post) {
      config.body = formData;
    }
    return new Promise((resolve, reject) => {
      fetch(fetch_url.href, config).then((response) => {
        if (response.ok) {
          // const statusText = response.statusText;
          response.text().then((text) => resolve(text)).catch();
        }
      }).catch();
    });
  }

  function fetchReplaceInner(el, url = '', params = {}, cb = null) {
    fetchHtml(url, params)
      .then((html) => {
        setInnerHTML(el, html);
        if (cb) {
          cb();
        }
      }).catch();
  }

  function fetchReplace(el_id, url = '', params = {}) {
    fetchHtml(url, params)
      .then((html) => {
        setOuterHTML(el_id, html);
      }).catch();
  }

  function fetchUrl(url = '', params = {}, args = {}, success_cb = null) {
    const fetch_url = getFetchUrl(url);
    const url_params = new URLSearchParams(fetch_url.search.slice(1));
    for (const [key, value] of Object.entries(params)) {
      url_params.append(key, value);
    }
    fetch_url.search = url_params;
    const config = {
      method: 'GET',
      mode: 'same-origin',
      cache: 'no-cache',
      credentials: 'same-origin',
      redirect: 'follow',
      referrerPolicy: 'same-origin',
    };
    fetch(fetch_url.href, config).then((response) => {
      if (response.ok) {
        // const statusText = response.statusText;
        response.text().then((text) => {
          if (success_cb) {
            success_cb({
              text,
              args,
            });
          }
        }).catch();
      }
    }).catch();
  }

  return {
    setInnerHTML,
    setOuterHTML,
    fetchHtml,
    fetchUrl,
    fetchReplace,
    fetchReplaceInner,
    trigger,
    init,
  };
}());
