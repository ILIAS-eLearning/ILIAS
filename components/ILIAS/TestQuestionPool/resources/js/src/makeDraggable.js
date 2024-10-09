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
  startMoving(event.target.closest(`.${draggableClass}`));
  clonedElementForTouch = draggedElement.cloneNode(true);
  draggedElement.parentNode.insertBefore(clonedElementForTouch, draggedElement);
  draggedElement.style.position = 'fixed';
  draggedElement.style.left = `${event.touches[0].clientX - draggedElement.offsetWidth / 2}px`;
  draggedElement.style.top = `${event.touches[0].clientY - draggedElement.offsetHeight / 2}px`;
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
      elem.style.width = `${draggedElement.offsetWidth}px`;
      elem.style.height = `${draggedElement.offsetHeight}px`;
      elem.classList.add(activeClass);
    },
  );
}

/**
 * @param {Event} event
 */
function touchmoveHandler(event) {
  event.preventDefault();
  draggedElement.style.left = `${event.touches[0].clientX - draggedElement.offsetWidth / 2}px`;
  draggedElement.style.top = `${event.touches[0].clientY - draggedElement.offsetHeight / 2}px`;

  const element = parentElement.ownerDocument.elementsFromPoint(
    event.changedTouches[0].pageX,
    event.changedTouches[0].pageY,
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
    event.changedTouches[0].pageX,
    event.changedTouches[0].pageY,
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
  onChangeHandler();
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
export default function makeDraggable(
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
