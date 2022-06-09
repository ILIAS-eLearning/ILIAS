/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Action
 */
export default class Action {

  /**
   * @type {string}
   */
  //component;

  /**
   * @type {string}
   */
  //type;

  /**
   * @type {number}
   */
  static next_id = 1;

  /**
   * @type {number}
   */
//  id;

  /**
   * @param {string} component
   * @param {string} type
   * @param {Object} params
   */
  constructor(component, type, params= {}, queueable = false) {
    this.component = component;
    this.type = type;
    this.id = Action.next_id++;       // maybe switch to uuid in the future
    //this.id = 1;
    this.params = params;
    this.queueable = queueable;
  }

  /**
   * @returns {string}
   */
  getComponent() {
    return this.component;
  }

  /**
   * @returns {string}
   */
  getType() {
    return this.type;
  }

  /**
   * @returns {number}
   */
  getId() {
    return this.id;
  }

  /**
   * @returns {Object}
   */
  getParams () {
    return this.params;
  }

  /**
   * @returns {bool}
   */
  getQueueable () {
    return this.queueable;
  }

}