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

/**
 * Editor action dispatcher
 */
export default class Dispatcher {

  constructor(modelActionHandler, uiActionHandler) {
    this.modelActionHandler = modelActionHandler;
    this.uiActionHandler = uiActionHandler;
  }

  /**
   * @param {EditorAction} action
   */
  dispatch(action) {
    console.log("dispatch " + action.getType());
    console.log(action.getParams());
    this.modelActionHandler.handle(action);
    this.uiActionHandler.handle(action, this.modelActionHandler.getModel());
  }
}