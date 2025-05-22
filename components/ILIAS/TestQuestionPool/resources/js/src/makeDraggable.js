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
   * @param {DOMDocument} documentParam
   * @param {string} draggableClass
   * @param {string} placeholderClassParam
   * @param {function} onChangeHandlerParam This handler is here to do two things:
   * Put the Placeholders in the right place after a change and trigger any other
   * changes necessary to make the parent usecase work.
   */
export default function makeDraggable(
  dragType,
  parentElement,
  draggableClass,
  placeholderClass,
  onChangeHandler,
  onStartPrepareHandler,
) {
  /**
  * @type {DOMElement}
  */
  let clonedElementForTouch;

  /**
   * @type {DOMElement}
   */
  let currentHoverElementForTouch;

  /**
   * @type {DOMElement}
   */
  let draggedElement;

  /**
   * @param {Event} event
   */
  function dragstartHandler(event) {
    setTimeout(() => {
      startMoving(event.target);
      event.dataTransfer.dropEffect = dragType;
      event.dataTransfer.effectAllowed = dragType;
      event.dataTransfer.setDragImage(draggedElement, 0, 0);
    }, 0);
  }

  /**
   * @param {Event} event
   */
  function touchstartHandler(event) {
    event.preventDefault();
    event.stopPropagation();
    startMoving(event.target.closest(`.${draggableClass}`));
    const width = draggedElement.offsetWidth;
    const height = draggedElement.offsetHeight;
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

    onStartPrepareHandler(draggedElement);

    parentElement.querySelectorAll(`.${placeholderClass}`).forEach(
      (elem) => {
        addPlaceholderEventListeners(elem);
        elem.classList.add(activeClass);
      },
    );

    draggedElement.querySelectorAll(`.${placeholderClass}`).forEach(
      (elem) => { elem.classList.remove(activeClass); },
    );
  }

  /**
   * @param {Event} event
   */
  function touchmoveHandler(event) {
    event.preventDefault();
    draggedElement.style.left = `${event.touches[0].clientX - draggedElement.offsetWidth / 2}px`;
    draggedElement.style.top = `${event.touches[0].clientY - draggedElement.offsetHeight / 2}px`;

    const { documentElement } = parentElement.ownerDocument;
    if (event.touches[0].clientY > documentElement.clientHeight * 0.8) {
      documentElement.scroll({
        left: 0,
        top: event.touches[0].pageY * 0.8,
        behavior: 'smooth',
      });
    }

    if (event.touches[0].clientY < documentElement.clientHeight * 0.2) {
      documentElement.scroll({
        left: 0,
        top: event.touches[0].pageY * 0.8,
        behavior: 'smooth',
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
    const source = draggedElement.parentNode;
    let dropElement = draggedElement;
    if (dragType !== 'move') {
      dropElement = draggedElement.cloneNode(true);
      dropElement.style.opacity = null;
      addDragEventListeners(dropElement);
    }
    target.parentNode.insertBefore(dropElement, target);
    onChangeHandler(dropElement, target, draggedElement, source);
  }

  /**
   * @param {DOMElement} elem
   * @returns {void}
   */
  function addDragEventListeners(elem) {
    elem.addEventListener('dragstart', dragstartHandler);
    elem.addEventListener('dragend', dragendHandler);
    elem.addEventListener('touchstart', touchstartHandler);
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

  parentElement.querySelectorAll(`.${draggableClass}`).forEach(addDragEventListeners);
  parentElement.querySelectorAll(`.${placeholderClass}`).forEach(addPlaceholderEventListeners);
}
