import document from 'document';
import il from 'ilias';
import $ from 'jquery';

il.WOPI = {
  modified: false,
  listeners: {},
};

il.WOPI.bindCloseSignal = function (elementId, signalId) {
  $(`#${elementId}`).on(signalId, (e, options) => { // we need to use jQuery here since signals are working with jQuery
    const targetUrl = options.options.target_url || null;
    if (targetUrl === null) {
      return true;
    }

    this.waitForSave().then(() => {
      window.location = targetUrl;
    }).catch(() => {
      // currently no special handling for errors
      window.location = targetUrl;
    });

    e.stopPropagation();
    e.preventDefault();
    return false;
  });
};

il.WOPI.waitForSave = function () {
  return new Promise((resolve, reject) => {
    const overlay = document.createElement('div');
    overlay.id = 'c-embedded-wopi-overlay';
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(255,255,255,0.7)';
    this.frameholder.appendChild(overlay);

    this.save().then((saved) => {
      if (saved) {
        resolve(true);
      } else {
        reject(new Error('not saved'));
      }
    }).catch((err) => {
      reject(err);
    });
  });
};

il.WOPI.save = function () {
  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => {
      reject(new Error('timeout'));
    }, 2000);

    this.registerListener('Action_Save_Resp', () => {
      clearTimeout(timer);
      resolve(true);
    });
    this.postMessage({
      MessageId: 'Action_Save',
      SendTime: Date.now(),
      Values: {
        DontTerminateEdit: true,
        DontSaveIfUnmodified: true,
        Notify: true,
      },
    });
  });
};

il.WOPI.windowResize = function () {
  const iframeWidth = this.editorFrame.parentElement.offsetWidth - 0;
  const iframeHeight = document.getElementsByClassName('il-layout-page-content')[0].clientHeight - document.getElementsByClassName('il_HeaderInner')[0].clientHeight - document.getElementsByTagName('footer')[0].clientHeight - 100;

  this.editorFrame.setAttribute('width', iframeWidth);
  this.editorFrame.setAttribute('height', iframeHeight);
};

il.WOPI.init = function () {
  // BUILD IFRAME
  const frameholder = document.getElementById('c-embedded-wopi');

  // read ttl, token and editor URL from data attributes
  const token = frameholder.getAttribute('data-token');
  const editorUrl = frameholder.getAttribute('data-editor-url');
  const ttl = frameholder.getAttribute('data-ttl');

  const editorFrame = document.createElement('iframe');
  editorFrame.name = 'editor_frame';
  editorFrame.id = 'editor_frame';
  editorFrame.title = 'Office Frame';
  editorFrame.setAttribute('allowfullscreen', 'true');
  editorFrame.setAttribute('allowtransparency', 'true');
  editorFrame.setAttribute('frameBorder', '0');
  frameholder.appendChild(editorFrame);

  this.frameholder = frameholder;
  this.editorFrame = editorFrame;
  // eslint-disable-next-line max-len
  this.editorFrameWindow = editorFrame.contentWindow || (editorFrame.contentDocument.document || editorFrame.contentDocument);
  this.windowResize();

  // BUILD FORM
  const form = document.createElement('form');
  const tokenInput = document.createElement('input');
  const ttlInput = document.createElement('input');

  form.method = 'POST';
  form.action = editorUrl;
  form.target = 'editor_frame';

  tokenInput.name = 'access_token';
  tokenInput.value = token;
  form.appendChild(tokenInput);

  ttlInput.name = 'access_token_ttl';
  ttlInput.value = ttl;
  form.appendChild(ttlInput);

  document.body.appendChild(form);

  // SEND FORM
  form.submit();

  // Listen to postMessages from the editor
  window.addEventListener(
    'message',
    (event) => {
      this.handleMessage(event);
    },
    false,
  );
  this.registerListener('*', (values) => {
    // console.log('message received', values);
  });

  // Add event listener to receive messages from the editor
  this.registerListener('App_LoadingStatus', () => {
    this.postMessage({
      MessageId: 'Host_PostmessageReady',
      SendTime: Date.now(),
      Values: {},
    });
  });

  // Collabora
  this.registerListener('Doc_ModifiedStatus', (values) => {
    this.modified = values.Modified ?? false;
  });

  // OnlyOffice
  this.registerListener('Edit_Notification', () => {
    this.modified = true;
  });

  // Add event listener to resize the editor iframe
  document.defaultView.addEventListener('resize', () => {
    il.WOPI.windowResize(editorFrame);
  });

  // resize after some time to make sure the editor is loaded and mainmenu has been collapsed
  setTimeout(
    () => {
      il.WOPI.windowResize(editorFrame);
    },
    200,
  );
};

il.WOPI.handleMessage = function (message) {
  const messageObj = JSON.parse(message.data);
  const MessageId = messageObj.MessageId ?? null;

  if (this.listeners[MessageId]) {
    this.listeners[MessageId].forEach((callback) => {
      callback(messageObj.Values);
    });
  }
  if (this.listeners['*']) {
    this.listeners['*'].forEach((callback) => {
      callback(messageObj);
    });
  }
};

il.WOPI.postMessage = function (mobj) {
  this.editorFrameWindow.postMessage(JSON.stringify(mobj), '*');
};

il.WOPI.registerListener = function (MessageId, callback) {
  if (!this.listeners[MessageId]) {
    this.listeners[MessageId] = [];
  }
  this.listeners[MessageId].push(callback);
};
