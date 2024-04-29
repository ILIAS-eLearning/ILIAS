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
 ********************************************************************
 */

import il from 'il';
import $ from 'jquery';
import replaceContent from './core.replaceContent';
import Tooltip from './core.Tooltip';
import URLBuilder from './core.URLBuilder';
import URLBuilderToken from './core.URLBuilderToken';

il.UI = il.UI || {};
il.UI.core = il.UI.core || {};

il.UI.core.replaceContent = replaceContent($);
il.UI.core.Tooltip = Tooltip;
il.UI.core.URLBuilder = URLBuilder;
il.UI.core.URLBuilderToken = URLBuilderToken;
