/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * editor ui
 */
export default class UI {

  /**
   * temp legacy code
   * @type {string}
   */
  droparea = "<div class='il_droparea'></div>";
  add = "<span class='glyphicon glyphicon-plus'></span>";

  /**
   * UI model
   * @type {Object}
   */
  model = {};

  /**
   * @type {Client}
   */
  client;

  /**
   * @type {Dispatcher}
   */
  dispatcher;

  /**
   * @type {ActionFactory}
   */
  actionFactory;

  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   */
  constructor(client, dispatcher, actionFactory) {
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
  }

  //
  // Initialisation
  //

  /**
   */
  init() {
    const ui_all_action = this.actionFactory.query().copage().uiAll();
    this.client.sendQuery(ui_all_action).then(result => {
      this.model = result.getPayload();
      this.initAddButtons();
      this.initDragDrop();
      this.initMultiSelection();
    });
  }

  /**
   */
  reInit() {
    this.initAddButtons();
    this.initDragDrop();
    this.initMultiSelection();
  }

  /**
   * Init add buttons
   */
  initAddButtons() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    // init add buttons
    document.querySelectorAll("[data-copg-ed-type='add-area']").forEach(area => {

      const model = this.model;
      let li, li_templ, ul;

      area.innerHTML = this.droparea + model.addDropdown;

      // droparea
      const drop = area.firstChild;
      drop.id = "TARGET" + area.dataset.hierid + ":" + (area.dataset.pcid || "");

      // add dropdown
      area.querySelectorAll("div.dropdown > button").forEach(b => {
        b.classList.add("copg-add");
        b.innerHTML = this.add;
        b.addEventListener("click", (event) => {
          ul = b.parentNode.querySelector("ul");
          li_templ = ul.querySelector("li").cloneNode(true);
          ul.innerHTML = "";
          for (const [ctype, txt] of Object.entries(model.addCommands)) {
            li = li_templ.cloneNode(true);
            li.querySelector("a").innerHTML = txt;
            li.querySelector("a").addEventListener("click", (event) => {
              dispatch.dispatch(action.editor().createAdd(ctype, area.dataset.pcid, area.dataset.hierid));
            });
            ul.appendChild(li);
          }
        });
      });
    });

  }

  /**
   * Init drag and drop handling
   */
  initDragDrop() {

    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    $(".il_editarea").draggable({
        cursor: 'move',
        revert: true,
        scroll: true,
        cursorAt: { top: 5, left:20 },
        snap: true,
        snapMode: 'outer',
        start: function( event, ui ) {
          dispatch.dispatch(action.editor().dndDrag());
        },
        stop: function( event, ui ) {
          dispatch.dispatch(action.editor().dndDrop());
        },
        helper: (() => {
          return $("<div style='width: 40px; border: 1px solid blue;'>&nbsp;</div>");
        })		/* temp helper */
      }
    );

    $(".il_droparea").droppable({
      drop: (event, ui) => {
        ui.draggable.draggable( 'option', 'revert', false );

        // @todo: remove legacy
        const target_id = event.target.id.substr(6);
        const source_id = ui.draggable[0].id.substr(7);
        if (source_id !== target_id) {
          ilCOPage.sendCmdRequest("moveAfter", source_id, target_id, {},
            true, {}, ilCOPage.pageReloadAjaxSuccess);
        }
      }
    });

    // this is needed to make scrolling while dragging with helper possible
    $("main.il-layout-page-content").css("position", "relative");

    this.hideDropareas();
  }

  /**
   * Init multi selection
   */
  initMultiSelection() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach(pc_area => {
      const pcid = pc_area.dataset.pcid;
      const hierid = pc_area.dataset.hierid;
      const ctype = pc_area.dataset.ctype;
      pc_area.addEventListener("dblclick", (event) => {
        dispatch.dispatch(action.editor().multiToggle(ctype, pcid, hierid));
      });
    });
  }

  initMultiButtons() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='multi']").forEach(multi_button => {
      const type = multi_button.dataset.action;
      multi_button.addEventListener("click", (event) => {
        dispatch.dispatch(action.editor().multiAction(type));
      });
    });
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

  showPageHelp() {
    document.querySelector("#copg-editor-slate-content").innerHTML = this.model.pageHelp;
  }

  showMultiButtons() {
    // @todo hate to use jquery here, but only jquery evals the included script tags
    //document.querySelector("#copg-editor-slate-content").innerHTML = this.model.multiActions;
    $("#copg-editor-slate-content").html(this.model.multiActions);
    this.initMultiButtons();
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

}
