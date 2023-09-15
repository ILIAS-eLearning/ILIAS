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
      multi_line_input: function (element_config, options) {

        var settings = $.extend({
          unique_values: false
        }, options);

        var element_config = element_config;
        var element = this;
        var limit = options.limit;
        var sortable = options.sortable;
        var counter = 1;
        var clone_line = $(this).find('.multi_input_line').first();
        var empty_id = "empty";
        var date_config = {
          "locale": options.locale,
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
          //counter++;
        };

        setup_clone_line(clone_line);

        var setup_line = function (line, init) {
          var init = init || false;
          var $line = line;


          // '$("#'.$a_id.'").datetimepicker('.json_encode($config).')'


          $(line).find('.add_button').on('click', function (e) {
            console.log('clicked');

            var $length = $('.multi_input_line').length;
            console.log(limit,$length);

            if (limit == 0 || $length < limit) {
              var new_line = clone_line.clone();
              new_line.show();
              $(new_line).addClass("multi_input_line");

              setup_line(new_line);

              console.log($(this).parent().parent());
              $(new_line).insertAfter($(this).parent().parent());

              // if date input, configure datetimepicker
              var $div = new_line.find('.date');
              if (typeof $div !== 'undefined') {
                $div.datetimepicker(date_config);
              }

              $(element).change();
              $(document).trigger('multi_line_add_button', [$line, new_line]);
              return false;
            }
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

          if (sortable) {
            $(line).find('.up_button').on('click', function (e) {
              $(line).insertBefore($(line).prev());
            });

            $(line).find('.down_button').on('click', function (e) {
              $(line).insertAfter($(line).next());
            });
          }

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
                console.log('element exists: ' + (counter + i));
                i++;
              }
              $(this).attr('name', name);
            });
          }
          counter++;
        };

        // hide/show delete icons
        $(element).on('change', function (e) {
          var remove_buttons = $(element).find('.multi_input_line .remove_button');

          // if (remove_buttons.length > 1) {
          // 	remove_buttons.show();
          // } else {
          // 	remove_buttons.hide();
          // }
        });

        $(this).find('.multi_input_line').each(function () {
          setup_line($(this), true);
        });
        $(element).change();

        return element;
      }
    });

    var genericMultiLineInit = function genericMultiLineInit(id,element_config, options) {
      console.log(id,element_config, options);
      $("#"+id).multi_line_input(element_config, options);
    };

    return {
      genericMultiLineInit: genericMultiLineInit,
    };
  }($));
}($, il));