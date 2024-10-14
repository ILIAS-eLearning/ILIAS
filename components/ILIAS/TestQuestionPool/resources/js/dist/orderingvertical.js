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
(function (il) {
  'use strict';

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
   * @type {String}
   */
  const positionInputQuery = '[name*="[position]"';

  /**
   * @type {String}
   */
  const indentationInputQuery = '[name*="[indentation]"';

  /**
   * @type {String}
   */
  const answerElementClass = 'dd-item';

  /**
   * @type {String}
   */
  const placeholderClass$1 = 'c-test__dropzone';

  /**
   * @type {DOMElement}
   */
  let parentElement$1;

  function setup() {
    const answers = parentElement$1.querySelectorAll(`.${answerElementClass}`);
    let elementHeight = 0;
    answers.forEach(
      (elem) => {
        if (elem.offsetHeight < elementHeight) {
          elementHeight = elem.offsetHeight;
        }
      }
    );
    parentElement$1.querySelectorAll(`.${placeholderClass$1}`).forEach(
      (elem) => {
        elem.style.height = `${answers.item(0).offsetHeight}px`;
      }
    );
  }

  function updatePlaceholders() {
    const placeholderElement = parentElement$1.querySelector(`.${placeholderClass$1}`);
    parentElement$1.querySelectorAll(`.${answerElementClass}`).forEach(
      (elem) => {
        if (!elem.previousElementSibling?.classList.contains(placeholderClass$1)) {
          elem.parentNode.insertBefore(placeholderElement.cloneNode(), elem);
        }

        if (!elem.nextElementSibling?.classList.contains(placeholderClass$1)) {
          elem.parentNode.insertBefore(placeholderElement.cloneNode(), elem.nextElementSibling);
        }
      },
    );

    parentElement$1.querySelectorAll(`.${placeholderClass$1} + .${placeholderClass$1}`).forEach(
      (elem) => {
        elem.remove();
      },
    );
  }

  function updateIndentationInputs(draggedElement, target) {
    let i = 0;
    let root = target.parentElement.parentElement;
    while (root !== parentElement$1) {
      root = root.parentElement.parentElement;
      i++;
    }
    draggedElement.querySelector(indentationInputQuery).value = i;

    draggedElement.querySelectorAll(`.${answerElementClass}`).forEach(
      (elem) => {
        i++;
        elem.querySelector(indentationInputQuery).value = i;
      }
    );
  }

  function updatePositionInputs() {
    let p = 0;
    parentElement$1.querySelectorAll(`.${answerElementClass}`).forEach(
      (elem) => {
        elem.querySelector(positionInputQuery).value = p;
        p++;
      }
    );
  }

  function changeHandler(draggedElement, target) {
    updatePlaceholders();
    updateIndentationInputs(draggedElement, target);
    updatePositionInputs();
  }

  function orderingVerticalHandler(parentElementParam, makeDraggable) {
    parentElement$1 = parentElementParam;
    setup();
    makeDraggable(parentElement$1, answerElementClass, placeholderClass$1, changeHandler);
  }

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
   * @type {String}
   */
  const activeClass = 'c-test__dropzone--active';

  /**
   * @type {String}
   */
  const hoverClass = 'c-test__dropzone--hover';

  /**
   * @type {DOMElement}
   */
  let parentElement;

  /**
   * @type {String}
   */
  let draggableClass;

  /**
   * @type {String}
   */
  let placeholderClass;

  /**
   * @type {Function}
   */
  let onChangeHandler;

  /**
   * @type {DOMElement}
   */
  let draggedElement;

  /**
   * @type {DOMElement}
   */
  let clonedElementForTouch;

  /**
   * @type {DOMElement}
   */
  let currentHoverElementForTouch;

  /**
   * @param {Event} event
   */
  function dragstartHandler(event) {
    startMoving(event.target);
    event.dataTransfer.dropEffect = 'move';
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setDragImage(draggedElement, 0, 0);
  }

  /**
   * @param {Event} event
   */
  function touchstartHandler(event) {
    event.preventDefault();
    event.stopPropagation();
    startMoving(event.target.closest(`.${draggableClass}`));
    let width = draggedElement.offsetWidth;
    let height = draggedElement.offsetHeight;
    clonedElementForTouch = draggedElement.cloneNode(true);
    draggedElement.parentNode.insertBefore(clonedElementForTouch, draggedElement);
    draggedElement.style.position = 'fixed';
    draggedElement.style.left = `${event.touches[0].clientX - width / 2}px`;
    draggedElement.style.top = `${event.touches[0].clientY - height / 2}px`;
    draggedElement.style.width = `${width}px`;
    draggedElement.style.height = `${height}px`;
    draggedElement.addEventListener('touchmove', touchmoveHandler);
    draggedElement.addEventListener('touchend', touchendHandler);
  }

  /**
   * @param {DOMElement} target
   * @returns {void}
   */
  function startMoving(target) {
    draggedElement = target;
    draggedElement.style.opacity = 0.5;

    if (draggedElement.previousElementSibling?.classList.contains(placeholderClass)) {
      draggedElement.previousElementSibling.remove();
    }

    if (draggedElement.nextElementSibling?.classList.contains(placeholderClass)) {
      draggedElement.nextElementSibling.remove();
    }

    parentElement.querySelectorAll(`.${placeholderClass}`).forEach(
      (elem) => {
        elem.classList.add(activeClass);
      },
    );

    draggedElement.querySelectorAll(`.${placeholderClass}`).forEach(
      (elem) => { elem.classList.remove(activeClass); }
    );
  }

  /**
   * @param {Event} event
   */
  function touchmoveHandler(event) {
    event.preventDefault();
    draggedElement.style.left = `${event.touches[0].clientX - draggedElement.offsetWidth / 2}px`;
    draggedElement.style.top = `${event.touches[0].clientY - draggedElement.offsetHeight / 2}px`;

    let documentElement = parentElement.ownerDocument.documentElement;
    if (event.touches[0].clientY > documentElement.clientHeight * 0.8) {
      documentElement.scroll({
        left: 0,
        top: event.touches[0].pageY * 0.8,
        behavior: 'smooth'
      });
    }

    if (event.touches[0].clientY < documentElement.clientHeight * 0.2) {
      documentElement.scroll({
        left: 0,
        top: event.touches[0].pageY * 0.8,
        behavior: 'smooth'
      });
    }

    const element = parentElement.ownerDocument.elementsFromPoint(
      event.changedTouches[0].clientX,
      event.changedTouches[0].clientY,
    ).filter((elem) => elem.classList.contains(placeholderClass));

    if ((element.length === 0 && typeof currentHoverElementForTouch !== 'undefined')) {
      currentHoverElementForTouch.classList.remove(hoverClass);
      currentHoverElementForTouch = undefined;
    }

    if (element.length === 1 && currentHoverElementForTouch !== element[0]) {
      if (typeof currentHoverElementForTouch !== 'undefined') {
        currentHoverElementForTouch.classList.remove(hoverClass);
      }
      [currentHoverElementForTouch] = element;
      currentHoverElementForTouch.classList.add(hoverClass);
    }
  }

  /**
   * @param {Event} event
   */
  function dragoverHandler(event) {
    event.preventDefault();
  }

  /**
   * @param {Event} event
   */
  function dragenterHandler(event) {
    event.target.classList.add(hoverClass);
  }

  /**
   * @param {Event} event
   */
  function dragleaveHandler(event) {
    event.target.classList.remove(hoverClass);
  }

  function dragendHandler() {
    draggedElement.removeAttribute('style');
    parentElement.querySelectorAll(`.${placeholderClass}`).forEach(
      (elem) => {
        elem.classList.remove(activeClass);
        elem.classList.remove(hoverClass);
        addPlaceholderEventListeners(elem);
      },
    );
  }

  /**
   * @param {event} event
   */
  function dropHandler(event) {
    event.preventDefault();
    stopMoving(event.target);
  }

  /**
   * @param {event} event
   */
  function touchendHandler(event) {
    event.preventDefault();

    const element = parentElement.ownerDocument.elementsFromPoint(
      event.changedTouches[0].clientX,
      event.changedTouches[0].clientY,
    ).filter((elem) => elem.classList.contains(placeholderClass));

    dragendHandler();
    clonedElementForTouch.remove();

    if (element.length === 1) {
      stopMoving(element[0]);
    }
  }

  /**
   * @param {DOMElement} target
   * @returns {void}
   */
  function stopMoving(target) {
    target.parentNode.insertBefore(draggedElement, target);
    onChangeHandler(draggedElement, target);
  }

  /**
   * @param {DOMElement} elem
   * @returns {void}
   */
  function addPlaceholderEventListeners(elem) {
    elem.removeEventListener('dragover', dragoverHandler);
    elem.removeEventListener('dragenter', dragenterHandler);
    elem.removeEventListener('dragleave', dragleaveHandler);
    elem.removeEventListener('drop', dropHandler);
    elem.addEventListener('dragover', dragoverHandler);
    elem.addEventListener('dragenter', dragenterHandler);
    elem.addEventListener('dragleave', dragleaveHandler);
    elem.addEventListener('drop', dropHandler);
  }

  /**
     * @param {DOMDocument} documentParam
     * @param {string} draggableClass
     * @param {string} placeholderClassParam
     * @param {function} onChangeHandlerParam This handler is here to do two things:
     * Put the Placeholders in the right place after a change and trigger any other
     * changes necessary to make the parent usecase work.
     */
  function makeDraggable(
    parentElementParam,
    draggableClassParam,
    placeholderClassParam,
    onChangeHandlerParam,
  ) {
    parentElement = parentElementParam;
    draggableClass = draggableClassParam;
    placeholderClass = placeholderClassParam;
    onChangeHandler = onChangeHandlerParam;
    parentElement.querySelectorAll(`.${draggableClass}`).forEach(
      (elem) => {
        elem.addEventListener('dragstart', dragstartHandler);
        elem.addEventListener('dragend', dragendHandler);
        elem.addEventListener('touchstart', touchstartHandler);
      },
    );
    parentElement.querySelectorAll(`.${placeholderClass}`).forEach(addPlaceholderEventListeners);
  }

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


  il.test = il.test || {};
  il.test.orderingvertical = il.test.orderingvertical || {};

  il.test.orderingvertical.init = (parentElement, questionId) => orderingVerticalHandler(
    parentElement,
    makeDraggable);

})(il);
