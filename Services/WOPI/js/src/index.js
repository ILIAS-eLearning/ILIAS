import document from 'document';
import il from 'ilias';

il.WOPI = {};
il.WOPI.windowResize = function (e) {
  const iframeWidth = e.parentElement.offsetWidth - 0;
  const iframeHeight = document.getElementsByClassName('il-layout-page-content')[0].clientHeight - document.getElementsByClassName('il_HeaderInner')[0].clientHeight - document.getElementsByTagName('footer')[0].clientHeight - 100;

  e.setAttribute('width', iframeWidth);
  e.setAttribute('height', iframeHeight);
};
il.WOPI.postMessage = function (mobj) {
  this.editorFrameWindow.postMessage(JSON.stringify(mobj), '*');
};
il.WOPI.save = function () {
  this.postMessage({
    MessageId: 'Action_Save',
    SendTime: Date.now(),
    Values: {
      DontTerminateEdit: true,
      DontSaveIfUnmodified: true,
      Notify: false,
    },
  });
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
  editorFrame.setAttribute('frameBorder', '0');
  frameholder.appendChild(editorFrame);
  this.windowResize(editorFrame);
  // eslint-disable-next-line max-len
  this.editorFrameWindow = editorFrame.contentWindow || (editorFrame.contentDocument.document || editorFrame.contentDocument);

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
  // Add event listener to receive messages from the editor
  window.addEventListener(
    'message',
    (event) => {
      const message = JSON.parse(event.data);
      if (message.MessageId === 'App_LoadingStatus' && message.Values.Status === 'Document_Loaded') {
        this.postMessage({
          MessageId: 'Host_PostmessageReady',
          SendTime: Date.now(),
          Values: {},
        });
      }
    },
    false,
  );

  // Add event listener to resize the editor iframe
  document.defaultView.addEventListener('resize', () => {
    il.WOPI.windowResize(editorFrame);
  });
};
