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
 */

(function ($) {
  $.fn.extend({
    study_programme_tree(options) {
      const settings = $.extend({
        button_selectors: {
          all: '.tree_button', create: 'button.cmd_create', info: 'button.cmd_view', delete: 'button.cmd_delete',
        },
        current_node_selector: '.current_node',
        save_tree_url: '',
        save_button_id: '',
        cancel_button_id: '',
      }, options);

      const element = this;
      let tree_buttons_disabled = false;

      // helper functions

      /**
             * reloads the js tree
             */
      const refresh_tree = function (force_reload) {
        if (force_reload) {
          window.location.reload(true);
        } else {
          $(element).jstree('refresh');
        }
      };

      /**
             * Enables or disable the save-order and cancel-order button in the toolbar
             * @param enabled
             */
      const enable_control_buttons = function (enabled) {
        if (settings.save_button_id !== '') {
          if (enabled) {
            $(`#${settings.save_button_id}`).removeClass('disabled');
          } else {
            $(`#${settings.save_button_id}`).addClass('disabled');
          }
        }
        if (settings.cancel_button_id !== '') {
          if (enabled) {
            $(`#${settings.cancel_button_id}`).removeClass('disabled');
          } else {
            $(`#${settings.cancel_button_id}`).addClass('disabled');
          }
        }
      };

      /**
             * Shows and hides all buttons on the tree nodes
             * This is used to remove all buttons if there are changes in the tree structure
             * @param enable
             */
      const enable_all_buttons = function (enable) {
        const buttons = element.find(settings.button_selectors.all);
        if (enable) {
          buttons.show();
        } else {
          buttons.hide();
        }

        tree_buttons_disabled = !enable;
      };

      /**
             * Hides all remove buttons from parents of the current selected element
             * This avoids loosing the reference to the current page
             */
      const handle_delete_buttons = function () {
        element.find(settings.button_selectors.delete).show();
        element.find(settings.current_node_selector).parents('li').each(function () {
          $(this).find(`> ${settings.button_selectors.delete}`).hide();
        });
      };

      /**
             * Defines drag & drop rules for tree-elements
             */
      const initDndTargetChecking = function () {
        // https://www.jstree.com/api/#/?q=$.jstree.defaults.dnd&f=$.jstree.defaults.core.check_callback
        $(element).jstree(true).settings.core.check_callback = function (operation, node, node_parent, node_position, more) {
          // Only allow drag if
          // - it does not create a new root,
          // - the target is not a lp-object,
          // - the target has no children or the type matches
          //      (only allow lp objects dropping if the new parent has lp-object children
          //      or only allow containers drop in container with other containers)

          const drop_above_root = node_parent.id === '#';
          let allowed_drag = false;
          let source; let target;
          let source_is_lp_obj; let target_is_lp_obj; let target_is_empty; let
            target_has_lp_content;

          if (!drop_above_root) {
            source = $(`#${node.id}`);
            target = $(`#${node_parent.id}`);

            // TODO: implement better/faster way to get information about node-types (identifier classes should be added to li-element)
            source_is_lp_obj = source.find('span.ilExp2NodeContent>span.title').first().hasClass('lp-object'),
            target_is_lp_obj = target.find('span.ilExp2NodeContent>span.title').first().hasClass('lp-object'),
            target_is_empty = target.find('ul > li > a > span.ilExp2NodeContent > span.title').length === 0,
            target_has_lp_content = target.find('ul > li > a > span.ilExp2NodeContent > span.title').first().hasClass('lp-object'),

            allowed_drag = (
              target_is_lp_obj === false
                            && (target_is_empty || (target_has_lp_content === source_is_lp_obj))
            );
          }

          if (allowed_drag) {
            return true;
          }
          return false;
        };
      };

      // JsTree events handlers

      /**
             * Controls toolbar buttons and tree-buttons when there are changes in the tree-structure
             */
      element.on('move_node.jstree', (event, data) => {
        enable_control_buttons(true);

        enable_all_buttons(false);
      });

      /**
             * root of the tree is loaded
             * Hides order-save and cancel buttons and removes delete buttons of all parents of the current element (handle_delete_buttons) and
             * init the Drag & Drop handling
             */
      element.on('loaded.jstree', (event, data) => {
        data.instance.settings.core.check_callback = initDndTargetChecking;

        enable_control_buttons(false);

        // hmmmm ugly js workaround: ready event does not exists in this version of jstree
        window.setTimeout(handle_delete_buttons, 500);
      });

      /**
             * Invoked when new nodes are loaded
             * Add or remove buttons of new nodes
             */
      element.on('load_node.jstree', (event, data) => {
        if (tree_buttons_disabled) {
          enable_all_buttons(false);
        }
      });

      // general events handled

      /**
             * Async form is successfully saved
             * Trigger notification and refreshes the tree
             */
      $('body').on('async_form-success', (event, data) => {
        // hmmmm ugly workaround: js-tree does not correctly refresh, when no element is available
        if ($(element).find('li > ul > li').length == 0 || (data.cmd == 'confirmedDelete' && $(element).find('li > ul > li').length == 1)) {
          refresh_tree(true);
        } else {
          $('body').trigger('study_programme-show_success', { message: data.message, type: 'success', cmd: data.cmd });
          refresh_tree(true);
        }
      });

      /**
             * New order was saved
             * Disables toolbar buttons and show tree buttons
             */
      $('body').on('study_programme-saved_order', (event, data) => {
        enable_control_buttons(false);
        enable_all_buttons(true);
      });

      /**
             * Cancel order save
             * Reset buttons and refresh the tree
             */
      $('body').on('study_programme-cancel_order', (event, data) => {
        enable_control_buttons(false);
        enable_all_buttons(true);

        refresh_tree();
      });

      /**
             * Saves the tree-order async
             */
      $('body').on('study_programme-save_order', () => {
        const tree_data = $(element).jstree(true).get_json('#', { flat: false });
        const json_data = JSON.stringify(tree_data);

        if (settings.save_tree_url !== '') {
          $.ajax({
            url: decodeURIComponent(settings.save_tree_url),
            type: 'post',
            dataType: 'json',
            data: { tree: json_data },
            success(response) {
              // try {
              if (response) {
                $('body').trigger('study_programme-show_success', { message: response.message, type: 'success', cmd: response.cmd });
                $('body').trigger('study_programme-saved_order');
              }
              /* } catch (error) {
                             console.log("The AJAX-response for the async form " + form.attr('id') + " is not JSON. Please check if the return values are set correctly: " + error);
                             } */
            },
          });
        }
      });

      return element;
    },

    study_programme_modal(options) {
      const settings = $.extend({
        events: { hide: ['async_form-success', 'async_form-cancel'] },
      }, options);

      const element = this;

      /**
             * Remove data in bootstrap overlay when closed
             */
      $(document).on('hidden.bs.modal', '.modal', (e) => {
        // only remove on study_programme_modal
        if ($(e.target).attr('id') === $(element).attr('id')) {
          $(e.target).removeData('bs.modal');
          $(e.target).find('.modal-content').empty();
        }
      });

      /**
             * Add modal events
             */
      $.each(settings.events, (modal_trigger, events) => {
        $.each(events, (key, event) => {
          $('body').on(event, () => {
            element.modal(modal_trigger);
          });
        });
      });

      return element;
    },
    study_programme_notifications(options) {
      const settings = $.extend({
        templates: {
          info: '', success: '', failure: '', question: '',
        },
        events: {
          info: [], success: [], failure: [], question: [],
        },
        message_var: '[MESSAGE]',
        message_delay: 3000,
      }, options);

      const content_container = this;

      /**
             * Renders notification and display it
             *
             * @param event
             * @param data
             */
      const displayMessage = function (event, data) {
        if (data.message) {
          data.type = data.type || 'info';

          let template = settings.templates[data.type];

          if (template !== '') {
            template = template.replace(settings.message_var, data.message);
          }
          $(content_container).prepend(template);
          $('div[role="alert"]').delay(settings.message_delay).slideUp('slow', function () {
            $(this).remove();
          });
        }
      };

      /**
             * Add all message display events
             */
      $.each(settings.events, (type, events) => {
        $.each(events, (key, val) => {
          $('body').on(val, displayMessage);
        });
      });

      return content_container;
    },
    study_programme_async_explorer(options) {
      const settings = $.extend({
        save_explorer_url: '',
      }, options);

      const element = this;

      const save_explorer_data = function (formData) {
        $.ajax({
          url: decodeURIComponent(settings.save_explorer_url),
          type: 'post',
          dataType: 'json',
          data: formData,
          success(response) {
            // try {
            if (response) {
              $('body').trigger('async_form-success', { message: response.message, type: 'success', cmd: response.cmd });
            }
            /* } catch (error) {
                         console.log("The AJAX-response for the async form " + form.attr('id') + " is not JSON. Please check if the return values are set correctly: " + error);
                         } */
          },
        });
      };

      $('body').on('async_explorer-add_reference', (event, data) => {
        save_explorer_data(data);
      });
    },
  });
}(jQuery));
