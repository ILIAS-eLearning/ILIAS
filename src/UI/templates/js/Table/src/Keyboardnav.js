class Keyboardnav {
    /**
     * @type {number}
     */
    #keyLeft;

    /**
     * @type {number}
     */
    #keyUp;

    /**
     * @type {number}
     */
    #keyRight;

    /**
     * @type {number}
     */
    #keyDown;

    constructor() {
        this.#keyLeft = 37;
        this.#keyUp = 38;
        this.#keyRight = 39;
        this.#keyDown = 40;
    }

    /**
     * @param {KeyboardEvent} event
     */
    navigateCellsWithArrowKeys(event) {
        if (!(event.which === this.#keyLeft
            || event.which === this.#keyUp
            || event.which === this.#keyRight
            || event.which === this.#keyDown
        )) {
            return;
        }

        const cell = event.target.closest('td, th');
        const row = cell.closest('tr');
        const table = row.closest('table');
        let { cellIndex } = cell;
        let { rowIndex } = row;

        switch (event.which) {
        case this.#keyLeft:
            cellIndex -= 1;
            break;
        case this.#keyRight:
            cellIndex += 1;
            break;
        case this.#keyUp:
            rowIndex -= 1;
            break;
        case this.#keyDown:
            rowIndex += 1;
            break;
        default:
            break;
        }

        if (rowIndex < 0 || cellIndex < 0
            || rowIndex >= table.rows.length
            || cellIndex >= row.cells.length
        ) {
            return;
        }
        this.focusCell(table, cell, rowIndex, cellIndex);
    }

    /**
     * @param {HTMLTableElement} table
     * @param {HTMLTableCellElement} cell
     * @param {number} rowIndex
     * @param {number} cellIndex
     */
    focusCell(table, cell, rowIndex, cellIndex) {
        const nextCell = table.rows[rowIndex].cells[cellIndex];
        nextCell.focus();
        cell.setAttribute('tabindex', -1);
        nextCell.setAttribute('tabindex', 0);
    }

    /**
     * @param {string} targetId
     */
    init(targetId) {
        document.getElementById(targetId)?.addEventListener('keydown', (event) => this.navigateCellsWithArrowKeys(event, this));
    }
}

export default Keyboardnav;
