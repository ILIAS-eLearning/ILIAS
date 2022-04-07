<?php declare(strict_types=1);

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

namespace ILIAS\Refinery\Integer;

use ILIAS\Refinery\Constraint;
use ILIAS\Data;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ilLanguage;

class GreaterThanOrEqual extends CustomConstraint implements Constraint
{
    protected int $min;

    public function __construct(int $min, Data\Factory $data_factory, ilLanguage $lng)
    {
        $this->min = $min;
        parent::__construct(
            function ($value) : bool {
                return $value >= $this->min;
            },
            function ($txt, $value) : string {
                return (string) $txt("not_greater_than_or_equal", $this->min);
            },
            $data_factory,
            $lng
        );
    }
}
