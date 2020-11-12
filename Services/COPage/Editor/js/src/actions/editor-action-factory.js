/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import EditorAction from './editor-action.js';

/**
 * action factory for calling the server
 */
export default class EditorActionFactory {
  /**
   * @param {string} component
   * @param {string} type
   * @param {Object} params
   */
  action(component, type, params) {
    return new EditorAction(component, type, params);
  }

}