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
import MetabarFactory from './metabar.factory';
import Slate from './slate.class';
import replaceContent from '../../Core/src/core.replaceContent';
import { counterFactory } from '../../Counter/src/counter.main';

il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

il.UI.maincontrols.metabar = new MetabarFactory(
  $,
  il.UI.page.isSmallScreen,
  counterFactory($),
  () => il.UI.maincontrols.mainbar.disengageAll(),
  (slate) => il.UI.maincontrols.slate.disengage(slate),
);
il.UI.maincontrols.slate = new Slate(
  $,
  replaceContent($),
  il.UI.maincontrols.metabar,
);
