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
 */

import il from 'ilias';
import document from 'document';

il.test = il.test || {};
il.test.questionpage = il.test.questionpage || {};

il.test.questionpage.init = () => {
  [...document.querySelectorAll('#ilAssQuestionPreview .modal-header form, '
      + '#ilAssQuestionPreview .modal-footer form, #ilc_Page .modal-header form, '
      + '#ilc_Page .modal-footer form')].forEach(
    (node) => {
      const fragment = document.createDocumentFragment();
      while (node.firstChild) {
          fragment.appendChild(node.firstChild);
      }

      node.parentNode.replaceChild(fragment, node);
    }
  );

  [...document.querySelectorAll('#ilAssQuestionPreview .modal-header [formmethod="dialog"], '
      + '#ilAssQuestionPreview .modal-header [formmethod="dialog"], '
      + '#ilc_Page .modal-header [formmethod="dialog"], '
      + '#ilc_Page .modal-footer [formmethod="dialog"]')].forEach(
    (button) => {
      button.addEventListener('click', () => button.closest('dialog').close());
    }
  );
};