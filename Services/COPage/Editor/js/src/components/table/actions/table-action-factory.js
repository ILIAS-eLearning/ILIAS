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