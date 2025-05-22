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

import il from 'ilias';
import orderingHandler from './orderingverticalHandler.js';
import makeDraggable from '../makeDraggable.js';

il.test = il.test || {};
il.test.orderingvertical = il.test.orderingvertical || {};

il.test.orderingvertical.init = (parentElement) => orderingHandler(
  parentElement,
  makeDraggable,
);
