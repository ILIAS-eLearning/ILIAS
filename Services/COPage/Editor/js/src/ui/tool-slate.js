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

import Util from './util.js';

/**
 * Editor tool slate
 */
export default class ToolSlate {

  /**
   * @type {string}
   */
  //content_id = "copg-editor-slate-content";

  /**
   */
  constructor() {
    this.content_id = "copg-editor-slate-content";
    this.error_id = "copg-editor-slate-error";
    this.util = new Util();
  }

  /**
   * @param uiModel
   */
  init(uiModel) {
    this.uiModel = uiModel;
  }

  /**
   * @param {string} html
   */
  setContent(html) {
    const slateEl = document.getElementById('copg-editor-slate-content');
    this.util.setInnerHTML(slateEl, html);
    $('body').trigger('il-copg-editor-slate');
    // this fixes #30378
    il.Form.registerFileUploadInputEventTrigger('#copg-editor-slate-content ');
  }

  /**
   * @param {string} component
   * @param {string} key
   */
  setContentFromComponent(component, key) {
    this.setContent(this.uiModel.components[component][key]);
  }

  displayError(error) {
    $("#copg-editor-slate-error").html(error);
  }

  clearError() {
    $("#copg-editor-slate-error").html('');
  }
}
