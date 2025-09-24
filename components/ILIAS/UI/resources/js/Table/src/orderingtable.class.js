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

// Constants for vertical scroll behavior during touch drag and drop
const SCROLL_TRIGGER_ZONE = 100; // px at top and bottom
const SCROLL_STEP = 8; // px distance to scroll during time interval
const SCROLL_TIME_INTERVAL = 16; // ms

// offset from finger position of drag image during touch drag and drop
const DRAG_IMAGE_X_OFFSET = -50; // px

export default class OrderingTable {
  /**
   * @type {HTMLDivElement}
   */
  #component;

  /**
   * @type {HTMLTableElement}
   */
  #table;

  /**
   * @type {Array<HTMLTableRowElement>}
   */
  #rows;

  /**
   * The original row that is being dragged.
   * @type {HTMLTableRowElement}
   */
  #originRow;

  /**
   * The clone that will follow the cursor (drag image).
   * @type {HTMLTableRowElement}
   */
  #dragImage;

  /**
   * The placeholder inserted into the table.
   * @type {HTMLTableRowElement}
   */
  #placeholderRow;

  /**
   * @param {string} componentId
   * @throws {Error} if DOM element is missing
   */
  constructor(componentId) {
    this.#component = document.getElementById(componentId);
    if (this.#component === null) {
      throw new Error(`Could not find a OrderingTable for id '${componentId}'.`);
    }
    this.#table = this.#component.getElementsByTagName('table').item(0);
    if (this.#table === null) {
      throw new Error('There is no <table> in the component\'s HTML.');
    }
    this.#indexRows();
    this.#rows.forEach((row) => this.#addDraglisteners(row));
  }

  #indexRows() {
    this.#rows = Array.from(this.#table.rows);
    this.#rows.shift(); // exclude header
    this.#rows.pop(); // exclude footer
  }

  #addDraglisteners(row) {
    row.addEventListener('dragstart', (event) => this.dragstart(event));
    row.addEventListener('dragover', (event) => this.dragover(event));
    row.addEventListener('dragend', (event) => this.dragend(event));

    row.addEventListener('touchstart', (event) => this.touchstart(event));
    row.addEventListener('touchmove', (event) => this.touchmove(event));
    row.addEventListener('touchend', (event) => this.touchend(event));
    row.addEventListener('touchcancel', (event) => this.touchend(event));
  }

  dragstart(event) {
    this.#component.classList.add('dragInProgress');

    this.#originRow = event.target.closest('tr');
    event.dataTransfer.clearData(); // prevents Modification Not Allowed in Firefox
    event.dataTransfer.setData('text/html', this.#originRow.outerHTML);
    event.dataTransfer.setData('text/plain', this.#originRow.textContent.replace(/[\n\r]+|[\s]{2,}/g, ' ').trim());

    // Create the drag image
    this.#dragImage = this.#originRow.cloneNode(true);
    this.#dragImage.classList.add('c-table-data__row--drag-image');
    this.#dragImage.style.top = '-9999px';
    this.#component.appendChild(this.#dragImage);
    event.dataTransfer.setDragImage(this.#dragImage, 0, 0);

    // Create the placeholder gap between rows to indicate where the item will be dropped
    this.#placeholderRow = this.#originRow.cloneNode(true);
    // adding dragover with preventDefault on placeholder stops fly back animation of some browsers
    this.#placeholderRow.addEventListener('dragover', (devent) => this.dragover(devent));
    this.#placeholderRow.classList.add('c-table-data__row--placeholder');
    Array.from(this.#placeholderRow.getElementsByTagName('td'))
      .forEach((cell) => {
        cell.innerHTML = '';
      });

    // an indicator at the position where the item was before
    this.#originRow.classList.add('c-table-data__row--drag-origin');
  }

  dragover(event) {
    if (!this.#isDraggedElementValidRow()) return;
    event.preventDefault();
    event.dataTransfer.effectAllowed = 'copyMove';
    const target = event.target.closest('tr');
    if (target && target !== this.#placeholderRow) {
      if (this.#rows.indexOf(target) > this.#rows.indexOf(this.#originRow)) {
        target.after(this.#placeholderRow);
      } else {
        target.before(this.#placeholderRow);
      }
    }
  }

  dragend(event) {
    event.preventDefault();

    // prevent flyback animation by removing the dragImage before some browsers animate it
    if (this.#dragImage && this.#component.contains(this.#dragImage)) {
      this.#component.removeChild(this.#dragImage);
      this.#dragImage = null;
    }

    this.#placeholderRow.replaceWith(this.#originRow); // drop original where placeholder was

    this.#originRow.classList.remove('c-table-data__row--drag-origin');
    this.#originRow.classList.add('c-table-data__row--drag-settle');
    this.#originRow.addEventListener(
      'animationend',
      () => this.#cleanUpAfterAnimation(),
      { once: true },
    );
    this.#component.classList.remove('dragInProgress');
    this.#indexRows();
    this.#renumberAfterDrag();
  }

  #cleanUpAfterAnimation() {
    this.#originRow.classList.remove('c-table-data__row--drag-settle');
  }

  #isDraggedElementValidRow() {
    return this.#rows.includes(this.#originRow);
  }

  // Drag and Drop on touch devices
  touchstart(event) {
    this.#originRow = event.target.closest('tr');

    this.#placeholderRow = this.#originRow.cloneNode(true);
    this.#placeholderRow.classList.add('c-table-data__row--placeholder');
    Array.from(this.#placeholderRow.getElementsByTagName('td'))
      .forEach(
        (cell) => {
          cell.innerHTML = '';
        },
      );

    // drag image for touch
    this.#dragImage = this.#originRow.cloneNode(true);
    this.#dragImage.classList.add('c-table-data__row--touch-drag-image');
    this.#component.appendChild(this.#dragImage);

    this.#originRow.classList.add('c-table-data__row--drag-origin');
  }

  touchmove(event) {
    event.preventDefault();
    const touch = event.touches[0];

    this.#dragImage.style.left = `${touch.clientX + DRAG_IMAGE_X_OFFSET}px`;
    this.#dragImage.style.top = `${touch.clientY}px`;

    const target = document.elementFromPoint(touch.clientX, touch.clientY)?.closest('tr');
    if (target && this.#rows.includes(target)) {
      if (this.#rows.indexOf(target) > this.#rows.indexOf(this.#originRow)) {
        target.after(this.#placeholderRow);
      } else {
        target.before(this.#placeholderRow);
      }
    }

    // Scroll viewport if near edges
    if (touch.clientY < SCROLL_TRIGGER_ZONE) {
      this.#startScrolling(-SCROLL_STEP);
    } else if (touch.clientY > window.innerHeight - SCROLL_TRIGGER_ZONE) {
      this.#startScrolling(SCROLL_STEP);
    } else {
      this.#stopScrolling();
    }
  }

  touchend() {
    this.#component.removeChild(this.#dragImage);
    this.#placeholderRow.replaceWith(this.#originRow);
    this.#originRow.classList.remove('c-table-data__row--drag-origin');
    this.#indexRows();
    this.#renumberAfterDrag();
  }

  #renumberAfterDrag() {
    let pos = 10;
    this.#table.querySelectorAll('input[type="number"]').forEach((input) => {
      input.value = pos;
      pos += 10;
    });
  }

  #startScrolling(step) {
    if (this.scrollInterval) return;
    this.scrollInterval = setInterval(() => {
      window.scrollBy(0, step);
    }, SCROLL_TIME_INTERVAL);
  }

  #stopScrolling() {
    if (this.scrollInterval) {
      clearInterval(this.scrollInterval);
      this.scrollInterval = null;
    }
  }
}
