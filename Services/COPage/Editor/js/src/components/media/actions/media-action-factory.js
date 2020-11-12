/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import MediaEditorActionFactory from './media-editor-action-factory.js';
import EditorActionFactory from '../../../actions/editor-action-factory.js';

/**
 * action factory for calling the server
 */
export default class MediaActionFactory {

  /**
   * @type {EditorActionFactory}
   */
//  editorActionFactory;


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
   * @returns {MediaEditorActionFactory}
   */
  editor() {
    return new MediaEditorActionFactory(this.editorActionFactory);
  }

  /**
   * @returns {ParagraphCommandActionFactory}
   */
  /*
  command() {
    return new ParagraphCommandActionFactory(this.clientActionFactory);
  }*/

}