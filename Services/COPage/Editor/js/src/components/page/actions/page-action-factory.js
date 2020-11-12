/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import PageQueryActionFactory from './page-query-action-factory.js';
import PageCommandActionFactory from './page-command-action-factory.js';
import PageEditorActionFactory from './page-editor-action-factory.js';
import EditorActionFactory from '../../../actions/editor-action-factory.js';

/**
 * action factory for calling the server
 */
export default class PageActionFactory {

  /**
   * @type {ClientActionFactory}
   */
  //clientActionFactory;

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
   * @returns {PageQueryActionFactory}
   */
  query() {
    return new PageQueryActionFactory(this.clientActionFactory);
  }

  /**
   * @returns {PageCommandActionFactory}
   */
  command() {
    return new PageCommandActionFactory(this.clientActionFactory);
  }

  /**
   * @returns {PageEditorActionFactory}
   */
  editor() {
    return new PageEditorActionFactory(this.editorActionFactory);
  }

}