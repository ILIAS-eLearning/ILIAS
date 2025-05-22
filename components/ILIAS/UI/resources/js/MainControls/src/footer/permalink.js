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

/**
 * @param {string} text
 * @returns {Promise}
 */
export const copyText = text => {
  if (window.navigator.clipboard) {
    return window.navigator.clipboard.writeText(text);
  }

  const node = document.createElement('span');
  const range = document.createRange();
  const selection = window.getSelection();

  node.textContent = text;
  document.body.appendChild(node);
  range.selectNodeContents(node);
  selection.addRange(range);

  const success = document.execCommand('copy');
  selection.removeAllRanges();
  node.remove();

  return success ? Promise.resolve() : Promise.reject(new Error('Unable to copy text.'));
};

export const showTooltip = (node, delay) => {
  const main = (Array.from(document.getElementsByTagName('main')).find(n => !n.hidden) || document.body).getBoundingClientRect();
  node.parentNode.classList.add('c-tooltip--visible');
  const r = node.getBoundingClientRect();

  if (main.left > r.left) {
    node.style.transform = 'translateX(calc(' + (main.left - r.left) + 'px - 50%))';
  } else if (main.right < r.right) {
    node.style.transform = 'translateX(calc(' + (main.right - r.right) + 'px - 50%))';
  }

  setTimeout(() => {
    node.parentNode.classList.remove('c-tooltip--visible');
  }, delay);
};
