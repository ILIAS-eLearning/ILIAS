var il = il || {};
il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

(function($, UI) {

    UI.table.presentation = (function ($) {
        _cls_collapsed = 'collapsed';
        _cls_expanded = 'expanded';

        var expandRow = function (id) {
            var row = $('#' + id);
            row.find('.il-table-presentation-row-controls-expander').hide();
            row.find('.il-table-presentation-row-controls-collapser').show();
            row.find('.il-table-presentation-row-expanded').show();
            row.find('.il-table-presentation-row-header-fields').hide();
            row.removeClass(_cls_collapsed);
            row.addClass(_cls_expanded);
        };

        var collapseRow = function (id) {
            var row = $('#' + id);
            row.find('.il-table-presentation-row-controls-expander').show();
            row.find('.il-table-presentation-row-controls-collapser').hide();
            row.find('.il-table-presentation-row-expanded').hide();
            row.find('.il-table-presentation-row-header-fields').show();
            row.removeClass(_cls_expanded);
            row.addClass(_cls_collapsed);
        };

        var toggleRow = function (id) {
            var row = $('#' + id);
            row.find('.il-table-presentation-row-controls-expander').toggle();
            row.find('.il-table-presentation-row-controls-collapser').toggle();
            row.find('.il-table-presentation-row-expanded').toggle();
            row.find('.il-table-presentation-row-header-fields').toggle();
            if(row.hasClass(_cls_expanded)) {
                row.removeClass(_cls_expanded);
                row.addClass(_cls_collapsed);
            } else {
                row.removeClass(_cls_collapsed);
                row.addClass(_cls_expanded);
            }
        };

        return {
            expandRow: expandRow,
            collapseRow: collapseRow,
            toggleRow: toggleRow
        };

    })($);

})($, il.UI);