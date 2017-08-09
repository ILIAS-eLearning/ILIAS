var il = il || {};
il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

(function($, UI) {

    UI.table.presentation = (function ($) {



        var expandRow = function (id) {
            window.top.console.log('EXPAND ' + id);
        };

        var collapseRow = function (id) {
        };

        return {
            expandRow: expandRow,
            collapseRow: collapseRow
        };

    })($);

})($, il.UI);