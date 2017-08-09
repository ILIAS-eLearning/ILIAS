var il = il || {};
il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

(function($, UI) {

    UI.table.presentation = (function ($) {

        var expandRow = function (id) {
            var row = $('#' + id);
            row.find('.il-table-presentation-row-controls-expander').hide()
            row.find('.il-table-presentation-row-controls-collapser').show()

            row.find('.il-table-presentation-row-expanded').show()
            row.find('.il-table-presentation-row-header-fields').hide()

        };

        var collapseRow = function (id) {
            var row = $('#' + id);
            row.find('.il-table-presentation-row-controls-expander').show()
            row.find('.il-table-presentation-row-controls-collapser').hide()

            row.find('.il-table-presentation-row-expanded').hide()
            row.find('.il-table-presentation-row-header-fields').show()


        };

        return {
            expandRow: expandRow,
            collapseRow: collapseRow
        };

    })($);

})($, il.UI);