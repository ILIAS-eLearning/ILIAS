/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import PageActionFactory from '../components/page/actions/page-action-factory.js';
import ParagraphActionFactory from '../components/paragraph/actions/paragraph-action-factory.js';
import MediaActionFactory from '../components/media/actions/media-action-factory.js';
import TableActionFactory from '../components/table/actions/table-action-factory.js';
import ClientActionFactory from '../client/actions/client-action-factory.js';
import EditorActionFactory from './editor-action-factory.js';

/**
 * action factory for calling the server
 */
export default class ActionFactory {

  /**
   * @type {ClientActionFactory}
   */
  //clientActionFactory;

  /**
   * @type {EditorActionFactory}
   */
  //editorActionFactory;

  /**
   */
  constructor() {
    this.clientActionFactory = new ClientActionFactory();
    this.editorActionFactory = new EditorActionFactory();
  }

  /**
   * @returns {PageActionFactory}
   */
  page() {
    return new PageActionFactory(this.clientActionFactory, this.editorActionFactory);
  }

  /**
   * @returns {ParagraphActionFactory}
   */
  paragraph() {
    return new ParagraphActionFactory(this.clientActionFactory, this.editorActionFactory);
  }

  /**
   * @returns {MediaActionFactory}
   */
  media() {
    return new MediaActionFactory(this.clientActionFactory, this.editorActionFactory);
  }

  /**
   * @returns {TableActionFactory}
   */
  table() {
    return new TableActionFactory(this.clientActionFactory, this.editorActionFactory);
  }

}