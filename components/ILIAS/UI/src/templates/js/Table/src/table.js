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
import DataTableFactory from './datatable.factory';
import PresentationTableFactory from './presentationtable.factory';

il.UI = il.UI || {};
il.UI.table = il.UI.table || {};
il.UI.table.data = new DataTableFactory($);
il.UI.table.presentation = new PresentationTableFactory();
