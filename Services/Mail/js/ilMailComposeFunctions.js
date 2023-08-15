/* global il */
il = il || {};

(function init(scope, factory) {
  scope.Mail = factory();
}(il, () => {
  let browserSupportsTextareaTextNodes;

  function canManipulateViaTextNodes(input) {
    if (input.nodeName !== 'TEXTAREA') {
      return false;
    }

    if (typeof browserSupportsTextareaTextNodes === 'undefined') {
      const textarea = document.createElement('textarea');
      textarea.value = '1';
      browserSupportsTextareaTextNodes = !!textarea.firstChild;
    }

    return browserSupportsTextareaTextNodes;
  }

  const methods = {};
  
  methods.initMailPlaceholderSelection = function(elements, target_textarea) {
    elements.forEach(function (link_element, i) {
      elements[i].addEventListener('click', function(e) {
        e.preventDefault();
        il.Mail.insertTextIntoTextField(target_textarea, link_element.innerHTML);
      });
      elements[i].addEventListener('keyup', function(e) {
        if (e.code === 'Space') {
          e.preventDefault();
          il.Mail.insertTextIntoTextField(target_textarea, link_element.innerHTML);
        }
      });
    });
  };

  methods.insertTextIntoTextField = function (elementId, text) {
    const input = document.getElementById(elementId);

    input.focus();

    const isSuccess = document.execCommand('insertText', false, text);
    if (!isSuccess) {
      const start = input.selectionStart;
      const end = input.selectionEnd;

      if (typeof input.setRangeText === 'function') {
        input.setRangeText(text);
      } else {
        const range = document.createRange();
        const textNode = document.createTextNode(text);

        if (canManipulateViaTextNodes(input)) {
          let node = input.firstChild;

          if (!node) {
            input.appendChild(textNode);
          } else {
            let offset = 0;
            let startNode = null;
            let endNode = null;

            while (node && (startNode === null || endNode === null)) {
              const nodeLength = node.nodeValue.length;

              if (start >= offset && start <= offset + nodeLength) {
                range.setStart((startNode = node), start - offset);
              }

              if (end >= offset && end <= offset + nodeLength) {
                range.setEnd((endNode = node), end - offset);
              }

              offset += nodeLength;
              node = node.nextSibling;
            }

            if (start !== end) {
              range.deleteContents();
            }
          }
        }

        if (canManipulateViaTextNodes(input) && range.commonAncestorContainer.nodeName === '#text') {
          range.insertNode(textNode);
        } else {
          const { value } = input;
          input.value = value.slice(0, start) + text + value.slice(end);
        }
      }

      input.setSelectionRange(start + text.length, start + text.length);

      const e = document.createEvent('UIEvent');
      e.initEvent('input', true, false);
      input.dispatchEvent(e);
    }
  };

  return methods;
}));

// removes ',' at the ending of recipients textfield
function getStripCommaCallback(obj) {
  return function () {
    const val = obj.value.replace(/^\s+/, '').replace(/\s+$/, '');
    let stripcount = 0;
    let i;
    for (i = 0; i < val.length && val.charAt(val.length - i - 1) === ','; i++) {
      stripcount++;
    }
    obj.value = val.substr(0, val.length - stripcount);
  };
}

// initializes textfields for comma stripping on leaving recipients textfields
il.Util.addOnLoad(
  () => {
    const ar = ['rcp_to', 'rcp_cc', 'rcp_bcc'];
    for (let i = 0; i < ar.length; i++) {
      const obj = document.getElementById(ar[i]);
      if (obj) {
        obj.onblur = getStripCommaCallback(document.getElementById(ar[i]));
      }
    }
  }
);
