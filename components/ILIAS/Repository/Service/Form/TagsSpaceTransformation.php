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

declare(strict_types=1);

namespace ILIAS\Repository\Form;

use ILIAS\Data;
use ILIAS\Refinery\Custom\Constraint;
use ilLanguage;
use ILIAS\Refinery\Transformation;

/**
 * This implements the behaviour discussed here: https://mantis.ilias.de/view.php?id=19727
 */
class TagsSpaceTransformation extends \ILIAS\Refinery\Custom\Transformation
{
    public function __construct()
    {
        parent::__construct(
            static function ($v): string {
                $stripped = strip_tags($v);
                if ($stripped !== $v) {
                    $v = str_replace('<', '< ', $v);
                }
                return strip_tags($v);
            },
        );
    }
}
