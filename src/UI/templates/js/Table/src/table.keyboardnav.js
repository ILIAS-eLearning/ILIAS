class keyboardnav {
    keys = {
        ESC: 27,
        SPACE: 32,
        PAGE_UP: 33,
        PAGE_DOWN: 34,
        END: 35,
        HOME: 36,
        LEFT: 37,
        UP: 38,
        RIGHT: 39,
        DOWN: 40
    };
    supported_keys = [ 
        this.keys.LEFT,
        this.keys.RIGHT, 
        this.keys.UP, 
        this.keys.DOWN
    ];
     
    /**
     * @param Event event
     * @param keyboardnav _self
     */
    #onKey(event, _self) {

        if (_self.supported_keys.indexOf(event.which) === -1) {
            return;
        }

        var cell = event.target.closest('td, th'),
            row = cell.closest('tr'),
            table = row.closest('table'),
            cell_index = cell.cellIndex, 
            row_index = row.rowIndex;

        switch (event.which) {
            case _self.keys.LEFT:
                cell_index -= 1;
                break;
            case _self.keys.RIGHT:
                cell_index += 1;
                break;
            case _self.keys.UP: 
                row_index = row_index -= 1;
                break;
            case _self.keys.DOWN:
                row_index = row.rowIndex + 1;
                break;
          }
        
        if (row_index < 0 || cell_index < 0
            || row_index >= table.rows.length 
            || cell_index >= row.cells.length
        ) {
            return;
        }
        _self.#focusCell(table, cell, row_index, cell_index);
    }

    #focusCell(table, cell, row_index, cell_index) {
        var next_cell = table.rows[row_index].cells[cell_index];
        next_cell.focus();
        cell.setAttribute('tabindex', -1);
        next_cell.setAttribute('tabindex', 0);
    }

    /**
     * @param string target_id
     */
    init(target_id) {
        document.querySelector('#' + target_id).addEventListener('keydown', (event)=>this.#onKey(event, this));
    }

}

export default keyboardnav;