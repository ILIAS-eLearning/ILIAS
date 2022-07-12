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

let actionId = 0;

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
  //next_id = 1;

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
    actionId++;
    this.id = actionId;
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