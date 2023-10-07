"use strict";
/* global il, $ */

il = il || {};
il.repository = il.repository || {};

il.repository.ui = (function(il, $) {
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
            const marker = "component";
            var $new_content = $("<div>" + text + "</div>");
            var $marked_new_content = $new_content.find("[data-replace-marker='" + marker + "']").first();

            if ($marked_new_content.length == 0) {

              // if marker does not come with the new content, we put the new content into the existing element
              $(replace).html(text);

            } else {

              // if marker is in new content, we replace the complete old node with the marker
              // with the new marked node
              $(replace).find("[data-replace-marker='" + marker + "']").first()
              .replaceWith($marked_new_content);

              // append included script (which will not be part of the marked node
              $(replace).find("[data-replace-marker='" + marker + "']").first()
              .after($new_content.find("[data-replace-marker='script']"));
            }
          }
        }
      );
    });
  };

  const initForms = function () {
    document.querySelectorAll("form[data-rep-modal-form='async']:not([data-rep-form-initialised='1'])").forEach(f => {
      f.addEventListener("submit", (event) => {
        event.preventDefault();
        const modal = f.closest(".modal");
        sendAsync(f, modal);
      });
      f.querySelectorAll(".il-standard-form-cmd").forEach(b => {
        b.style.display='none';
      });
      f.dataset.repFormInitialised = '1';
    });
    document.querySelectorAll("form[data-rep-modal-form='sync']:not([data-rep-form-initialised='1'])").forEach(f => {
      f.querySelectorAll(".il-standard-form-cmd").forEach(b => {
        b.style.display='none';
      });
      f.dataset.repFormInitialised = '1';
    });
  };

  const init = function() {
    initForms();
  };

  const submitModalForm = function(event, sentAsync) {
    console.log("one");
    const f = event.target.closest(".modal").querySelector("form");
    console.log(f);
    const modal = f.closest(".modal");
    if (sentAsync) {
      sendAsync(f, modal);
    } else {
      f.submit();
    }
  };

  return {
    init: init,
    submitModalForm: submitModalForm
  };
}(il, $));

il.repository.core = (function() {
  let httpPath = '';

  const init = function(path) {
    httpPath = path;
  };

  // set inner html and execute script tags
  function setInnerHTML(el, html) {
    el.innerHTML = html;

    Array.from(el.querySelectorAll("script"))
    .forEach( oldScriptEl => {
      const newScriptEl = document.createElement("script");

      Array.from(oldScriptEl.attributes).forEach( attr => {
        newScriptEl.setAttribute(attr.name, attr.value)
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

    Array.from(el.querySelectorAll("script"))
    .forEach( oldScriptEl => {
      const newScriptEl = document.createElement("script");

      Array.from(oldScriptEl.attributes).forEach( attr => {
        newScriptEl.setAttribute(attr.name, attr.value)
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

    let fetch_url = getFetchUrl(url);
    let url_params = new URLSearchParams(fetch_url.search.slice(1));
    for (const [key, value] of Object.entries(params)) {
      url_params.append(key, value)
    }
    fetch_url.search = url_params;

    return fetch(fetch_url.href, {
      method: 'GET',
      mode: 'same-origin',
      cache: 'no-cache',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json'
      },
      redirect: 'follow',
      referrerPolicy: 'same-origin'
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
      fetch_url = new URL(httpPath + "/" + url);
    }
    return fetch_url;
  }

  function fetchHtml(url = '', params = {}, post = false) {
    let fetch_url = getFetchUrl(url);
    let formData;
    let url_params = new URLSearchParams(fetch_url.search.slice(1));
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

    const method = (post) ? "POST" : "GET" ;
    let config = {
      method: method,
      mode: 'same-origin',
      cache: 'no-cache',
      credentials: 'same-origin',
      redirect: 'follow',
      referrerPolicy: 'same-origin'
    };
    if (post) {
      config.body = formData;
    }
    return new Promise((resolve, reject) => {
      fetch(fetch_url.href, config).then(response => {
        if (response.ok) {
          //const statusText = response.statusText;
          response.text().then(text =>
            resolve(text)
        ).catch();
        }
      }).catch();
    });
  }

  function fetchReplaceInner(el, url = '', params = {}) {
    fetchHtml(url, params)
      .then(html => {
          setInnerHTML(el, html)
    }).catch();
  }

  function fetchReplace(el_id, url = '', params = {}) {
    fetchHtml(url, params)
    .then(html => {
      setOuterHTML(el_id, html)
    }).catch();
  }

  function fetchUrl(url = '', params = {}, args = {}, success_cb = null) {
    _fetchHtml(url, params)
    .then(response => {
      if (response.ok) {
        //const statusText = response.statusText;
        response.text().then(text => {
            if (success_cb) {
              success_cb({
                text: text,
                args: args
              });
            }
          }
        ).catch();
      }
    }).catch();
  }

  return {
    setInnerHTML: setInnerHTML,
    setOuterHTML: setOuterHTML,
    fetchHtml: fetchHtml,
    fetchReplace: fetchReplace,
    fetchReplaceInner: fetchReplaceInner,
    trigger: trigger,
    init: init
  };

}());