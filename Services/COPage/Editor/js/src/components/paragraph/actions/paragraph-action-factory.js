/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ParagraphEditorActionFactory from './paragraph-editor-action-factory.js';
import ParagraphCommandActionFactory from './paragraph-command-action-factory.js';
import EditorActionFactory from '../../../actions/editor-action-factory.js';

/**
 * action factory for calling the server
 */
export default class ParagraphActionFactory {

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
   * @returns {ParagraphEditorActionFactory}
   */
  editor() {
    return new ParagraphEditorActionFactory(this.editorActionFactory);
  }

  /**
   * @returns {ParagraphCommandActionFactory}
   */
  command() {
    return new ParagraphCommandActionFactory(this.clientActionFactory);
  }

}