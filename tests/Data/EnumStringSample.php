<?php

declare(strict_types=1);

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

namespace Data;

use ILIAS\Data\Enum;

/**
 * @method self CASE1()
 * @method self CASE2()
 * @method self CASE3()
 */
class EnumStringSample
{
    use Enum;

    private const CASE1 = 'case1';
    private const CASE2 = 'case2';
    private const CASE3 = 'case3';
}
