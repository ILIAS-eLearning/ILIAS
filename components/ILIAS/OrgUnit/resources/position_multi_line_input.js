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

/* global jQuery */
window.il = window.il || {};
window.il.OrgUnit = window.il.OrgUnit || {};

(function (jq) {
  /**
   * Multi-line form rows for OrgUnit position authorities.
   * The row with #multi_line_add_button is a prototype: no remove action, only add.
   */
  window.il.OrgUnit = (() => {
    const EMPTY_ID = 'empty';
    const MIN_LAST_CELL_WIDTH = 150;
    const WIDTH_OFFSET = 100;

    jq.fn.extend({
      positionMultiLineInit() {
        const element = this;
        let counter = 0;
        const cloneTemplate = jq(this).find('.multi_input_line').first();

        const isStandaloneAddLine = (line) => jq(line).find('#multi_line_add_button').length > 0;

        /**
         * Authority rows only (not the standalone “add first row” prototype).
         * Do not use :visible — on first paint the wrapper may still be display:none
         * until the next onLoad statement runs, which would hide every nested row from :visible.
         */
        const getAuthorityLines = () => jq(element)
          .find('.multi_input_line')
          .filter((__, el) => !isStandaloneAddLine(el));

        const setPrototypeRemoveVisibility = (standaloneAddButton) => {
          if (standaloneAddButton.length === 0) {
            return;
          }
          const prototypeRow = standaloneAddButton.closest('.multi_input_line');
          prototypeRow.find('.remove_button').hide();
        };

        const updatePrototypeVisibility = () => {
          const standaloneAddButton = jq(element).find('#multi_line_add_button');
          if (standaloneAddButton.length > 0) {
            const standaloneLine = standaloneAddButton.closest('.multi_input_line');
            if (getAuthorityLines().length === 0) {
              standaloneLine.show();
              standaloneAddButton.show();
            } else {
              standaloneAddButton.hide();
              standaloneLine.hide();
            }
            setPrototypeRemoveVisibility(standaloneAddButton);
          }
        };

        const calcWidth = (row) => {
          if (row.find('.ml-input').length === 0) {
            return;
          }
          const iconsWidth = row.find('.multi_icons_wrapper').last().width() || 0;
          let sumWidths = iconsWidth;
          row.find('.ml-input').each((_, inputCell) => {
            sumWidths += jq(inputCell).width() || 0;
          });
          const lastInputWidth = row.find('.ml-input').last().width() || 0;
          const usedWidth = sumWidths - lastInputWidth;
          const remainingWidth = (row.width() || 0) - usedWidth - WIDTH_OFFSET;
          const lastCell = row.find('.ml-input').last();
          if (remainingWidth > MIN_LAST_CELL_WIDTH) {
            lastCell.width(remainingWidth);
          } else {
            lastCell.css('width', '');
          }
        };

        jq(this).find('.multi_input_line').each((_, el) => {
          calcWidth(jq(el));
        });

        const setupCloneLine = (template) => {
          template.hide();
          template.removeClass('multi_input_line');
          const fieldId = element.attr('id') || '';
          const nameSelector = `textarea[name^='${fieldId}'], input[name^='${fieldId}'], select[name^='${fieldId}']`;
          template.find(nameSelector).each((_, inputEl) => {
            const input = jq(inputEl);
            const name = input.attr('name');
            const regex = new RegExp(`^${fieldId}\\[[0-9]+\\](.*)$`);
            const matches = regex.exec(name);
            if (!matches) {
              return;
            }
            input.attr('name', `${EMPTY_ID}[${counter}]${matches[1]}`);
          });
        };

        setupCloneLine(cloneTemplate);

        const setupLine = (line, isInit = false) => {
          const $line = line;

          jq(line).find('.add_button').on('click', () => {
            const newLine = cloneTemplate.clone();
            newLine.show();
            newLine.addClass('multi_input_line');
            setupLine(newLine);
            jq(element).append(newLine);
            calcWidth(newLine);
            jq(element).trigger('change');
            jq(document).trigger('multi_line_add_button', [$line, newLine]);
            jq(element).find("textarea, input[type='text']").last().focus();
            updatePrototypeVisibility();
            return false;
          });

          jq(line).find('.remove_button').on('click', () => {
            if (isStandaloneAddLine(line)) {
              return false;
            }
            $line.remove();
            updatePrototypeVisibility();
            jq(element).trigger('change');
            jq(document).trigger('multi_line_remove_button', $line);
            return false;
          });

          if (!isInit) {
            const fieldId = element.attr('id') || '';
            const cloneSelector = (
              `textarea[name^='${EMPTY_ID}'], input[name^='${EMPTY_ID}'], `
              + `select[name^='${EMPTY_ID}']`
            );
            $line.find(cloneSelector).each((_, inputEl) => {
              const input = jq(inputEl);
              const rawName = input.attr('name');
              input.val('');
              const regex = new RegExp(`^${EMPTY_ID}\\[[0-9]+\\](.*)$`);
              const matches = regex.exec(rawName);
              if (!matches) {
                return;
              }
              const suffix = matches[1];
              let idx = counter;
              let candidate = `${fieldId}[${idx}]${suffix}`;
              const isNameTaken = (n) => jq('input, select, textarea')
                .filter((__, el) => el.getAttribute('name') === n).length > 0;
              while (isNameTaken(candidate)) {
                idx += 1;
                candidate = `${fieldId}[${idx}]${suffix}`;
              }
              input.attr('name', candidate);
            });
          }
          counter += 1;
        };

        jq(this).find('.multi_input_line').each((_, el) => {
          setupLine(jq(el), true);
        });
        updatePrototypeVisibility();
        jq(element).trigger('change');

        return element;
      },
    });

    const positionMultiLineInit = (id) => {
      const root = document.getElementById(id);
      if (!root) {
        return;
      }
      jq(root).positionMultiLineInit();
      // PHP outputs the wrapper hidden (display:none) until init runs; avoids a flash
      // of the clone row before setupCloneLine hides it.
      root.removeAttribute('style');
    };

    return {
      positionMultiLineInit,
    };
  })();
}(jQuery));
