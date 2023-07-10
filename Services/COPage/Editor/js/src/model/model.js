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
 *********************************************************************/

import PageModel from "../components/page/model/page-model.js";
import TableModel from "../components/table/model/table-model.js";

/**
 * Controller (handles editor initialisation process)
 */
export default class Model {
  /**
   * @type {Object}
   */
  //models = new Map();

  constructor() {
    this.models = new Map();
    this.models.set("page", new PageModel());
    this.models.set("table", new TableModel());
  }

  model(key) {
    return this.models.get(key);
  }
}