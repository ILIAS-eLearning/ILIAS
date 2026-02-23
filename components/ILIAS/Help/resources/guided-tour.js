// eslint-disable-next-line no-global-assign
il = il || {};
// il.guidedTour = il.guidedTour || {};

il.guidedTour = (function ($) {
  const compIds = new Map();
  let url; let tour; let signal; let popover; let currentTour;

  function addMapping(name, elId) {
    console.log(`addMapping: ${name} ${elId}`);
    compIds.set(name, elId);
  }

  function getScrollableContainer() {
    if (window.innerWidth < 768) {
      return document.querySelector('body');
    }
    return document.querySelector('.il-layout-page-content');
  }

  function trigger(el, s) {
    $(document).trigger(s, {
      id: s,
      event: null,
      triggerer: $(el),
      options: {
        event: 'manual',
        trigger: 'manual',
      },
    });
  }

  function hideAllPopovers() {
    WebuiPopovers.hideAll();
  }

  function checkStepElement(s) {
    const el = getTriggerElement(s.type, s.elementId);
    if (el) {
      return true;
    }
    return false;
  }

  function showPopover(type, elName, stepUrl, latest) {
    const el = getTriggerElement(type, elName);
    if (el) {
      const contentEl = popover;
      contentEl.innerHTML = `<iframe style="border:0; height: 5px; display:inline-block; width:20em; margin: 15px;" src='${stepUrl}'></iframe>`;
      hideAllPopovers();
      trigger(el, signal);
      const iframe = contentEl.querySelector('iframe');
      let first = true;
      iframe.addEventListener('load', () => {
        resizeIframe(iframe);
        if (first) {
          const scrollContainer = getScrollableContainer();
          scrollContainer.dispatchEvent(new Event('scroll'));
          first = false;
        }
        addButtonListeners(iframe, latest);
      });
      window.addEventListener('resize', () => {
        resizeIframe(iframe);
      });
      return true;
    }
    return false;
  }

  function addButtonListeners(iframe, latest) {
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    const buttons = [...doc.querySelectorAll('button')]; // NodeList → Array
    const lastButtons = buttons.slice(-2);
    // Beispiel: den Text der beiden Buttons ausgeben
    lastButtons.forEach((btn) => {
      if (btn.dataset.gdtrType === 'next') {
        if (latest) {
          btn.style.display = 'none';
        } else {
          btn.addEventListener('click', () => {
            nextStep();
          });
        }
      }
      if (btn.dataset.gdtrType === 'close') {
        btn.addEventListener('click', () => {
          closeTour();
        });
      }
    });
  }

  function resizeIframe(iframe) {
    const doc = iframe.contentWindow.document;

    iframe.style.height = '5px';

    doc.body.style.height = '100%';
    doc.body.style.minHeight = '100%';

    const newWidth = Math.max(
      doc.documentElement.scrollWidth,
      doc.body.scrollWidth,
    );

    let newHeight = Math.max(
      doc.documentElement.scrollHeight,
      doc.body.scrollHeight,
    );

    doc.body.style.height = 'auto';
    doc.body.style.minHeight = 'auto';

    newHeight += 10;

    // iframe.style.width = `${newWidth}px`;
    iframe.style.height = `${newHeight}px`;
  }

  function getTriggerElement(type, elName) {
    let el;
    // main, meta and tabs
    if (elName != '') {
      const elId = compIds.get(elName);
      if (elId) {
        el = document.getElementById(elId);
        // check if we have a mainbar slate instead of the button
        if (el.classList.contains('il-maincontrols-slate')) {
          const metabar = el.closest('.il-metabar-slates');
          if (metabar) {
            // metabar
            el = metabar.parentNode.querySelector('button');
          } else {
            // mainbar
            el = document.querySelector(`button[aria-controls="${elId}"]`);
          }
        }
      }
    } else {
      switch (type) {
        case 4: // Form
          el = document.querySelector('#ilContentContainer h2');
          generateIdIfMissing(el);
          break;
        case 5: // Table
          el = document.querySelector('#ilContentContainer thead');
          generateIdIfMissing(el);
          break;
        case 6: // Toolbar
          el = document.querySelector('#mainspacekeeper .c-toolbar .c-toolbar__item');
          generateIdIfMissing(el);
          break;
        case 7: // Primary Button
          el = document.querySelector('#mainspacekeeper .btn-primary');
          generateIdIfMissing(el);
          break;
      }
    }
    return el;
  }

  function generateIdIfMissing(el) {
    if (el && !el.id) {
      el.id = `uid-${Math.random().toString(36).substr(2, 9)}`;
    }
  }

  /* function fetchHTML(cmd) {
    return il.repository.core.fetchHtml(url, { cmd });
  } */

  function fetchJson(cmd) {
    return il.repository.core.fetchJson(url, { cmd });
  }

  function loadData() {
    fetchJson('getData').then((json) => {
      const main = document.querySelector('main');
      il.repository.core.appendHTML(main, json.popoverHtml);
      popover = document.querySelector('main .il-standard-popover-content');
      signal = json.popoverShowSignal;
      tour = json.tour;
      nextStep();
    });
  }

  function hasValidSuccessor(t, preStepId) {
    let foundPre = false;
    for (const [stepId, s] of Object.entries(t.steps)) {
      if (foundPre && checkStepElement(s)) {
        return true;
      }
      if (stepId === preStepId) {
        foundPre = true;
      }
    }
    return false;
  }

  function nextStep() {
    for (const [tourId, t] of Object.entries(tour)) {
      let nr = 1;
      let latest = false;
      for (const [stepId, s] of Object.entries(t.steps)) {
        console.log(stepId);
        if (!hasValidSuccessor(t, stepId)) {
          console.log('latest!');
          latest = true;
        }
        // no element? set to true
        if (!checkStepElement(s)) {
          console.log('no element!');
          s.done = true;
        }
        if (!s.done) {
          console.log('performStep');
          s.done = true;
          currentTour = tourId;
          performStep(s, latest);
          return;
        }
        console.log('increase');
        nr += 1;
      }
    }
  }

  function closeTour() {
    for (const [tourId, t] of Object.entries(tour)) {
      if (tourId === currentTour) {
        il.repository.core.fetchJson(t.finishUrl, {}).then(() => {
          hideAllPopovers();
        });
      }
    }
  }

  function performStep(s, latest) {
    return showPopover(s.type, s.elementId, s.url, latest);
  }

  function init(u) {
    url = u;
    setTimeout(() => {
      loadData();
    }, 200);
  }

  return {
    addMapping,
    init,
  };
  // eslint-disable-next-line no-undef
}($));
