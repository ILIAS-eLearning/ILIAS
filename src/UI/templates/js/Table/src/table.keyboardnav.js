
var keyboardnav = function() {
    var 
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
    },

    onKey = function(event) {
        var supported_keys = [ 
            keys.LEFT, keys.RIGHT, keys.UP, keys.DOWN
        ];
        if (supported_keys.indexOf(event.which) === -1) {
            return;
        }

        var cell = event.target.closest('td, th'),
            row = cell.closest('tr'),
            table = row.closest('table'),
            cell_index = cell.cellIndex, 
            row_index = row.rowIndex;

        switch (event.which) {
            case keys.LEFT:
                cell_index -= 1;
                break;
            case keys.RIGHT:
                cell_index += 1;
                break;
            case keys.UP: 
                row_index = row_index -= 1;
                break;
            case keys.DOWN:
                row_index = row.rowIndex + 1;
                break;
          }
        
        if (row_index < 0 || cell_index < 0
            || row_index >= table.rows.length 
            || cell_index >= row.cells.length
        ) {
            return;
        }
        focusCell(table, cell, row_index, cell_index);
    },

    focusCell = function(table, cell, row_index, cell_index) {
        var next_cell = table.rows[row_index].cells[cell_index];
        next_cell.focus();
        cell.setAttribute('tabindex', -1);
        next_cell.setAttribute('tabindex', 0);
    },

    init = function(target_id) {
        document.querySelector('#' + target_id).addEventListener('keydown', onKey);
    },

    public_interface = {
        init: init,
    };

    return public_interface;
}

export default keyboardnav;