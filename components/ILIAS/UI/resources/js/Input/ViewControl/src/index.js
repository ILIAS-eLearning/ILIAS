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

import il from 'il';
import $ from 'jquery';
import FieldSelection from './fieldselection.js';
import Sortation from './sortation.js';
import Pagination from './pagination.js';
import Mode from './mode.js';
import JQueryEventDispatcher from '../../../Core/src/jqueryeventdispatcher.js';

const eventDispatcher = new JQueryEventDispatcher($);

il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
il.UI.Input.Viewcontrols = il.UI.Input.Viewcontrols || {};
il.UI.Input.Viewcontrols.FieldSelection = new FieldSelection(eventDispatcher);
il.UI.Input.Viewcontrols.Sortation = new Sortation(eventDispatcher);
il.UI.Input.Viewcontrols.Pagination = new Pagination(eventDispatcher);
il.UI.Input.Viewcontrols.Mode = new Mode(eventDispatcher);
