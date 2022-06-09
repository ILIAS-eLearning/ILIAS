/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "./page-action-types.js";

/**
 * COPage command actions being sent to the server
 */
export default class PageCommandActionFactory {

  //COMPONENT = "Page";

  /**
   * @type {ClientActionFactory}
   */
  //clientActionFactory;

  /**
   * @param {ClientActionFactory} clientActionFactory
   */
  constructor(clientActionFactory) {
    this.COMPONENT = "Page";
    this.clientActionFactory = clientActionFactory;
  }

  /**
   * @param {string} ctype
   * @param {string} pcid
   * @param {string} hier_id
   * @param {string} pluginName
   * @return {CommandAction}
   */
  createLegacy(ctype, pcid, hier_id, pluginName) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.CREATE_LEGACY, {
      cmd: "insert",
      ctype: ctype,
      pcid: pcid,
      hier_id: hier_id,
      pluginName: pluginName
    });
  }

  /**
   * @param {string} cname
   * @param {string} pcid
   * @param {string} hier_id
   * @return {CommandAction}
   */
  editLegacy(cname, pcid, hier_id) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.EDIT_LEGACY, {
      cmd: "edit",
      cname: cname,
      pcid: pcid,
      hier_id: hier_id
    });
  }

  /**
   * @param {string} type
   * @param {[]} ids
   * @return {CommandAction}
   */
  multiLegacy(type, ids) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.MULTI_LEGACY, {
      cmd: type,
      ids: ids
    });
  }

  /**
   * @param {[]} pcids
   * @param {string} target_pcid
   * @return {CommandAction}
   */
  paste(target_pcid) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.PASTE, {
      target_pcid: target_pcid
    });
  }

  /**
   * @param {[]} pcids
   * @return {CommandAction}
   */
  cut(pcids) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.CUT, {
      pcids: pcids
    });
  }

  /**
   * @param {[]} pcids
   * @return {CommandAction}
   */
  copy(pcids) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.COPY, {
      pcids: pcids
    });
  }

  /**
   * @param {string} target
   * @param {string} source
   * @return {CommandAction}
   */
  dragDrop(target, source) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.DRAG_DROP, {
      target: target,
      source: source
    });
  }

  /**
   * @param {[]} pcids
   * @param {string} paragraph_format
   * @param {string} section_format
   * @param {string} media_format
   * @return {CommandAction}
   */
  format(pcids, paragraph_format, section_format, media_format) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.FORMAT, {
      pcids: pcids,
      paragraph_format: paragraph_format,
      section_format: section_format,
      media_format: media_format
    });
  }

  /**
   * @param {[]} pcids
   * @return {CommandAction}
   */
  delete(pcids) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.DELETE, {
      pcids: pcids
    });
  }

  /**
   * @param {[]} pcids
   * @return {CommandAction}
   */
  activate(pcids) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.ACTIVATE, {
      pcids: pcids
    });
  }

  /**
   *
   * @param {string} after_pcid
   * @param {string} pcid
   * @param {string} component
   * @param {formData} data
   * @return {CommandAction}
   */
  insert(after_pcid, pcid, component, data) {
    data.append("after_pcid", after_pcid);
    data.append("pcid", pcid);
    return this.clientActionFactory.formCommand(component, ACTIONS.INSERT, data);
  }

  /**
   *
   * @param {string} pcid
   * @param {string} component
   * @param {formData} data
   * @return {CommandAction}
   */
  update(pcid, component, data) {
    data.append("pcid", pcid);
    return this.clientActionFactory.formCommand(component, ACTIONS.UPDATE, data);
  }

  /**
   * @param {string} cname
   * @param {string} pcid
   * @param {string} hier_id
   * @return {CommandAction}
   */
  editListItem(listCmd, component, pcid) {
    return this.clientActionFactory.command(component, ACTIONS.LIST_EDIT, {
      cmd: "editListItem",
      list_cmd: listCmd,
      pcid: pcid
    });
  }

}