import document from 'document';
import window from 'window';
import il from 'ilias';

il.WOPI = {};
il.WOPI.windowResize = function (e) {
  const iframeWidth = e.parentElement.offsetWidth - 20;
  const iframeHeight = document.getElementsByClassName('il-layout-page-content')[0].clientHeight - document.getElementsByClassName('il_HeaderInner')[0].clientHeight - 100;

  e.setAttribute('width', iframeWidth);
  e.setAttribute('height', iframeHeight);
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

  document.defaultView.addEventListener('resize', () => {
    il.WOPI.windowResize(editorFrame);
  });
};
