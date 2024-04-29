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
 */

namespace ILIAS\UI\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\Container\ViewControl\ViewControlInput;
use ILIAS\UI\Component\Input\Group as GroupInterface;

/**
 * Describes the monoid operation of grouping view control inputs.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Group extends ViewControlInput, GroupInterface
{
}
