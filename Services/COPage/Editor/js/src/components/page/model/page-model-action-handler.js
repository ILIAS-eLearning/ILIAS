/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Model action handler
 */
export default class ModelActionHandler {

  /**
   * {Model}
   */
  //model;

  /**
   *
   * @param {PageModel} model
   */
  constructor(model) {
    this.model = model;
  }


  /**
   * @return {Model}
   */
  getModel() {
    return this.model;
  }

  /**
   * @param {EditorAction} action
   */
  handle(action) {

    const params = action.getParams();

    switch (action.getType()) {

      case "dnd.drag":
        this.model.setState(this.model.STATE_DRAG_DROP);
        break;

      case "dnd.drop":
        this.model.setState(this.model.STATE_SERVER_CMD);
        break;

      case "switch.multi":
        this.model.setState(this.model.STATE_MULTI_ACTION);
        break;

      case "switch.single":
        this.model.selectNone();
        this.model.setState(this.model.STATE_PAGE);
        break;

      case "multi.toggle":
        this.model.setState(this.model.STATE_MULTI_ACTION);   // note that cmd+click comes from page state
        this.model.toggleSelect(params.pcid, params.hierid);
        break;

      case "multi.action":
        console.log("page-model-action-hanlder multi.action " + params.type);
        switch (params.type) {

          case "cut":
            this.model.cut();
            if (this.model.hasSelected()) {
              this.model.activatePasting(true);
            }
            this.model.selectNone();
            this.model.setState(this.model.STATE_PAGE);
            this.model.setMultiState(this.model.STATE_MULTI_CUT);
            break;

          case "copy":
            this.model.copy();
            if (this.model.hasSelected()) {
              this.model.activatePasting(true);
            }
            this.model.selectNone();
            this.model.setMultiState(this.model.STATE_MULTI_COPY);
            this.model.setState(this.model.STATE_PAGE);
            break;

          case "characteristic":
            this.model.setMultiState(this.model.STATE_MULTI_CHARACTERISTIC);
            break;

          case "none":
            this.model.selectNone();
            break;

          case "all":
            this.model.selectAll();
            break;
        }
        break;

      case "multi.paste":
        this.model.setState(this.model.STATE_SERVER_CMD);
        this.model.setMultiState(this.model.STATE_MULTI_NONE);
        break;

      case "component.edit":
        this.model.setState(this.model.STATE_COMPONENT);
        this.model.setComponentState(this.model.STATE_COMPONENT_EDIT);
        this.model.setCurrentPageComponent(params.cname, params.pcid, params.hierid);

        this.model.setUndoPCModel(
          this.model.getCurrentPCId(),
          this.model.getPCModel(this.model.getCurrentPCId())
        );
        break;

      case "component.insert":
        this.model.setState(this.model.STATE_COMPONENT);
        this.model.setComponentState(this.model.STATE_COMPONENT_INSERT);
        this.model.setCurrentInsertPCId(params.pcid);   // insert after...
        this.model.setInsertFromPlaceholder(params.fromPlaceholder);
        const pcid = this.model.getNewPCId();
        this.model.setCurrentPageComponent(params.cname, pcid, '');
        break;

      case "component.switch":
        // we do nothing here, the components decide whether to perform the switch or not
        break;

      case "component.saved":
        this.model.setState(this.model.STATE_PAGE);
        break;

      case "component.update":
        this.model.setState(this.model.STATE_SERVER_CMD);
        break;

      case "component.cancel":
        this.model.undoPCModel(
          this.model.getCurrentPCId()
        );
        this.model.setState(this.model.STATE_PAGE);
        // note: we keep the component state and current component here, so that handlers
        // can use this
        break;

      case "format.section":
        this.model.setSectionFormat(params.format);
        break;

      case "format.media":
        this.model.setMediaFormat(params.format);
        break;

      case "format.paragraph":
        this.model.setParagraphFormat(params.format);
        break;

      case "format.save":
        let m, i;
        const pcids = Array.from(
          params.pcids).map(x => (x.split(":")[1])
        );
        for(i = 0; i < pcids.length; i++) {
          m = this.model.getPCModel(pcids[i]);
          // update paragraph characteristic
          if (m.text) {
            m.characteristic = params.parFormat;
            this.model.setPCModel(pcids[i], m);
          }
        }
        this.model.selectNone();
        this.model.setState(this.model.STATE_PAGE);
        this.model.setMultiState(this.model.STATE_MULTI_NONE);
        break;

      case "format.cancel":
        this.model.selectNone();
        this.model.setState(this.model.STATE_PAGE);
        this.model.setMultiState(this.model.STATE_MULTI_NONE);
        break;

      case "multi.delete":
        this.model.selectNone();
        this.model.setState(this.model.STATE_SERVER_CMD);
        this.model.setMultiState(this.model.STATE_MULTI_NONE);
        break;

      case "multi.activate":
        this.model.selectNone();
        this.model.setState(this.model.STATE_SERVER_CMD);
        this.model.setMultiState(this.model.STATE_MULTI_NONE);
        break;

      case "page.editing":
        this.model.selectNone();
        this.model.setState(this.model.STATE_PAGE);
        break;

      case "list.edit":
        this.model.setState(this.model.STATE_SERVER_CMD);
        break;
    }
  }
}