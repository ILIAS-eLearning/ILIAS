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
 */

/**
 * This script serves as the bootstrap file for all inputs within the
 * Field/src/ directory (which have been implemented as ES6 modules).
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The script is necessary due to rollup.js bundeling, which creates
 * duplicate declarations if e.g. classes were to extend from each-
 * other and are bundled into separate files.
 */

import $ from 'jquery';
import il from 'ilias';
import document from 'document';
import TextareaFactory from './Textarea/textarea.factory.js';
import MarkdownFactory from './Markdown/markdown.factory.js';
import TreeSelectFactory from './TreeSelect/TreeSelectFactory.js';
import JQueryEventListener from '../../../Core/src/JQueryEventListener.js';
import Tagify from '@yaireo/tagify';
import tag from './Tag/tag.js';

il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};

(function (Input) {
  Input.textarea = new TextareaFactory();
  Input.markdown = new MarkdownFactory();
  Input.treeSelect = new TreeSelectFactory(
    new JQueryEventListener($),
    il.UI.menu.drilldown,
    // workaround for language being initialised after UI
    {txt: (s) => il.Language.txt(s)},
    document,
  );
  Input.tagInput = Input.tag || {};
  Input.tagInput.init = (input, config, value, autocompleteEndpoint, autocompleteToken) => tag(
    Tagify, input, config, value, autocompleteEndpoint, autocompleteToken);
}(il.UI.Input));
