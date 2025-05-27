/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ******************************************************************** */

il = il || {};
il.DataCollection = il.DataCollection || {};

(function ($, il) {
  il.DataCollection = (function($) {
    $.fn.extend({
      multi_line_input: function () {

        var settings = $.extend({ unique_values: false });

        var element = this;
        var counter = 1;
        var clone_line = $(this).find('.multi_input_line').first();
        var empty_id = "empty";
        var date_config = {
          "stepping": 5,
          "useCurrent": false,
          "calendarWeeks": true,
          "toolbarPlacement": "top",
          "showClear": true,
          "keepInvalid": true,
          "sideBySide": true,
          "format": "DD.MM.YYYY"
        };

        var setup_clone_line = function (clone_line) {
          clone_line.hide();
          clone_line.removeClass('multi_input_line');

          clone_line.find("textarea[name^='" + element.attr('id') + "'], input[name^='" + element.attr(
            'id') + "'], select[name^='" + element.attr('id') + "']").each(function () {
            var name = $(this).attr('name');
            var id = element.attr('id');
            var regex = new RegExp('^' + id + '\[[0-9]+\](.*)$', 'g');
            var matches = regex.exec(name);
            name = empty_id + '[' + counter + ']' + matches[1];
            $(this).attr('name', name);
          });
        };

        setup_clone_line(clone_line);

        var setup_line = function (line, init) {
          var init = init || false;
          var $line = line;

          $(line).find('.add_button').on('click', function (e) {
            var new_line = clone_line.clone();
            new_line.show();
            $(new_line).addClass("multi_input_line");

            setup_line(new_line);

            $(new_line).insertAfter($(this).parent().parent());

            var $div = new_line.find('.date');
            if (typeof $div !== 'undefined') {
              $div.datetimepicker(date_config);
            }

            $(element).change();
            $(document).trigger('multi_line_add_button', [$line, new_line]);
            return false;
          });

          $(line).find('.remove_button').on('click', function (e) {
            if ($(line).parent().children().length > 2) {
              $line.remove();
            } else {
              $line.find('input').val("");
            }
            $(element).change();
            $(document).trigger('multi_line_remove_button', $line);
            return false;
          });

          $(line).find('.up_button').on('click', function (e) {
            $(line).insertBefore($(line).prev());
          });

          $(line).find('.down_button').on('click', function (e) {
            $(line).insertAfter($(line).next());
          });

          if (!init) {
            $line.find("textarea[name^='" + empty_id + "'], input[name^='" + empty_id + "'], select[name^='" + empty_id + "']")
            .each(function () {
              var name = $(this).attr('name');
              var id = element.attr('id');
              $(this).val('');
              var regex = new RegExp('^' + empty_id + '\[[0-9]+\](.*)$', 'g');
              var matches = regex.exec(name);
              name = id + '[' + counter + ']' + matches[1];
              i = 1;
              while ($("[name='" + name + "']").length) {     // while element with this id already exists, take next id
                name = id + '[' + (counter + i) + ']' + matches[1];
                i++;
              }
              $(this).attr('name', name);
            });
          }
          counter++;
        };

        $(element).on('change', function (e) {
          var remove_buttons = $(element).find('.multi_input_line .remove_button');
        });

        $(this).find('.multi_input_line').each(function () {
          setup_line($(this), true);
        });
        $(element).change();

        return element;
      }
    });

    var genericMultiLineInit = function genericMultiLineInit(id) {
      $("#"+id).multi_line_input();
    };

    return {
      genericMultiLineInit: genericMultiLineInit,
    };
  }($));
}($, il));
