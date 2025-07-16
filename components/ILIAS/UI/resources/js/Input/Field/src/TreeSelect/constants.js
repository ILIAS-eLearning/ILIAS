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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

/** @type {string} data attribute name */
export const NODE_ID = 'data-node-id';
/** @type {string} data attribute name */
export const NODE_NAME = 'data-node-name';
/** @type {string} data attribute name */
export const NODE_RENDER_URL = 'data-render-url';
/** @type {string} data attribute name */
export const DRILLDOWN_LEVEL = 'data-ddindex';

/** @type {string} css class */
export const NODE_CLASS = 'c-input-node';
/** @type {string} css class */
export const TREE_SELECT_CLASS = 'c-input-tree_select';
/** @type {string} css class */
export const ASYNC_NODE_CLASS = `${NODE_CLASS}__async`;
/** @type {string} css class */
export const LEAF_NODE_CLASS = `${NODE_CLASS}__leaf`;
/** @type {string} css class */
export const SELECTED_NODE_CLASS = `${NODE_CLASS}--selected`;
/** @type {string} css class */
export const HIDDEN_CLASS = 'hidden';
/** @type {string} css class */
export const DISABLED_CLASS = 'disabled';

/** @type {string} query selector */
export const GLYPH = '.glyph';
/** @type {string} query selector */
export const NODE = `.${NODE_CLASS}`;
/** @type {string} query selector */
export const CRUMB = '.crumb';
/** @type {string} query selector */
export const BREADCRUMB = '.breadcrumb';
/** @type {string} query selector */
export const DRILLDOWN = '.c-drilldown';
/** @type {string} query selector */
export const TREE_SELECT = `.${TREE_SELECT_CLASS}`;
/** @type {string} query selector */
export const TREE_SELECT_SELECTION = `.${TREE_SELECT_CLASS}__selection`;
/** @type {string} query selector */
export const TREE_SELECT_BUTTON = '.btn-primary';
/** @type {string} query selector */
export const CLOSE_ACTION = '[data-action="close"]';
/** @type {string} query selector */
export const REMOVE_ACTION = '[data-action="remove"]';
/** @type {string} query selector */
export const SELECT_ACTION = '[data-action="select"]';
/** @type {string} query selector */
export const NODE_SELECT_BUTTON = `.${NODE_CLASS}__select`;
/** @type {string} query selector */
export const DRILLDOWN_BUTTON = '.c-drilldown__menulevel--trigger';
