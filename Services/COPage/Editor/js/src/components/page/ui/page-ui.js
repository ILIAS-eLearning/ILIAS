/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "../actions/page-action-types.js";

/**
 * page ui
 */
export default class PageUI {

  /**
   * @type {boolean}
   */
//  debug = true;

  /**
   * temp legacy code
   * @type {string}
   */
    //  droparea = "<div class='il_droparea'></div>";
  //add = "<span class='glyphicon glyphicon-plus'></span>";

  /**
   * Model
   * @type {PageModel}
   */
  //model = {};

  /**
   * UI model
   * @type {Object}
   */
  //uiModel = {};

  /**
   * @type {Client}
   */
  //client;

  /**
   * @type {Dispatcher}
   */
  //dispatcher;

  /**
   * @type {ActionFactory}
   */
  //actionFactory;

  /**
   * @type {Map<any, any>}
   */
  //clickMap = new Map();

  /**
   * @type {ToolSlate}
   */
  //toolSlate;

  /**
   * @type {PageModifier}
   */
  //pageModifier;

  /**
   * @type {Map<any, any>}
   */
  //componentUI = new Map();

  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {PageModel} model
   * @param {ToolSlate} toolSlate
   * @param {PageModifier} pageModifier
   */
  constructor(client, dispatcher, actionFactory, model, toolSlate
    , pageModifier) {

    this.debug = true;
    this.droparea = "<div class='il_droparea'></div>";
    this.add = "<span class='glyphicon glyphicon-plus-sign'></span>";
    this.first_add = "<span class='il-copg-add-text'> " +
      il.Language.txt("cont_ed_click_to_add_pg") +
      "</span>";
    this.model = {};
    this.uiModel = {};

    this.clickMap = new Map();
    this.componentUI = new Map();
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.model = model;
    this.toolSlate = toolSlate;
    this.pageModifier = pageModifier;
  }

  //
  // Initialisation
  //

  /**
   * @param message
   */
  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   */
  init(uiModel) {
    this.uiModel = uiModel;
    this.initComponentClick();
    this.initAddButtons();
    this.initDragDrop();
    this.initMultiSelection();
    this.initComponentEditing();
    this.showEditPage();
    this.markCurrent();
  }

  /**
   */
  reInit() {
    this.initComponentClick();
    this.initAddButtons();
    this.initDragDrop();
    this.initMultiSelection();
    this.initComponentEditing();
    this.markCurrent();
  }

  addComponentUI(cname, ui) {
    this.componentUI.set(cname, ui);
  }

  /**
   * Init add buttons
   */
  initAddButtons(selector) {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    if (!selector) {
      selector = "[data-copg-ed-type='add-area']"
    }

    // init add buttons
    document.querySelectorAll(selector).forEach(area => {

      const uiModel = this.uiModel;
      let li, li_templ, ul;
      area.innerHTML = this.droparea + uiModel.dropdown;

      const model = this.model;

      // droparea
      const drop = area.firstChild;
      const hier_id = (area.dataset.hierid)
        ? area.dataset.hierid
        : "";
      drop.id = "TARGET" + hier_id + ":" + (area.dataset.pcid || "");

      // add dropdown
      const addButtons = area.querySelectorAll("div.dropdown > button");
      addButtons.forEach(b => {
        b.classList.add("copg-add");
        b.addEventListener("click", (event) => {

          // we need that to "filter" out these events on the single clicks
          // on editareas
          event.isDropDownToggleEvent = true;

          ul = b.parentNode.querySelector("ul");
          li_templ = ul.querySelector("li").cloneNode(true);
          ul.innerHTML = "";

          this.log("add dropdown: click");
          this.log(model);

          const pasting = model.isPasting();

          if (pasting) {
            li = li_templ.cloneNode(true);
            li.querySelector("a").innerHTML = il.Language.txt("paste");
            li.querySelector("a").addEventListener("click", (event) => {
              event.isDropDownSelectionEvent = true;
              dispatch.dispatch(action.page().editor().multiPaste(
                area.dataset.pcid,
                hier_id,
                model.getMultiState()));
            });
            ul.appendChild(li);
          }

          // add each components
          for (const [ctype, txt] of Object.entries(uiModel.addCommands)) {
            let cname, pluginName;
            li = li_templ.cloneNode(true);
            li.querySelector("a").innerHTML = txt;
            if (ctype.substr(0, 5) === "plug_") {
              cname = "Plugged";
              pluginName = ctype.substr(5);
            } else {
              cname = this.getPCNameForType(ctype);
              pluginName = "";
            }
            li.querySelector("a").addEventListener("click", (event) => {
              event.isDropDownSelectionEvent = true;
              dispatch.dispatch(action.page().editor().componentInsert(cname,
                  area.dataset.pcid,
                  hier_id,
                  pluginName,
                  false));
            });
            ul.appendChild(li);
          }
        });
      });
    });
    this.refreshAddButtonText();
  }

  getPCTypeForName(name) {
    return this.uiModel.pcDefinition.types[name];
  }

  getPCNameForType(type) {
    return this.uiModel.pcDefinition.names[type];
  }

  getLabelForType(type) {
    return this.uiModel.pcDefinition.txt[type];
  }

  getCnameForPCID(pcid) {
    const el = document.querySelector("[data-pcid='" + pcid + "']");
    if (el) {
      return el.dataset.cname;
    }
    return null;
  }

  /**
   * Click and DBlClick is not naturally supported on browsers (click is also fired on
   * dblclick, time period for dblclick varies)
   */
  initComponentClick(selector) {
    let areaSelector, coverSelector, area;

    if (!selector) {
      areaSelector = "[data-copg-ed-type='pc-area']";
      coverSelector = "[data-copg-ed-type='media-cover']";
    } else {
      areaSelector = selector + "[data-copg-ed-type='pc-area']";
      coverSelector = selector + "[data-copg-ed-type='media-cover']";
    }

    // init add buttons
    document.querySelectorAll(areaSelector).forEach(area => {
      area.addEventListener("click", (event) => {
        if (event.isDropDownToggleEvent === true ||
          event.isDropDownSelectionEvent === true) {
          return;
        }
        event.stopPropagation();

        if (event.shiftKey || event.ctrlKey || event.metaKey) {
          area.dispatchEvent(new Event("areaCmdClick"));
        } else {
          area.dispatchEvent(new Event("areaClick"));
        }
      });
    });

    // init add buttons
    document.querySelectorAll(coverSelector).forEach(cover => {
      cover.addEventListener("click", (event) => {
        console.log("---COVER CLICKED---");
        if (event.isDropDownToggleEvent === true ||
            event.isDropDownSelectionEvent === true) {
          return;
        }
        event.stopPropagation();
        area = cover.closest("[data-copg-ed-type='pc-area']");
        if (event.shiftKey || event.ctrlKey || event.metaKey) {
          area.dispatchEvent(new Event("areaCmdClick"));
        } else {
          area.dispatchEvent(new Event("areaClick"));
        }
      });
    });

  }

  initComponentEditing(selector) {

    if (!selector) {
      selector = "[data-copg-ed-type='pc-area']";
    }

    // init add buttons
    document.querySelectorAll(selector).forEach(area => {
      const dispatch = this.dispatcher;
      const action = this.actionFactory;

      area.addEventListener("areaClick", (event) => {
        this.log("*** Component click event");
        // start editing from page state
        if (this.model.getState() === this.model.STATE_PAGE) {
          if (area.dataset.cname !== "ContentInclude") {
            dispatch.dispatch(action.page().editor().componentEdit(area.dataset.cname,
                area.dataset.pcid,
                area.dataset.hierid));
          }
        } else if (this.model.getState() === this.model.STATE_COMPONENT) {

          // Invoke switch action, if click is on other component of same type
          // (and currently type must be Paragraph)
          if (this.model.getCurrentPCName() === area.dataset.cname &&
              this.model.getCurrentPCId() !== area.dataset.pcid &&
              this.model.getCurrentPCName() === "Paragraph") {

            const pcModel = this.model.getPCModel(area.dataset.pcid);
            if (pcModel.characteristic !== "Code") {

              let compPara = {};
              if (this.componentUI.has(area.dataset.cname)) {
                const componentUI = this.componentUI.get(area.dataset.cname);
                if (typeof componentUI.getSwitchParameters === 'function') {
                  compPara = componentUI.getSwitchParameters();
                }
              }

              dispatch.dispatch(action.page().editor().componentSwitch(
                  area.dataset.cname,
                  this.model.getComponentState(),
                  this.model.getCurrentPCId(),
                  compPara,
                  area.dataset.pcid,
                  area.dataset.hierid));
            }
          }
        }
      });
    });
  }

  /**
   * Init drag and drop handling
   */
  initDragDrop(draggableSelector, droppableSelector) {

    this.log("page-ui.initDragDrop");
    this.log("- draggableSelector: " + draggableSelector);
    this.log("- droppableSelector: " + droppableSelector);

    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    if (!draggableSelector) {
      draggableSelector = ".il_editarea, .il_editarea_disabled";
    }

    if (!droppableSelector) {
      droppableSelector = ".il_droparea";
    }

    $(draggableSelector).draggable({
        cursor: 'move',
        revert: false,
        scroll: true,
        distance: 3,
        cursorAt: { top: 5, left:20 },
        snap: true,
        snapMode: 'outer',
        start: function( event, ui ) {
          dispatch.dispatch(action.page().editor().dndDrag());
        },
        stop: function( event, ui ) {
          dispatch.dispatch(action.page().editor().dndStopped());
        },
        helper: (() => {
          return $("<div class='il-copg-drag'>&nbsp;</div>");
        })		/* temp helper */
      }
    );

    $(droppableSelector).droppable({
      drop: (event, ui) => {
        ui.draggable.draggable( 'option', 'revert', false );



        // @todo: remove legacy
        const target_id = event.target.id.substr(6);
        const source_id = ui.draggable[0].id.substr(7);

        dispatch.dispatch(action.page().editor().dndDrop(target_id, source_id));
      }
    });

    // this is needed to make scrolling while dragging with helper possible
    $("main.il-layout-page-content").css("position", "relative");

    this.hideDropareas();
  }

  /**
   * Init multi selection
   */
  initMultiSelection(selector) {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    if (!selector) {
      selector = "[data-copg-ed-type='pc-area']";
    }
    this.log("init multi section");
    document.querySelectorAll(selector).forEach(pc_area => {
      const pcid = pc_area.dataset.pcid;
      const hierid = pc_area.dataset.hierid;
      const ctype = pc_area.dataset.ctype;
      pc_area.addEventListener("areaClick", (event) => {
        if (this.model.getState() !== this.model.STATE_MULTI_ACTION) {
          return;
        }
        dispatch.dispatch(action.page().editor().multiToggle(ctype, pcid, hierid));
      });
      pc_area.addEventListener("areaCmdClick", (event) => {
        if (!([this.model.STATE_PAGE, this.model.STATE_MULTI_ACTION].includes(this.model.getState()))) {
          return;
        }
        dispatch.dispatch(action.page().editor().multiToggle(ctype, pcid, hierid));
      });
    });
  }

  initMultiButtons() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    const selected = this.model.getSelected();
    let buttonDisabled;

    document.querySelectorAll("[data-copg-ed-type='multi']").forEach(multi_button => {
      const type = multi_button.dataset.copgEdAction;
      multi_button.addEventListener("click", (event) => {
        if (type === "activate") {
          const pcids = new Set(this.model.getSelected());
          dispatch.dispatch(action.page().editor().multiActivate(pcids));
        } else {
          dispatch.dispatch(action.page().editor().multiAction(type));
        }
      });

      buttonDisabled = (selected.size === 0 && type !== "all");
      if (type === "all") {
        const all_areas = document.querySelectorAll("[data-copg-ed-type='pc-area']");
        buttonDisabled = (selected.size === all_areas.length);
      }
      multi_button.disabled = buttonDisabled

    });
  }

  initTopActions() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='view-control']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
          switch (act) {
            case ACTIONS.SWITCH_SINGLE:
              dispatch.dispatch(action.page().editor().switchSingle());
              break;
            case ACTIONS.SWITCH_MULTI:
              dispatch.dispatch(action.page().editor().switchMulti());
              break;
          }
      });
    });
    this.refreshModeSelector();
  }

  refreshModeSelector() {
    const model = this.model;
    const multi = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='switch.multi']");
    const single = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='switch.single']");
    multi.classList.remove("engaged");
    single.classList.remove("engaged");
    if (model.getState() === model.STATE_PAGE) {
      //multi.disabled = false;
      //single.disabled = true;
      single.classList.add("engaged");
    } else {
      //multi.disabled = true;
      //single.disabled = false;
      multi.classList.add("engaged");
    }
  }

  initFormatButtons() {

    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    const model = this.model;
    const selected = model.getSelected();
    let pcModel, cname;


    this.toolSlate.setContent(this.uiModel.formatSelection);

    document.querySelector("#il-copg-format-paragraph").
        style.display = "none";
    document.querySelector("#il-copg-format-section").
        style.display = "none";
    document.querySelector("#il-copg-format-media").
        style.display = "none";
    console.log("***INIT FORMAT");
    console.log(selected);
    selected.forEach((id) => {
      cname = this.getCnameForPCID(id.split(":")[1]);
      switch (cname) {
        case "MediaObject":
          document.querySelector("#il-copg-format-media").
              style.display = "";
          break;
        case "Section":
          document.querySelector("#il-copg-format-section").
              style.display = "";
          break;
        case "Paragraph":
          document.querySelector("#il-copg-format-paragraph").
              style.display = "";
          break;
      }
    });

    document.querySelectorAll("[data-copg-ed-type='format']").forEach(multi_button => {
      const act = multi_button.dataset.copgEdAction;
      const format = multi_button.dataset.copgEdParFormat;

      switch (act) {

        case "format.paragraph":
          multi_button.addEventListener("click", (event) => {
            dispatch.dispatch(action.page().editor().formatParagraph(format));
          });
          break;

        case "format.section":
          multi_button.addEventListener("click", (event) => {
            dispatch.dispatch(action.page().editor().formatSection(format));
          });
          break;

        case "format.media":
          multi_button.addEventListener("click", (event) => {
            dispatch.dispatch(action.page().editor().formatMedia(format));
          });
          break;

        case "format.save":
          multi_button.addEventListener("click", (event) => {
            const pcids = new Set(this.model.getSelected());
            dispatch.dispatch(action.page().editor().formatSave(
              pcids,
              model.getParagraphFormat(),
              model.getSectionFormat(),
              model.getMediaFormat()
            ));
          });
          break;

        case "format.cancel":
          multi_button.addEventListener("click", (event) => {
            dispatch.dispatch(action.page().editor().formatCancel());
          });
          break;
      }
    });

    // get first values and dispatch their selection
    const b1 = document.querySelector("#il-copg-format-paragraph div.dropdown ul li button");
    const f1 = b1.dataset.copgEdParFormat;
    if (f1) {
      dispatch.dispatch(action.page().editor().formatParagraph(f1));
    }
    const b2 = document.querySelector("#il-copg-format-section div.dropdown ul li button");
    const f2 = b2.dataset.copgEdParFormat;
    if (f2) {
      dispatch.dispatch(action.page().editor().formatSection(f2));
    }
    const b3 = document.querySelector("#il-copg-format-media div.dropdown ul li button");
    const f3 = b3.dataset.copgEdParFormat;
    if (f3) {
      dispatch.dispatch(action.page().editor().formatMedia(f3));
    }
  }

  setParagraphFormat(format) {
    const b1 = document.querySelector("#il-copg-format-paragraph div.dropdown > button");
    if (b1) {
      b1.firstChild.textContent = format + " ";
    }
  }

  setSectionFormat(format) {
    const b2 = document.querySelector("#il-copg-format-section div.dropdown > button");
    if (b2) {
      b2.firstChild.textContent = format + " ";
    }
  }

  setMediaFormat(format) {
    const b3 = document.querySelector("#il-copg-format-media div.dropdown > button");
    if (b3) {
      b3.firstChild.textContent = format + " ";
    }
  }


  //
  // Show/Hide single elements
  //

  enableDragDrop() {
    $('.il_editarea').draggable("enable");
  }

  disableDragDrop() {
    $('.il_editarea').draggable("disable");
  }

  showAddButtons() {
    document.querySelectorAll("button.copg-add").forEach(el => {
      el.style.display = "";
    });
  }

  refreshAddButtonText() {
    const addButtons = document.querySelectorAll("button.copg-add");
    document.querySelectorAll("button.copg-add").forEach(b => {
      if (addButtons.length === 1) {
        b.innerHTML = this.add + this.first_add;
      } else {
        b.innerHTML = this.add;
      }
    });
  }

  hideAddButtons() {
    document.querySelectorAll("button.copg-add").forEach(el => {
      el.style.display = "none";
    });
  }

  showDropareas() {
    document.querySelectorAll("#il_EditPage .il_droparea").forEach(el => {
      el.style.display = "";
    });
  }

  hideDropareas() {
    document.querySelectorAll("#il_EditPage .il_droparea").forEach(el => {
      el.style.display = "none";
    });
  }

  showEditPage() {
    const model = this.model;
    const pasteHelp = ([model.STATE_MULTI_CUT, model.STATE_MULTI_COPY].includes(model.getMultiState()))
      ? this.uiModel.pasteMessage
      : "";
    this.toolSlate.setContent(this.uiModel.pageTopActions + pasteHelp + this.uiModel.pageEditHelp);
    this.initTopActions();
  }

  showMultiButtons() {
    const model = this.model;

    switch (model.getMultiState()) {

      case model.STATE_MULTI_CHARACTERISTIC:
        break;

      default:
        this.toolSlate.setContent(this.uiModel.pageTopActions + this.uiModel.multiActions + this.uiModel.multiEditHelp);
        this.initTopActions();
        this.initMultiButtons();
        break;
    }


  }

  /**
   * @param {Set<string>} items
   */
  highlightSelected(items) {
    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach(el => {
      const key = el.dataset.hierid + ":" + (el.dataset.pcid || "");
      if (items.has(key)) {
        el.classList.add("il_editarea_selected");
      } else {
        el.classList.remove("il_editarea_selected");
      }
    });
  }

  markCurrent() {
    const editContainer = document.getElementById("il_EditPage");
    if (editContainer) {
      editContainer.setAttribute("class", "copg-state-" + this.model.getState());
    }

    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach(el => {
      const pcid = el.dataset.pcid;
      if (this.model.getCurrentPCId() === pcid && this.model.getState() === this.model.STATE_COMPONENT) {
        el.classList.add("copg-current-edit");
      } else {
        el.classList.remove("copg-current-edit");
      }
    });
  }

  // default callback for successfull ajax request, reloads page content
  handlePageReloadResponse(result)
  {
    const pl = result.getPayload();

    if(pl.renderedContent !== undefined)
    {
      $('#il_center_col').html(pl.renderedContent);

      console.log("PCMODEL---");
      console.log(pl.pcModel);

      for (const [key, value] of Object.entries(pl.pcModel)) {
        this.model.addPCModelIfNotExists(key, value);
      }

//      il.IntLink.refresh();           // missing
      this.reInit();
    }
  }

  showDeleteConfirmation() {
    let content = this.pageModifier.getConfirmation(il.Language.txt("copg_confirm_el_deletion"))
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    this.pageModifier.showModal(
      il.Language.txt("cont_delete_content"),
      content,
      il.Language.txt("delete"),
      () => {
        const pcids = new Set(this.model.getSelected());
        dispatch.dispatch(action.page().editor().multiDelete(pcids));
      });
  }

  hideDeleteConfirmation() {
    this.pageModifier.hideCurrentModal();
  }

  //
  // Generic creation
  //

  showGenericCreationForm() {
    const model = this.model;

    let content = this.model.getCurrentPCName();

    if (this.uiModel.components[this.model.getCurrentPCName()] &&
      this.uiModel.components[this.model.getCurrentPCName()].icon) {
      content = "<div class='copg-new-content-placeholder'>" + this.uiModel.components[this.model.getCurrentPCName()].icon +
        "<div>" +  this.getLabelForType(this.getPCTypeForName(this.model.getCurrentPCName())) + "</div></div>";
    }

    this.pageModifier.insertComponentAfter(
      model.getCurrentInsertPCId(),
      model.getCurrentPCId(),
      this.model.getCurrentPCName(),
      content,
      this.model.getCurrentPCName()
    );
    this.toolSlate.setContentFromComponent(this.model.getCurrentPCName(), "creation_form");
    this.initFormButtonsAndSettingsLink();
  }

  initFormButtonsAndSettingsLink() {
    const model = this.model;

    document.querySelectorAll("#copg-editor-slate-content [data-copg-ed-type='form-button']").forEach(form_button => {
      const dispatch = this.dispatcher;
      const action = this.actionFactory;
      const act = form_button.dataset.copgEdAction;
      const cname = form_button.dataset.copgEdComponent;
      if (cname === "Page") {
        form_button.addEventListener("click", (event) => {
          event.preventDefault();
          switch (act) {
            case "component.cancel":
              dispatch.dispatch(action.page().editor().componentCancel());
              break;

            case "component.save":
              const form = form_button.closest("form");
              const form_data = new FormData(form);

              //after_pcid, pcid, component, data
              dispatch.dispatch(action.page().editor().componentSave(
                model.getCurrentInsertPCId(),
                model.getCurrentPCId(),
                model.getCurrentPCName(),
                form_data
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
          }
        });
      }
    });

    document.querySelectorAll("#copg-editor-slate-content [data-copg-ed-type='link']").forEach(link => {
      const dispatch = this.dispatcher;
      const action = this.actionFactory;
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

  removeInsertedComponent(pcid) {
    this.pageModifier.removeInsertedComponent(pcid);
  }

  ////
  //// Generic editing
  ////

  loadGenericEditingForm(cname, pcid, hierid) {
    const loadEditingFormAction = this.actionFactory.page().query().loadEditingForm(cname, pcid, hierid);
    this.client.sendQuery(loadEditingFormAction).then(result => {
      const p = result.getPayload();

      this.toolSlate.setContent(p.editForm);
      this.initFormButtonsAndSettingsLink();
    });

  }

}
