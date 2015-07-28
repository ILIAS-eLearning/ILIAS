$(function () {

    var ilToolbar = (function ($) {

        var $header = $('.ilToolbar .navbar-header');
        var $toolbar_container = $('#tb-collapse-1');
        var primary_items = [];
        var initialized_responsive_mode = false;
        var $items = $toolbar_container.find('ul.navbar-nav li');

        // Private

        var getItemGroups = function ($items) {
            var groups = [];
            var group = [];
            $items.each(function () {
                var $item = $(this);
                if ($item.hasClass('ilToolbarSeparator')) {
                    groups.push(group);
                    group = [];
                } else {
                    group.push($item); // Add item to group
                }
            });

            return groups;
        };

        var groupItems = function () {
            var groups = getItemGroups($items);
            if (groups.length) {
                // We found at least one group
                for (var i in groups) {
                    var group = groups[i];
                    var $first_item = group[0];
                    var $form = $first_item.find('.navbar-form');
                    var $wrapper = $form.length ? $form : $first_item;
                    for (var j in group) {
                        var $item = group[j];
                        var $input = $item.find('select, input, a, span')
                            .css({'display': 'inline-block', 'width': 'auto'})
                            .addClass('group-item');
                        if (j > 0) {
                            // Append to first item in Toolbar
                            $input.appendTo($wrapper);
                            $item.addClass('hidden-item');
                        }
                    }
                }
            }
        };


        var ungroupItems = function () {
            var $grouped_items = $toolbar_container.find('.group-item');
            var $hidden_items = $toolbar_container.find('.hidden-item');
            $grouped_items.each(function(index) {
                var $item = $(this);
                if (index > 0) {
                    // Find the hidden li element where this item belongs to
                    var $li = $hidden_items.eq((index-1));
                    var $form = $li.find('.navbar-form');
                    var $wrapper = $form.length ? $form : $li;
                    $item.appendTo($wrapper);
                    $li.removeClass('hidden-item');
                }
                $item.removeClass('group-item');
            });
        };

        var addPrimaryButtonsToHeader = function () {
            var $primary_buttons = $toolbar_container.find('.btn-primary').parents('li');
            if ($primary_buttons.length && !primary_items.length) {
                var $container = $('<ul>').addClass('sticky-primary-items').appendTo($header);
                $primary_buttons.each(function () {
                    var $button = $(this);
                    primary_items.push({button: $button, prev: $button.prev('li')});
                });
                $primary_buttons.appendTo($container);
            }
        };

        var removePrimaryButtonsFromHeader = function () {
            for (var i in primary_items) {
                var item = primary_items[i];
                item.button.insertAfter(item.prev);
            }
            $('ul.sticky-primary-items').remove();
        };


        // Public

        var init = function () {
            var $last = $items.last();
            if ($last.hasClass('ilToolbarSeparator')) {
                // If the last item is a separator, remove its border style as this separator is (probably) only used
                // to group the previous items for mobile view
                $last.css('border', '0');
            }
        };

        /**
         * Optimize Toolbar for responsive view
         * - Always show primary buttons by moving them into the toolbar header
         * - Group toolbar items separated by a "Separator" on same line
         */
        var activateResponsiveMode = function () {
            if (initialized_responsive_mode) return; // Already initialized
            addPrimaryButtonsToHeader();
            groupItems();
            initialized_responsive_mode = true;
        };

        /**
         * Deactivate responsive mode and move primary buttons and items to their original position
         */
        var deactivateResponsiveMode = function () {
            if (!initialized_responsive_mode) return;
            removePrimaryButtonsFromHeader();
            ungroupItems();
            initialized_responsive_mode = false;
        };

        return {
            init: init,
            activateResponsiveMode: activateResponsiveMode,
            deactivateResponsiveMode: deactivateResponsiveMode
        };

    })($);

    var onResize = function () {
        var width = $(window).width();
        if (width < 750) {
            ilToolbar.activateResponsiveMode();
        } else {
            ilToolbar.deactivateResponsiveMode();
        }
    };

    ilToolbar.init();
    onResize();
    $(window).resize(onResize);
});
