<?php

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

namespace ILIAS\UI;

/**
 * Storage is simple key/value store without further schema definition.
 * It's used to (mainly temporarily) keep parameters induced by a user interaction,
 * e.g. operating view controls.
 * Storage has no data-separation or id-handling on its own, so, recommandedly,
 * you will store the whole of your data at once in a suitable format
 * as a single value depending on a key unique to your view.
 */
interface Storage extends \ArrayAccess
{
}
