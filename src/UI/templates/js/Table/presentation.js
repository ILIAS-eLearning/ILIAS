var il = il || {};
il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

(function($, UI) {

    UI.table.presentation = (function ($) {

        var expandRow = function (id) {
            var row = $('#' + id);
            row.find('.il-table-presentation-row-controls-expander').hide();
            row.find('.il-table-presentation-row-controls-collapser').show();
            row.find('.il-table-presentation-row-expanded').show();
            row.find('.il-table-presentation-row-header-fields').hide();
        };

        var collapseRow = function (id) {
            var row = $('#' + id);
            row.find('.il-table-presentation-row-controls-expander').show();
            row.find('.il-table-presentation-row-controls-collapser').hide();
            row.find('.il-table-presentation-row-expanded').hide();
            row.find('.il-table-presentation-row-header-fields').show();
        };

        var toggleRow = function (id) {
            var row = $('#' + id);
            row.find('.il-table-presentation-row-controls-expander').toggle();
            row.find('.il-table-presentation-row-controls-collapser').toggle();
            row.find('.il-table-presentation-row-expanded').toggle();
            row.find('.il-table-presentation-row-header-fields').toggle();
        };

        return {
            expandRow: expandRow,
            collapseRow: collapseRow,
            toggleRow: toggleRow
        };

    })($);

})($, il.UI);