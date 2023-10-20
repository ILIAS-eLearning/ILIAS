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

import Util from "./util.js";

/**
 * Page modifier is an adapter for components to
 *
 */
export default class PageModifier {
  /**
   *
   * @type {PageUI}
   */
  // pageUI = null;

  /**
   * @param {ToolSlate} toolSlate
   */
  constructor(toolSlate) {
    this.pageUI = null;
    this.toolSlate = toolSlate;
    this.util = new Util();
  }

  setPageUI(pageUI) {
    this.pageUI = pageUI;
  }

  /**
   *
   * @param {string} after_pcid
   * @param {string} after_hierid
   * @param {string} pcid
   * @param {string} cname
   * @param {string} content
   * @param {string} label
   */
  insertComponentAfter(after_pcid, pcid, cname, content, label) {
    const addArea = document.querySelector(`[data-copg-ed-type='add-area'][data-pcid='${after_pcid}']`);
    const d = document.createElement('div');

    // insert after addArea
    addArea.parentNode.insertBefore(d, addArea.nextSibling);
    d.innerHTML = `<div data-copg-ed-type="pc-area" class="il_editarea" id="CONTENT:${pcid}"  data-pcid="${pcid}" data-cname="${cname}"><div class="ilEditLabel">${label}<!--Dummy--></div><div>${content}</div></div>`;
    const newAddArea = document.createElement('div');
    newAddArea.dataset.copgEdType = 'add-area';
    newAddArea.dataset.pcid = pcid;
    d.parentNode.insertBefore(newAddArea, d.nextSibling);

    const addSelector = `[data-copg-ed-type='add-area'][data-pcid='${pcid}']`;
    const pcSelector = `[data-pcid='${pcid}']`;

    this.pageUI.initComponentClick(pcSelector);
    this.pageUI.initAddButtons(addSelector);
    this.pageUI.initDragDrop(pcSelector, `${addSelector} .il_droparea`);
    this.pageUI.initMultiSelection(pcSelector);
    this.pageUI.initComponentEditing(pcSelector);

    this.pageUI.hideAddButtons();
    this.pageUI.hideDropareas();
  }

  removeInsertedComponent(pcid) {
    const pcSelector = `[data-copg-ed-type='pc-area'][data-pcid='${pcid}']`;
    const el = document.querySelector(pcSelector).parentNode;
    const next = el.nextSibling;
    el.parentNode.removeChild(el);
    next.parentNode.removeChild(next);
  }

  hideComponent(pcid) {
    const pcSelector = `[data-copg-ed-type='pc-area'][data-pcid='${pcid}']`;
    const el = document.querySelector(pcSelector).parentNode;
    const next = el.nextSibling;
    el.style.display = 'none';
    next.style.display = 'none';
  }

  showComponent(pcid) {
    const pcSelector = `[data-copg-ed-type='pc-area'][data-pcid='${pcid}']`;
    const el = document.querySelector(pcSelector).parentNode;
    const next = el.nextSibling;
    el.style.display = '';
    next.style.display = '';
  }

  cut(items) {
    for (const id of items) {
      console.log('cut');
      const pcid = id.split(':')[1];
      const pcSelector = `[data-copg-ed-type='pc-area'][data-pcid='${pcid}']`;
      const areaEl = document.querySelector(pcSelector);
      if (areaEl) { // this may already not exist anymore, if nested elements are cut
        const el = areaEl.parentNode;
        const next = el.nextSibling;
        el.parentNode.removeChild(el);
        next.parentNode.removeChild(next);
      }
    }
  }

  showModal(title, content, button_txt, onclick) {
    const { uiModel } = this.pageUI;

    $('#il-copg-ed-modal').remove();
    let modal_template = uiModel.modal.template;
    modal_template = modal_template.replace('#title#', title);
    modal_template = modal_template.replace('#content#', content);
    modal_template = modal_template.replace('#button_title#', button_txt);

    $('body').append(`<div id='il-copg-ed-modal'>${modal_template}</div>`);

    $(document).trigger(
      uiModel.modal.signal,
      {
        id: uiModel.modal.signal,
        triggerer: $(this),
        options: JSON.parse('[]'),
      },
    );

    if (button_txt) {
      const b = document.querySelector('#il-copg-ed-modal .modal-footer button');
      b.addEventListener('click', onclick);
    } else {
      document.querySelectorAll('#il-copg-ed-modal .modal-footer').forEach((b) => {
        b.remove();
      });
    }
  }

  hideCurrentModal() {
    $('#il-copg-ed-modal .modal').modal('hide');
  }

  getConfirmation(text) {
    const { uiModel } = this.pageUI;

    let confirmation_template = uiModel.confirmation;
    confirmation_template = confirmation_template.replace('#text#', text);
    return confirmation_template;
  }

  // default callback for successfull ajax request, reloads page content
  handlePageReloadResponse(result) {
    this.pageUI.handlePageReloadResponse(result);
  }

  redirectToPage(pcid) {
    this.redirect(`${this.pageUI.uiModel.backUrl}#pc${pcid}`);
  }

  redirect(url) {
    window.location.replace(url);
  }

  displayError(error) {
    console.log("*** DISPLAY ERROR");
    console.log(error);
    const uiModel = this.pageUI.uiModel;
    this.toolSlate.displayError(uiModel.errorMessage);
    const pm = this;
    const util = this.util;
    const content =  uiModel.errorModalMessage + error;

    const link = document.querySelector('#copg-editor-slate-error ul li a');
    if (link) {
      link.addEventListener("click", () => {
        this.util.showModal(uiModel.modal, il.Language.txt("copg_error"), content);
        let m = document.querySelector("#il-copg-ed-modal .modal-dialog");
        if (m) {
          m.style.width = '90%';
        }
      });
      link.click();
    } else {
      const slate_error = document.querySelector('#copg-editor-slate-error');
      slate_error.innerHTML = content;
    }
  }

  clearError() {
    this.toolSlate.clearError();
  }

  initFormButtonsAndSettingsLink(model) {
    let c;

    // this removes all event listeners that have been already attached
    document.querySelectorAll("#copg-editor-slate-content [data-copg-ed-type='form-button']").forEach(form_button => {
      c = form_button.cloneNode(true);
      form_button.parentNode.replaceChild(c, form_button);
    });

    document.querySelectorAll("#copg-editor-slate-content [data-copg-ed-type='form-button']").forEach(form_button => {
      const dispatch = this.pageUI.dispatcher;
      const action = this.pageUI.actionFactory;
      const act = form_button.dataset.copgEdAction;
      const cname = form_button.dataset.copgEdComponent;
      if (cname === "Page") {
        form_button.addEventListener("click", (event) => {
          event.preventDefault();
          // prevents event listeners being called, that are attached later
          // especially standard file upload processing (which submits forms etc.)
          event.stopPropagation();
          switch (act) {
            case "component.cancel":
              dispatch.dispatch(action.page().editor().componentCancel());
              break;

            case "component.back":
              dispatch.dispatch(action.page().editor().componentBack());
              break;

            case "component.save":
              const form = form_button.closest("form");

              //after_pcid, pcid, component, data
              dispatch.dispatch(action.page().editor().componentSave(
                model.getCurrentInsertPCId(),
                model.getCurrentPCId(),
                model.getCurrentPCName(),
                {
                  form:form
                }
              ));
              break;

            case "component.update":
              const uform = form_button.closest("form");
              const uform_data = new FormData(uform);

              //after_pcid, pcid, component, data
              dispatch.dispatch(action.page().editor().componentUpdate(
                model.getCurrentPCId(),
                model.getCurrentPCName(),
                uform_data
              ));
              break;

            case "component.update.back":
              const uform2 = form_button.closest("form");
              const uform_data2 = new FormData(uform2);

              //after_pcid, pcid, component, data
              dispatch.dispatch(action.page().editor().componentUpdateBack(
                model.getCurrentPCId(),
                model.getCurrentPCName(),
                uform_data2
              ));
              break;
          }
        });
      }
    });

    document.querySelectorAll("#copg-editor-slate-content [data-copg-ed-type='link']").forEach(link => {
      const dispatch = this.pageUI.dispatcher;
      const action = this.pageUI.actionFactory;
      const act = link.dataset.copgEdAction;
      const cname = link.dataset.copgEdComponent;
      if (cname === "Page") {
        link.addEventListener("click", (event) => {
          event.preventDefault();
          switch (act) {
            case "component.settings":
              //after_pcid, pcid, component, data
              dispatch.dispatch(action.page().editor().componentSettings(
                model.getCurrentPCName(),
                model.getCurrentPCId(),
                model.getCurrenntHierId()
              ));
              break;

          }
        });
      }
    });
  }

}
