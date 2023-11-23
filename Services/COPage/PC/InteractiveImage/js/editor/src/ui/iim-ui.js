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

import ACTIONS from '../actions/iim-action-types.js';
import Util from '../../../../../../Editor/js/src/ui/util.js';
import ShapeEditor from '../shape-edit/shape-editor.js';
import ActionFactory from '../actions/iim-editor-action-factory.js';
import TriggerFactory from '../trigger/trigger-factory.js';
import Poly from '../shape-edit/poly.js';
import IIMUIModifier from './iim-ui-modifier.js';
import IimCommonUtil from '../../../common/src/util.js';

/**
 * interactive image ui
 */
export default class UI {
  /**
   * @type {boolean}
   */
  // debug = true;

  /**
   * Model
   * @type {PageModel}
   */
  // page_model = {};

  /**
   * UI model
   * @type {Object}
   */
  // uiModel = {};

  /**
   * @type {Client}
   */
  // client;

  /**
   * @type {Dispatcher}
   */
  // dispatcher;

  /**
   * @type {ActionFactory}
   */
  // actionFactory;

  /**
   * @type {ToolSlate}
   */
  // toolSlate;

  /**
   * @type {pageModifier}
   */
  //  pageModifier;

  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {IIMModel} page_model
   * @param {ToolSlate} toolSlate
   * @param {IIMUIModifier} uiModifier
   */
  constructor(client, dispatcher, actionFactory, iimModel, uiModel, toolSlate, uiModifier) {
    this.debug = true;
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.iimModel = iimModel;
    this.toolSlate = toolSlate;
    this.uiModel = uiModel;
    this.util = new Util();
    this.shapeEditor = null;
    this.triggerFactory = new TriggerFactory();
    this.uiModifier = uiModifier;
    this.iimCommonUtil = new IimCommonUtil();
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
    const action = this.actionFactory;
    const dispatch = this.dispatcher;

    this.uiModel = uiModel;
    const t = this;
    this.showMainScreen();
  }

  /**
   */
  reInit() {
  }

  showMainScreen() {
    let content = this.uiModel.mainHead;
    if (this.iimModel.model.iim.triggers.length > 0) {
      content += this.uiModel.selectTriggerMessage;
    } else {
      content += this.uiModel.addTriggerMessage;
    }
    content += this.uiModel.mainSlate;
    this.toolSlate.setContent(content);
    this.initMainScreenActions();
    this.refreshMainScreen();
  }

  initMainScreenActions() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_ADD_TRIGGER:
            dispatch.dispatch(action.interactiveImage().editor().addTrigger());
            break;
          case ACTIONS.E_COMPONENT_BACK:
            dispatch.dispatch(action.interactiveImage().editor().componentBack());
            break;
        }
      });
    });
    document.querySelectorAll("[data-copg-ed-type='link']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_SWITCH_SETTINGS:
            dispatch.dispatch(action.interactiveImage().editor().switchSettings());
            break;
          case ACTIONS.E_SWITCH_OVERLAYS:
            dispatch.dispatch(action.interactiveImage().editor().switchOverlays());
            break;
          case ACTIONS.E_SWITCH_POPUPS:
            dispatch.dispatch(action.interactiveImage().editor().switchPopups());
            break;
        }
      });
    });
  }

  initShapeEditor() {
    const el = document.getElementById('il-copg-iim-main');
    const mob = el.querySelector('.ilc_Mob');
    console.log('initShapeEditor');
    if (mob) {
      this.shapeEditor = new ShapeEditor(mob);
      const ed = this.shapeEditor;
      // ed.addShape(ed.factory.rect(10,10, 200, 200));
      // ed.addShape(ed.factory.circle(210,210, 230,230));
      // const p = ed.factory.poly();
      // p.addHandle(ed.factory.handle(20,20));
      // p.addHandle(ed.factory.handle(30,200));
      // p.addHandle(ed.factory.handle(110,70));
      // p.addHandle(ed.factory.handle(60,30));
      // ed.addShape(p);
      // ed.repaint();
    }
  }

  showAllShapes() {
    const m = this.iimModel.model.iim;
    m.triggers.forEach((tr) => {
      const trigger = this.triggerFactory.fullTriggerFromModel(tr.Nr, m);
      if (trigger) {
        if (trigger.getShape()) {
          this.shapeEditor.addShape(trigger.getShape());
        } else if (trigger.getMarker()) {
          this.shapeEditor.addMarker(trigger.getMarker());
        }
      }
    });
    this.shapeEditor.repaint();
    this.initShapes();
  }

  initShapes() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    document.querySelectorAll("[data-copg-ed-type='shape']").forEach((shape) => {
      shape.addEventListener('click', (event) => {
        dispatch.dispatch(action.interactiveImage().editor().editTrigger(
          shape.dataset.triggerNr,
        ));
      });
    });
    document.querySelectorAll("[data-copg-iim-type='marker']").forEach((marker) => {
      marker.addEventListener('click', (event) => {
        dispatch.dispatch(action.interactiveImage().editor().editTrigger(
          marker.dataset.triggerNr,
        ));
      });
    });
  }

  setMainContent(html) {
    const el = document.getElementById('il-copg-iim-main');
    this.util.setInnerHTML(el, html);
  }

  addTrigger() {
    const trigger = this.iimModel.getCurrentTrigger();
    this.showTriggerProperties();
    this.shapeEditor.addShape(trigger.getShape());
    this.shapeEditor.repaint();
  }

  editTrigger(nr) {
    const trigger = this.iimModel.getCurrentTrigger();
    this.showTriggerProperties();
    this.setEditorAddMode();
    this.shapeEditor.removeAllShapes();
    this.shapeEditor.addShape(trigger.getShape(), true);
    this.shapeEditor.repaint();
  }

  setEditorAddMode() {
    const trigger = this.iimModel.getCurrentTrigger();
    this.shapeEditor.setAllowAdd(false);
    if (this.iimModel.getActionState() === this.iimModel.ACTION_STATE_ADD
      && trigger.getShape() instanceof Poly) {
      this.shapeEditor.setAllowAdd(true);
    }
  }

  repaintTrigger() {
    // const trigger = this.iimModel.getCurrentTrigger();
    this.setEditorAddMode();
    this.showCurrentShape(true);
    /*
    this.shapeEditor.removeAllShapes();
    this.shapeEditor.addShape(trigger.getShape(), true);
    this.shapeEditor.repaint(); */
  }

  formInput(nr) {
    return `form/input_${nr}`;
  }

  showTriggerProperties() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    const tr = this.iimModel.getCurrentTrigger();
    this.toolSlate.setContent(this.uiModel.triggerProperties);
    this.setInputValueByName('#copg-iim-trigger-prop-form', this.formInput(0), tr.title);
    if (tr.getShape()) {
      this.setInputValueByName('#copg-iim-trigger-prop-form', this.formInput(1), tr.area.shapeType);
    } else {
      this.setInputValueByName('#copg-iim-trigger-prop-form', this.formInput(1), 'Marker');
    }
    this.initTriggerViewControl();
    this.initBackButton();
    model = this.iimModel;
    document.querySelectorAll("form [data-copg-ed-type='form-button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        let coords;
        switch (act) {
          case ACTIONS.E_TRIGGER_PROPERTIES_SAVE:
            event.preventDefault();
            if (model.getCurrentTrigger().getShape()) {
              coords = model.getCurrentTrigger().getShape().getAreaCoordsString();
            } else {
              coords = model.getCurrentTrigger().getMarker().getCoordsString();
            }
            dispatch.dispatch(action.interactiveImage().editor().saveTriggerProperties(
              model.getCurrentTrigger().nr,
              this.getInputValueByName(this.formInput(0)),
              this.getInputValueByName(this.formInput(1)),
              coords,
            ));
            break;
        }
      });
    });
    document.querySelectorAll(`form [name='${this.formInput(1)}']`).forEach((select) => {
      select.addEventListener('change', (event) => {
        dispatch.dispatch(action.interactiveImage().editor().changeTriggerShape(
          this.getInputValueByName(this.formInput(1)),
        ));
      });
    });
    this.showCurrentShape(true);
    this.setMessage('triggerPropertiesMesssage');
  }

  getInputValueByName(name) {
    const path = `#copg-iim-trigger-prop-form input[name='${name}'],select[name='${name}']`;
    const el = document.querySelector(path);
    if (el) {
      return el.value;
    }
    return null;
  }

  setInputValueByName(sel, name, value) {
    const path = `${sel} input[name='${name}'],select[name='${name}']`;
    const el = document.querySelector(path);
    if (el) {
      el.value = value;
    }
  }

  setSelectOptions(sel, name, options, selected = null) {
    let op;
    const path = `${sel} select[name='${name}']`;
    const el = document.querySelector(path);
    if (el) {
      el.innerHTML = null;
      options.forEach((value, key) => {
        op = document.createElement('option');
        op.value = key;
        op.innerHTML = value;
        el.appendChild(op);
      });
    }
    if (selected) {
      el.value = selected;
    }
  }

  showTriggerOverlay() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    this.toolSlate.setContent(this.uiModel.triggerOverlay);
    const tr = this.iimModel.getCurrentTrigger();
    this.initTriggerViewControl();
    this.initBackButton();
    this.initTriggerOverlay();
    this.showCurrentShape(false, true);
    document.querySelectorAll(`form [name='${this.formInput(0)}']`).forEach((select) => {
      select.addEventListener('change', (event) => {
        dispatch.dispatch(action.interactiveImage().editor().changeTriggerOverlay(
          this.getInputValueByName(this.formInput(0)),
        ));
      });
    });
  }

  updateOverlayPresentationAfterSaving() {
    this.showCurrentShape(false, true);
  }

  updatePopupPresentationAfterSaving() {
    this.showCurrentShape(false, false);
    const tr = this.iimModel.getCurrentTrigger();
    let size;
    if (tr.getPopupNr() !== '') {
      size = tr.getPopupSize();
      if (size == '') {
        size = 'md';
      }
      this.showPopupDummy(size, tr.getNr());
    }
  }

  showCurrentShape(edit = false, showOverlay = false) {
    const trigger = this.iimModel.getCurrentTrigger();
    const overlay = trigger.getOverlay();
    this.shapeEditor.removeAllOverlays();
    this.shapeEditor.removeAllMarkers();
    this.removeDummyPopup();
    if (showOverlay && overlay) {
      this.setInputValueByName('#copg-iim-trigger-overlay-form', this.formInput(0), overlay.getSrc());
      this.shapeEditor.addOverlay(overlay, true);
    }
    this.shapeEditor.removeAllShapes();
    if (trigger.getShape()) {
      this.shapeEditor.addShape(trigger.getShape(), edit);
    }
    console.log('A');
    if (trigger.getMarker()) {
      console.log('B');
      this.shapeEditor.addMarker(trigger.getMarker(), edit);
    }
    this.shapeEditor.repaint();
  }

  initTriggerOverlay() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    document.querySelectorAll("[data-copg-ed-type='button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_OVERLAY_ADD:
            dispatch.dispatch(action.interactiveImage().editor().addTriggerOverlay());
            break;
        }
      });
    });
    document.querySelectorAll("form [data-copg-ed-type='form-button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_OVERLAY_SAVE:
            event.preventDefault();
            dispatch.dispatch(action.interactiveImage().editor().saveTriggerOverlay(
              model.getCurrentTrigger().nr,
              this.getInputValueByName(this.formInput(0)),
              model.getCurrentTrigger().getOverlay().getCoordsString(),
            ));
            break;
        }
      });
    });
    const options = new Map();
    options.set('', ' - ');
    this.iimModel.getOverlays().forEach((ov) => {
      options.set(ov.name, ov.name);
    });
    this.setSelectOptions('#copg-editor-slate-content', this.formInput(0), options);
  }

  showTriggerPopup() {
    this.toolSlate.setContent(this.uiModel.triggerPopup);
    this.initTriggerViewControl();
    this.initBackButton();
    this.initTriggerPopup();
    const tr = this.iimModel.getCurrentTrigger();
    let size;
    if (tr.getPopupNr() !== '') {
      size = tr.getPopupSize();
      if (size == '') {
        size = 'md';
      }
      this.setInputValueByName('#copg-iim-trigger-overlay-form', this.formInput(0), tr.getPopupNr());
      this.setInputValueByName('#copg-iim-trigger-overlay-form', this.formInput(1), size);
    }
    this.showCurrentShape();
    if (tr.getPopupNr() !== '') {
      this.showPopupDummy(size, tr.getNr());
    }
  }

  removeDummyPopup() {
    document.querySelectorAll("[data-copg-iim-type='dummmy-popup']").forEach((d) => {
      d.remove();
    });
  }

  showPopupDummy(size, triggerNr) {
    const mainEl = document.getElementById('il-copg-iim-main');
    const dummy = document.createElement('div');
    this.removeDummyPopup();
    dummy.setAttribute('data-copg-iim-type', 'dummmy-popup');
    let ln = 160;
    switch (size) {
      case 'sm': ln = 40; break;
      case 'lg': ln = 360; break;
    }
    dummy.innerHTML = this.uiModel.popupDummy.replace('###content###', `${this.uiModel.lore.substr(0, ln)}...`);
    mainEl.appendChild(dummy);
    const popEl = mainEl.querySelector("[data-copg-cont-type='iim-popup']");
    popEl.classList.remove('copg-iim-popup-md');
    popEl.classList.add(`copg-iim-popup-${size}`);
    this.iimCommonUtil.attachPopupToTrigger(mainEl, mainEl, popEl, triggerNr);
  }

  initTriggerPopup() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    document.querySelectorAll("[data-copg-ed-type='button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_POPUP_ADD:
            dispatch.dispatch(action.interactiveImage().editor().addTriggerPopup());
            break;
        }
      });
    });
    document.querySelectorAll("form [data-copg-ed-type='form-button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_POPUP_SAVE:
            event.preventDefault();
            dispatch.dispatch(action.interactiveImage().editor().saveTriggerPopup(
              model.getCurrentTrigger().nr,
              this.getInputValueByName(this.formInput(0)),
              'Horizontal',
              this.getInputValueByName(this.formInput(1)),
            ));
            break;
        }
      });
    });
    const options = new Map();
    options.set('', ' - ');
    this.iimModel.getPopups().forEach((pop) => {
      options.set(`${pop.nr}`, pop.title);
    });
    this.setSelectOptions('#copg-editor-slate-content', this.formInput(0), options);
  }

  initBackButton() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    document.querySelectorAll("[data-copg-ed-type='button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_BACK:
            dispatch.dispatch(action.interactiveImage().editor().triggerBack());
            break;
        }
      });
    });
  }

  initTriggerViewControl() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='view-control']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_PROPERTIES:
            dispatch.dispatch(action.interactiveImage().editor().triggerProperties());
            break;
          case ACTIONS.E_TRIGGER_OVERLAY:
            dispatch.dispatch(action.interactiveImage().editor().triggerOverlay());
            break;
          case ACTIONS.E_TRIGGER_POPUP:
            dispatch.dispatch(action.interactiveImage().editor().triggerPopup());
            break;
        }
      });
    });
    this.refreshTriggerViewControl();
  }

  setMessage(mess) {
    const messArea = document.getElementById('cont_iim_message');
    if (messArea) {
      if (this.uiModel[mess]) {
        messArea.innerHTML = this.uiModel[mess];
      }
    }
  }

  setLoader() {
    const messArea = document.getElementById('cont_iim_message');
    if (messArea) {
      messArea.innerHTML = this.uiModel.loader;
    }
  }

  refreshTriggerViewControl() {
    const model = this.iimModel;
    const prop = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='trigger.properties']");
    const ov = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='trigger.overlay']");
    const pop = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='trigger.popup']");
    prop.classList.remove('engaged');
    ov.classList.remove('engaged');
    pop.classList.remove('engaged');
    prop.disabled = false;
    ov.disabled = false;
    pop.disabled = false;
    if (model.getState() === model.STATE_TRIGGER_PROPERTIES) {
      prop.disabled = true;
      prop.classList.add('engaged');
    } else if (model.getState() === model.STATE_TRIGGER_OVERLAY) {
      ov.disabled = true;
      ov.classList.add('engaged');
    } else if (model.getState() === model.STATE_TRIGGER_POPUP) {
      pop.disabled = true;
      pop.classList.add('engaged');
    }
  }

  showSettings() {
    this.toolSlate.setContent(this.uiModel.backgroundProperties);
    this.initBackButton();
    this.initSettings();
  }

  initSettings() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    document.querySelectorAll("form [data-copg-ed-type='form-button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_SAVE_SETTINGS:
            event.preventDefault();
            const form = document.querySelector('#copg-editor-slate-content form');
            dispatch.dispatch(action.interactiveImage().editor().saveSettings(
              form,
            ));
            break;
        }
      });
    });
    this.setInputValueByName(
      '#copg-editor-slate-content',
      this.formInput(1),
      this.iimModel.getCaption(),
    );
  }

  showOverlays() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    this.toolSlate.setContent(this.uiModel.overlayOverview);
    this.initBackButton();
    this.initOverlayList();
    document.querySelectorAll("[data-copg-ed-type='button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_OVERLAY_ADD:
            dispatch.dispatch(action.interactiveImage().editor().addTriggerOverlay());
            break;
        }
      });
    });
  }

  initOverlayList() {
    const overlays = this.iimModel.getOverlays();
    const action = this.actionFactory;
    const items = [];
    overlays.forEach((ov) => {
      items.push({
        placeholders: {
          'item-title': ov.name,
          'img-alt': ov.name,
          'img-src': ov.thumbpath,
        },
        actions: [
          {
            action: action.interactiveImage().editor().deleteOverlay(ov.name),
            txt: il.Language.txt('delete'),
          },
        ],
      });
    });
    this.fillItemList(items);
  }

  fillItemList(items) {
    let newNode; let newLiNode; let liTempl; let
      liParent;
    const dispatch = this.dispatcher;
    const templEl = document.querySelector('#copg-editor-slate-content .il-std-item-container');
    const parent = templEl.parentNode;
    items.forEach((item) => {
      newNode = templEl.cloneNode(true);
      for (const [key, value] of Object.entries(item.placeholders)) {
        newNode.innerHTML = newNode.innerHTML.replace(
          `#${key}#`,
          value,
        );
      }
      liTempl = newNode.querySelector('.dropdown-menu li');
      liParent = liTempl.parentNode;
      item.actions.forEach((action) => {
        newLiNode = liTempl.cloneNode(true);
        newLiNode.innerHTML = newLiNode.innerHTML.replace(
          '#link-label#',
          action.txt,
        );
        newLiNode = liParent.appendChild(newLiNode);
        newLiNode.addEventListener('click', () => {
          dispatch.dispatch(action.action);
        });
      });
      liTempl.remove();
      parent.appendChild(newNode);
    });
    templEl.remove();
  }

  showPopups() {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    this.toolSlate.setContent(this.uiModel.popupOverview);
    this.initBackButton();
    this.initPopupList();
    document.querySelectorAll("[data-copg-ed-type='button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_POPUP_ADD:
            dispatch.dispatch(action.interactiveImage().editor().addTriggerPopup());
            break;
        }
      });
    });
  }

  initPopupList() {
    const popups = this.iimModel.getPopups();
    const action = this.actionFactory;
    const items = [];
    popups.forEach((pop) => {
      items.push({
        placeholders: {
          'item-title': pop.title,
        },
        actions: [
          {
            action: action.interactiveImage().editor().renamePopup(pop.nr),
            txt: il.Language.txt('rename'),
          },
          {
            action: action.interactiveImage().editor().deletePopup(pop.nr),
            txt: il.Language.txt('delete'),
          },
        ],
      });
    });
    this.fillItemList(items);
  }

  showOverlayModal() {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    this.util.showModal(
      this.uiModel.modal,
      il.Language.txt('cont_iim_add_overlay'),
      this.uiModel.overlayUpload,
      il.Language.txt('add'),
      (e) => {
        const form = document.querySelector('#il-copg-ed-modal form');

        // after_pcid, pcid, component, data
        dispatch.dispatch(action.interactiveImage().editor().uploadOverlay(
          {
            form,
          },
        ));
      },
    );
  }

  showPopupModal(params = null, model = null) {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    const nr = (params) ? params.nr : '';
    this.util.showModal(
      this.uiModel.modal,
      il.Language.txt('cont_add_popup'),
      this.uiModel.popupForm,
      il.Language.txt('save'),
      (e) => {
        const form = document.querySelector('#il-copg-ed-modal form');

        // after_pcid, pcid, component, data
        dispatch.dispatch(action.interactiveImage().editor().savePopup(
          {
            form,
          },
          nr,
        ));
      },
    );
    if (params) {
      this.setInputValueByName('.modal-content', this.formInput(0), model.getPopupTitle(params.nr));
    }
  }

  deactivateSlateButtons() {
    const model = this.iimModel;
    const prop = document.querySelectorAll('#copg-editor-slate-content button').forEach((b) => {
      b.disabled = true;
    });
  }

  activateSlateButtons() {
    const model = this.iimModel;
    const prop = document.querySelectorAll('#copg-editor-slate-content button').forEach((b) => {
      b.disabled = false;
    });
  }

  refreshMainScreen() {
    this.setMainContent(this.uiModel.backgroundImage);
    this.initShapeEditor();
    this.showAllShapes();
    // ensure img is not scaled
    const img = document.querySelector('#il-copg-iim-main .ilc_Mob img');
    img.style.width = 'auto';
  }

  redirectToPage() {
    // this.pageUI.uiModel.backUrl + "#pc" + pcid
    window.location.replace(this.uiModel.backUrl);
  }
}
