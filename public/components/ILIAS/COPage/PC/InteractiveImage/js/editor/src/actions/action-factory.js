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

import InteractiveImageFactory from './iim-action-factory.js';
import ClientActionFactory from '../../../../../../Editor/js/src/client/actions/client-action-factory.js';
import EditorActionFactory from '../../../../../../Editor/js/src/actions/editor-action-factory.js';

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
   * @returns {InteractiveImageFactory}
   */
  interactiveImage() {
    return new InteractiveImageFactory(this.clientActionFactory, this.editorActionFactory);
  }
}