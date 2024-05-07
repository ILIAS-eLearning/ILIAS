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