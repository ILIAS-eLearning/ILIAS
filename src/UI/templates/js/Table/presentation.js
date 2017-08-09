var il = il || {};
il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

(function($, UI) {

    UI.table.presentation = (function ($) {

        var expandRow = function (id) {
            var row = $('#' + id);
            row.find('.il-table-presentation-row-expander').hide()
            row.find('.il-table-presentation-row-collapser').show()

            row.find('.il-table-presentation-row-expanded').show()
            row.find('.il-table-presentation-row-header-fields').hide()

        };

        var collapseRow = function (id) {
            var row = $('#' + id);
            row.find('.il-table-presentation-row-expander').show()
            row.find('.il-table-presentation-row-collapser').hide()

            row.find('.il-table-presentation-row-expanded').hide()
            row.find('.il-table-presentation-row-header-fields').show()


        };

        return {
            expandRow: expandRow,
            collapseRow: collapseRow
        };

    })($);

})($, il.UI);