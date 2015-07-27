$(function () {

    var ilToolbar = (function ($) {

        var $header = $('.ilToolbar .navbar-header');
        var primary_items = [];
        var initialized_responsive_mode = false;

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
            var $items = $('#tb-collapse-1').find('ul.navbar-nav li');
            var groups = getItemGroups($items);
            if (groups.length) {
                // We found at least one group
                for (var i in groups) {
                    var group = groups[i];
                    var $first_item = group[0];
                    var $wrapper = $first_item.find('.navbar-form').length ? $first_item.find('.navbar-form') : $first_item;
                    for (var j in group) {
                        var $item = group[j];
                        var $input = $item.find('select, input, a, span')
                            .css({'display': 'inline-block', 'width': 'auto'})
                            .addClass('group-item');
                        if (j > 0) {
                            // Append to first item
                            $input.appendTo($wrapper);
                            $item.addClass('hidden-item');
                        }
                    }
                }
            }
        };


        var ungroupItems = function () {
            var $container = $('#tb-collapse-1');
            var $items = $container.find('.group-item');
            var $hidden_lis = $container.find('.hidden-item');
            $items.each(function(index) {
                var $item = $(this);
                if (index > 0) {
                    // Find the hidden li element where this item belongs to
                    var $li = $hidden_lis.eq((index-1));
                    var $wrapper = $li.find('.navbar-form').length ? $li.find('.navbar-form') : $li;
                    console.log($wrapper);
                    $item.appendTo($wrapper);
                    $li.removeClass('hidden-item');
                }
                $item.removeClass('group-item');
            })
        };

        // Public

        /**
         * Optimize Toolbar for responsive view
         * - Always show primary buttons by moving them into the header
         * - Group toolbar items separated by a "Separator" on same line
         */
        var activateResponsiveMode = function () {
            if (initialized_responsive_mode) return; // Already initialized
            var $content = $('#tb-collapse-1');
            var $primary_buttons = $content.find('.btn-primary').parents('li');
            if ($primary_buttons.length && !primary_items.length) {
                var $container = $('<ul>').addClass('sticky-primary-items').appendTo($header);
                $primary_buttons.each(function () {
                    var $button = $(this);
                    primary_items.push({button: $button, prev: $button.prev('li')});
                });
                $primary_buttons.appendTo($container);
            }

            // Group items
            groupItems();
            initialized_responsive_mode = true;
        };

        var deactivateResponsiveMode = function () {
            if (!initialized_responsive_mode) return;
            for (var i in primary_items) {
                var item = primary_items[i];
                item.button.insertAfter(item.prev);
            }
            $('ul.sticky-primary-items').remove();
            ungroupItems();
            initialized_responsive_mode = false;
        };

        return {
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

    onResize();
    $(window).resize(onResize);
});
