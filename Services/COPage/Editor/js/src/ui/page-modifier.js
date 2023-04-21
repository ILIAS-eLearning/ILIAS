/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Page modifier is an adapter for components to
 *
 */
export default class PageModifier {

  /**
   *
   * @type {PageUI}
   */
  //pageUI = null;

  /**
   * @param {ToolSlate} toolSlate
   */
  constructor(toolSlate) {
    this.pageUI = null;
    this.toolSlate = toolSlate;
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
    const addArea = document.querySelector("[data-copg-ed-type='add-area'][data-pcid='" + after_pcid + "']");
    let d = document.createElement("div");

    // insert after addArea
    addArea.parentNode.insertBefore(d, addArea.nextSibling);
    d.innerHTML =
      '<div data-copg-ed-type="pc-area" class="il_editarea" id="CONTENT:' + pcid + '"  data-pcid="' + pcid + '" data-cname="' + cname + '"><div class="ilEditLabel">' + label + '<!--Dummy--></div><div>' + content + '</div></div>';
    let newAddArea = document.createElement("div");
    newAddArea.dataset.copgEdType = "add-area";
    newAddArea.dataset.pcid = pcid;
    d.parentNode.insertBefore(newAddArea, d.nextSibling);

    let addSelector = "[data-copg-ed-type='add-area'][data-pcid='" + pcid + "']";
    let pcSelector = "[data-pcid='" + pcid + "']";

    this.pageUI.initComponentClick(pcSelector);
    this.pageUI.initAddButtons(addSelector);
    this.pageUI.initDragDrop(pcSelector, addSelector + " .il_droparea");
    this.pageUI.initMultiSelection(pcSelector);
    this.pageUI.initComponentEditing(pcSelector);

    this.pageUI.hideAddButtons();
    this.pageUI.hideDropareas();
  }

  removeInsertedComponent(pcid) {
    const pcSelector = "[data-copg-ed-type='pc-area'][data-pcid='" + pcid + "']";
    const el = document.querySelector(pcSelector).parentNode;
    const next = el.nextSibling;
    el.parentNode.removeChild(el);
    next.parentNode.removeChild(next);
  }

  hideComponent(pcid) {
    const pcSelector = "[data-copg-ed-type='pc-area'][data-pcid='" + pcid + "']";
    const el = document.querySelector(pcSelector).parentNode;
    const next = el.nextSibling;
    el.style.display = 'none';
    next.style.display = 'none';
  }

  showComponent(pcid) {
    const pcSelector = "[data-copg-ed-type='pc-area'][data-pcid='" + pcid + "']";
    const el = document.querySelector(pcSelector).parentNode;
    const next = el.nextSibling;
    el.style.display = '';
    next.style.display = '';
  }

  cut(items) {
    for (let id of items) {
      console.log("cut");
      const pcid = id.split(":")[1];
      const pcSelector = "[data-copg-ed-type='pc-area'][data-pcid='" + pcid + "']";
      const areaEl = document.querySelector(pcSelector);
      if (areaEl) {   // this may already not exist anymore, if nested elements are cut
        const el = areaEl.parentNode;
        const next = el.nextSibling;
        el.parentNode.removeChild(el);
        next.parentNode.removeChild(next);
      }
    }
  }

  showToast(text) {
    const OSDNotifier = OSDNotifications({
      initialNotifications: [{
        notification_osd_id: 123,
        valid_until: 0,
        visible_for: 3,
        data: {
          title: "",
          link: false,
          iconPath: false,
          shortDescription: text,
          handlerParams: {
            osd: {
              closable: false
            }
          }
        }
      }]
    });
  }

  showModal(title, content, button_txt, onclick) {
    const uiModel = this.pageUI.uiModel;

    $("#il-copg-ed-modal").remove();
    let modal_template = uiModel.modal.template;
    modal_template = modal_template.replace("#title#", title);
    modal_template = modal_template.replace("#content#", content);
    modal_template = modal_template.replace("#button_title#", button_txt);

    $("body").append("<div id='il-copg-ed-modal'>" + modal_template + "</div>");

    $(document).trigger(
      uiModel.modal.signal,
      {
        'id': uiModel.modal.signal,
        'triggerer': $(this),
        'options': JSON.parse('[]')
      }
    );

    if (button_txt) {
      const b = document.querySelector("#il-copg-ed-modal .modal-footer button");
      b.addEventListener("click", onclick);
    } else {
      document.querySelectorAll("#il-copg-ed-modal .modal-footer").forEach((b) => {
        b.remove();
      });
    }
  }

  hideCurrentModal() {
    $("#il-copg-ed-modal .modal").modal("hide");
  }

  getConfirmation(text) {
    const uiModel = this.pageUI.uiModel;

    let confirmation_template = uiModel.confirmation;
    confirmation_template = confirmation_template.replace("#text#", text);
    return confirmation_template;
  }

  // default callback for successfull ajax request, reloads page content
  handlePageReloadResponse(result) {
    this.pageUI.handlePageReloadResponse(result);
  }

  redirectToPage(pcid) {
    this.redirect(this.pageUI.uiModel.backUrl + "#pc" + pcid);
  }

  redirect(url) {
    window.location.replace(url);
  }

  displayError(error) {
    const uiModel = this.pageUI.uiModel;
    this.toolSlate.displayError(uiModel.errorMessage);
    const pm = this;

    const content =  uiModel.errorModalMessage + error;

    const link = document.querySelector("#copg-editor-slate-error ul li a");
    link.addEventListener("click", () => {
      pm.showModal(il.Language.txt("copg_error"), content);
      let m = document.querySelector("#il-copg-ed-modal .modal-dialog");
      if (m) {
        m.style.width = "90%";
      }
    });
    link.click();
 }

  clearError() {
    this.toolSlate.clearError();
  }
}
