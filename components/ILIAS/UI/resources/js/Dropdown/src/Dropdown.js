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

export default class Dropdown {
  /**
   * @type {Document}
   */
  #document;

  /**
   * @type {HTMLElement}
   */
  #element;

  /**
   * @type {HTMLElement}
   */
  #button;

  /**
   * @type {HTMLElement}
   */
  #list;

  /**
   * @param {HTMLElement} element
   */
  constructor(element) {
    this.#element = element;
    this.#document = element.ownerDocument;

    this.#button = this.#element.querySelector(':scope > button');
    if (this.#button === null) {
      throw new Error('Dropdown: Expected exactly one button in dropdown element.', this.#element);
    }

    this.#list = this.#element.querySelector(':scope > ul');
    if (this.#list === null) {
      throw new Error('Dropdown: Expected exactly one ul in dropdown element.', this.#element);
    }

    this.#button.addEventListener('click', this.#showOnClick);
  }

  /**
   * @type {function(KeyboardEvent)}
   */
  #hideOnEscape = (/** @param {KeyboardEvent} event */ event) => {
    if (event.key === 27) { // ESCAPE
      this.hide();
    }
  };

  /**
   * @type {function(KeyboardEvent)}
   */
  #showOnClick = (/** @param {KeyboardEvent} event */event) => {
    event.stopPropagation();
    this.show();
  };

  /**
   * @type {function()}
   */
  #hideOnClick = () => {
    this.hide();
  };

  #align = () => {
    const availableWidth = this.#document.documentElement.clientWidth;
    const buttonPosition = this.#button.getBoundingClientRect().left;
    const listWidth = this.#list.getBoundingClientRect().width;
    if (buttonPosition + listWidth > availableWidth) {
      this.#list.classList.remove('dropdown-menu__right');
      this.#list.classList.add('dropdown-menu__left');
    } else {
      this.#list.classList.remove('dropdown-menu__left');
      this.#list.classList.add('dropdown-menu__right');
      
    }
  };

  show() {
    il.UI.dropdown.opened?.hide();
    il.UI.dropdown.opened = this;
    this.#list.style.display = 'block';
    this.#align();
    this.#button.setAttribute('aria-expanded', 'true');
    this.#document.addEventListener('keydown', this.#hideOnEscape);
    this.#document.addEventListener('click', this.#hideOnClick);
    this.#button.removeEventListener('click', this.#showOnClick);
  }

  hide() {
    this.#list.style.display = 'none';
    this.#button.setAttribute('aria-expanded', 'false');
    this.#document.removeEventListener('keydown', this.#hideOnEscape);
    this.#document.removeEventListener('click', this.#hideOnClick);
    this.#button.addEventListener('click', this.#showOnClick);
  }
}
