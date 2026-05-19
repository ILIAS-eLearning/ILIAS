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

import il from 'ilias';
import createTooltip from './createTooltip.js';
import createHoverTooltip from './createHoverTooltip.js';
import createClickTooltip from './createClickTooltip.js';

il.UI = il.UI || {};

il.UI.Tooltip = {
  createTooltip,
  createHoverTooltip,
  createClickTooltip,
};
