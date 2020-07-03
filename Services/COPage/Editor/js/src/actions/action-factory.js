/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import QueryActionFactory from '../client/actions/query-action-factory.js';
import CommandActionFactory from '../client/actions/command-action-factory.js';
import EditorActionFactory from './editor-action-factory.js';

/**
 * action factory for calling the server
 */
export default class ActionFactory {

  /**
   */
  constructor() {
  }

  /**
   * @returns {QueryActionFactory}
   */
  query() {
    return new QueryActionFactory();
  }

  /**
   * @returns {CommandActionFactory}
   */
  command() {
    return new CommandActionFactory();
  }

  /**
   * @returns {EditorActionFactory}
   */
  editor() {
    return new EditorActionFactory();
  }

}