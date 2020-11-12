/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import TableEditorActionFactory from './table-editor-action-factory.js';
import TableCommandActionFactory from './table-command-action-factory.js';
import EditorActionFactory from '../../../actions/editor-action-factory.js';

/**
 * action factory for calling the server
 */
export default class TableActionFactory {

  /**
   * @type {EditorActionFactory}
   */
  //editorActionFactory;


  /**
   *
   * @param {ClientActionFactory} clientActionFactory
   * @param {EditorActionFactory} editorActionFactory
   */
  constructor(clientActionFactory, editorActionFactory) {
    this.clientActionFactory = clientActionFactory;
    this.editorActionFactory = editorActionFactory;
  }

  /**
   * @returns {TableEditorActionFactory}
   */
  editor() {
    return new TableEditorActionFactory(this.editorActionFactory);
  }

  /**
   * @returns {TableCommandActionFactory}
   */
  command() {
    return new TableCommandActionFactory(this.clientActionFactory);
  }

}