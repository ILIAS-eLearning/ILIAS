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

import IIMEditorActionFactory from './iim-editor-action-factory.js';
import IIMQueryActionFactory from './iim-query-action-factory.js';
import IIMCommandActionFactory from './iim-command-action-factory.js';
import ClientActionFactory from '../../../../../../Editor/js/src/client/actions/client-action-factory.js';
import EditorActionFactory from '../../../../../../Editor/js/src/actions/editor-action-factory.js';

/**
 * action factory for calling the server
 */
export default class IIMActionFactory {

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
   * @returns {IIMQueryActionFactory}
   */
  query() {
    return new IIMQueryActionFactory(this.clientActionFactory);
  }

  /**
   * @returns {IIMEditorActionFactory}
   */
  editor() {
    return new IIMEditorActionFactory(this.editorActionFactory);
  }

  /**
   * @returns {IIMCommandActionFactory}
   */
  command() {
    return new IIMCommandActionFactory(this.clientActionFactory);
  }

}