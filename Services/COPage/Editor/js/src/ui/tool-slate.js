/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    // @todo hate to use jquery here, but only jquery evals the included script tags
    //document.querySelector("#copg-editor-slate-content").innerHTML = html;
    $("#copg-editor-slate-content").html(html);
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
