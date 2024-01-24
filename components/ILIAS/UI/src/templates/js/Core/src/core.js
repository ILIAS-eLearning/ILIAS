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
import document from 'document';
import Tooltip from './Tooltip';
import URLBuilder from './URLBuilder';
import URLBuilderToken from './URLBuilderToken';
import HydrationRegistry from './Hydration/HydrationRegistry';
import hydrateComponents from './Hydration/hydrateComponents';
import AsyncRenderer from './AsyncRenderer';

il.UI = il.UI || {};
il.UI.core = il.UI.core || {};

il.UI.core.Tooltip = Tooltip;
il.UI.core.URLBuilder = URLBuilder;
il.UI.core.URLBuilderToken = URLBuilderToken;

il.UI.core.HydrationRegistry = new HydrationRegistry();
il.UI.core.AsyncRenderer = new AsyncRenderer(il.UI.core.HydrationRegistry, document);
il.UI.core.hydrateComponents = (element) => {
  hydrateComponents(il.UI.core.HydrationRegistry, element);
};
