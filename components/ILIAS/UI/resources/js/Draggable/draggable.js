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
 * @type {string}
 */
const activeClass = 't-draggable__dropzone--active';

/**
 * @type {string}
 */
const hoverClass = 't-draggable__dropzone--hover';

/**
 * @type {string}
 */
const draggingClass = 't-draggable--dragging';

/**
  * @param {string} dragType Two possible values: 'move' or 'copy'.
  * @param {HTMLElement} parentElement
  * @param {string} draggableClass
  * @param {string} placeholderClass
  * @param {object} accessibility
  * @param {function} onStartPrepareHandler This function will be called, when
  * a drag operation starts. It allows you to ensure placeholders are in the right
  * place and to do anything else important before a drag-operation can be started.
  * @param {function} onChangeHandler This function is here to do two things:
  * Put the Placeholders in the right place after a change and trigger any other
  * changes necessary to make the parent usecase work.
  */
export default function makeDraggable(
  dragType,
  parentElement,
  draggableClass,
  placeholderClass,
  accessibility,
  onStartPrepareHandler,
  onChangeHandler,
) {
  /**
  * @type {HTMLElement}
  */
  let clonedElementForTouch;

  /**
   * @type {HTMLElement}
   */
  let currentHoverElementForTouch;

  /**
   * @type {object}
   */
  let nodesForKeyboardInteraction;

  /**
   * @type {HTMLElement}
   */
  let draggedElement;

  /**
   * @param {Event} event
   * @returns {void}
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
   * @returns {void}
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
   * @param {HTMLElement} target
   * @returns {void}
   */
  function startMoving(target) {
    draggedElement = target;
    target.classList.add(draggingClass);

    onStartPrepareHandler(target);

    parentElement.querySelectorAll(`.${placeholderClass}`).forEach(
      (elem) => {
        addPlaceholderEventListeners(elem);
        elem.classList.add(activeClass);
      },
    );

    target.querySelectorAll(`.${placeholderClass}`).forEach(
      (elem) => { elem.classList.remove(activeClass); },
    );
  }

  /**
   * @param {Event} event
   * @returns {void}
   */
  function touchmoveHandler(event) {
    event.preventDefault();
    draggedElement.style.left = `${event.touches[0].clientX - draggedElement.offsetWidth / 2}px`;
    draggedElement.style.top = `${event.touches[0].clientY - draggedElement.offsetHeight / 2}px`;

    const documentElement = parentElement.ownerDocument;
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
   * @returns {void}
   */
  function dragoverHandler(event) {
    event.preventDefault();
  }

  /**
   * @param {Event} event
   * @returns {void}
   */
  function dragenterHandler(event) {
    event.target.classList.add(hoverClass);
  }

  /**
   * @param {Event} event
   * @returns {void}
   */
  function dragleaveHandler(event) {
    event.target.classList.remove(hoverClass);
  }

  function dragendHandler() {
    draggedElement.classList.remove(draggingClass);
    parentElement.querySelectorAll(`.${placeholderClass}`).forEach(
      (elem) => {
        elem.classList.remove(activeClass);
        elem.classList.remove(hoverClass);
      },
    );
  }

  /**
   * @param {Event} event
   * @returns {void}
   */
  function dropHandler(event) {
    event.preventDefault();
    stopMoving(event.target);
  }

  /**
   * @param {Event} event
   * @returns {void}
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
   * @param {HTMLElement} target
   * @returns {void}
   */
  function stopMoving(target) {
    const source = draggedElement.parentNode;
    let droppedElement = draggedElement;
    if (dragType !== 'move') {
      droppedElement = draggedElement.cloneNode(true);
      addDragEventListeners(droppedElement);
    }
    target.parentNode.insertBefore(droppedElement, target);
    onChangeHandler(droppedElement, target, draggedElement, source);
  }

  /**
   * @param {HTMLElement} target
   * @returns {object}
   */
  function buildNodesForKeyboardInteraction(target) {
    const nodes = {
      nodeArray: [],
    };
    Array.from(
      parentElement.querySelectorAll(`.${draggableClass}, .${placeholderClass}`)
    ).forEach(
      (currentNode) => {
        if (currentNode === target) {
          nodes.nodeArray.push(currentNode);
          nodes.currentPosition  = nodes.nodeArray.length - 1;
          return;
        }

        if (currentNode.classList.contains(draggableClass)) {
          return;
        }

        nodes.nodeArray.push(currentNode);
      }
    );
    return nodes;
  }

  /**
   * @param {HTMLElement} target
   * @returns {void}
   */
  function startKeyboardInteraction(target) {
    startMoving(target);
    target.dataset.selected = true;
    nodesForKeyboardInteraction = buildNodesForKeyboardInteraction(target);

    const controller = new AbortController();
    target.addEventListener(
      'blur',
      () => {
        abortKeyboardInteraction(target, controller);
      },
      { signal: controller.signal },
    );
    accessibility.infoContainer.innerText = accessibility.texts.tagSelected(target);
  }

  /**
   * @param {HTMLElement} target
   * @param {AbortController} controller
   * @returns {void}
   */
  function abortKeyboardInteraction(target, controller) {
    target.dataset.selected = false;
    accessibility.infoContainer.innerText = accessibility.texts.default();

    if (typeof controller !== 'undefined') {
      controller.abort();
    }
    dragendHandler();
    stopMoving(target);
  }

  /**
    * @param {KeyEvent} event
    * @returns {void}
    */
  function keyboardControlHandler(event) {
    if (event.key === 'Tab' && event.target.dataset.selected === 'true') {
      event.preventDefault();
      return;
    }

    if (event.key === ' ' && event.target.dataset.selected === 'true') {
      event.target.dataset.selected = false;
      dragendHandler();
      stopMoving(nodesForKeyboardInteraction.nodeArray[nodesForKeyboardInteraction.currentPosition]);
      event.target.focus();
      return;
    }

    if (event.key === ' ') {
      startKeyboardInteraction(event.target);
      return;
    }

    if (event.key === 'Escape' && event.target.dataset.selected === 'true') {
      abortKeyboardInteraction(event.target);
      return;
    }

    if (event.key === 'ArrowLeft' && event.target.dataset.selected === 'true') {
      let previous = nodesForKeyboardInteraction.currentPosition - 1;
      if (previous < 0) {
        previous = nodesForKeyboardInteraction.nodeArray.length - 1;
      }
      parentElement.querySelector(`.${hoverClass}`)?.classList.remove(hoverClass);
      nodesForKeyboardInteraction.nodeArray[previous].classList.add(hoverClass);
      nodesForKeyboardInteraction.currentPosition = previous;
      accessibility.infoContainer.innerText = accessibility.texts
        .position(nodesForKeyboardInteraction.nodeArray[previous]);
      return;
    }

    if (event.key === 'ArrowRight' && event.target.dataset.selected === 'true') {
      let next = nodesForKeyboardInteraction.currentPosition + 1;
      if (next === nodesForKeyboardInteraction.nodeArray.length) {
        next = 0;
      }
      parentElement.querySelector(`.${hoverClass}`)?.classList.remove(hoverClass);
      nodesForKeyboardInteraction.nodeArray[next].classList.add(hoverClass);
      nodesForKeyboardInteraction.currentPosition = next;
      accessibility.infoContainer.innerText = accessibility.texts
        .position(nodesForKeyboardInteraction.nodeArray[next]);
    }
  }

  /**
   * @param {HTMLElement} elem
   * @returns {void}
   */
  function ensureDraggable(elem) {
    if (!elem.hasAttribute('draggable')) {
      elem.setAttribute('draggable', true);
    }
  }

  /**
   * @param {HTMLElement} elem
   * @returns {void}
   */
  function addDragEventListeners(elem) {
    elem.addEventListener('dragstart', dragstartHandler);
    elem.addEventListener('dragend', dragendHandler);
    elem.addEventListener('touchstart', touchstartHandler);
    elem.addEventListener('keydown', keyboardControlHandler);
  }

  /**
   * @param {HTMLElement} elem
   * @returns {void}
   */
  function addPlaceholderEventListeners(elem) {
    elem.removeEventListener('dragover', dragoverHandler);
    elem.removeEventListener('dragenter', dragenterHandler);
    elem.removeEventListener('dragleave', dragleaveHandler);
    elem.removeEventListener('drop', dropHandler);
    elem.removeEventListener('keydown', keyboardControlHandler);
    elem.addEventListener('dragover', dragoverHandler);
    elem.addEventListener('dragenter', dragenterHandler);
    elem.addEventListener('dragleave', dragleaveHandler);
    elem.addEventListener('drop', dropHandler);
    elem.addEventListener('keydown', keyboardControlHandler);
  }

  function initializeDraggableElements() {
    parentElement.querySelectorAll(`.${draggableClass}`).forEach((elem) => {
      ensureDraggable(elem);
      addDragEventListeners(elem);
    });
  }

  function initializeDomChangeObserver() {
    const tagAddedObserver = new parentElement.ownerDocument.defaultView.MutationObserver(
      (mutationList) => {
        mutationList.forEach((mutation) => {
          [...mutation.addedNodes].forEach((elem) => {
            if (elem.classList.contains(draggableClass)) {
              ensureDraggable(elem);
              addDragEventListeners(elem);
            }
          });
        });
      },
    );

    tagAddedObserver.observe(parentElement, {
      attributes: false,
      childList: true,
      subtree: false,
    });
  }

  function initializeAccessibility() {
    accessibility.infoContainer.innerText = accessibility.texts.default();
  }

  initializeDraggableElements();
  initializeDomChangeObserver();
  initializeAccessibility();
}
