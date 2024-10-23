/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

import { expandableTextareaFromNodes } from './expandableTextarea';

export default (anchor, sendMessage, typing) => {
  const textarea = anchor.querySelector('#submit_message_text');
  const button = anchor.querySelector('#submit_message');
  button.addEventListener('click', e => {
    e.preventDefault();
    e.stopPropagation();
    send();
  });

  const syncSize = expandableTextareaFromNodes(anchor.querySelector('#chat-shadow'), textarea, 3);

  textarea.addEventListener('input', syncSize);

  textarea.addEventListener('keydown', e => {
    const keycode = e.keyCode || e.which;

    if (keycode === 13 && !e.shiftKey) {
      e.preventDefault();
      e.stopPropagation();

      textarea.blur();
      send();
    }
  });

  textarea.addEventListener('keyup', e => {
    typing[(e.keyCode || e.which) === 13 ? 'release' : 'heartbeat']();
  });

  function send() {
    const content = textarea.value;

    if (content.trim() !== '') {
      const message = {
        content,
        format: {}
      };

      textarea.value = '';
      typing.release();
      sendMessage(message);
      textarea.focus();
      syncSize();
    }
  }
};
