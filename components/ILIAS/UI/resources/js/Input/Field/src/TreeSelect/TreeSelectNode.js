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

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class TreeSelectNode {
  /**
   * @param {string} id
   * @param {string} name
   * @param {HTMLLIElement} element
   * @param {HTMLButtonElement} selectButton
   * @param {number} drilldownParentLevel
   * @param {HTMLButtonElement|null} drilldownButton (only for branches)
   * @param {HTMLUListElement|null} listElement (only for branches)
   * @param {URI|string|null} renderUrl (only for async nodes)
   */
  constructor(
    id,
    name,
    element,
    selectButton,
    drilldownParentLevel,
    drilldownButton = null,
    listElement = null,
    renderUrl = null,
  ) {
    this.id = id;
    this.name = name;
    this.element = element;
    this.selectButton = selectButton;
    this.drilldownParentLevel = drilldownParentLevel;
    this.drilldownButton = drilldownButton;
    this.listElement = listElement;
    this.renderUrl = renderUrl;
  }
}
