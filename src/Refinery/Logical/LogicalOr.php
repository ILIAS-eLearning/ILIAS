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

namespace ILIAS\Refinery\Logical;

use ILIAS\Refinery\Custom\Constraint;
use ILIAS\Data;
use ilLanguage;

/**
 * Class LogicalOr
 * @package ILIAS\Refinery\Validation\Constraints
 * @author  Michael Jansen <mjansen@databay.de>
 */
class LogicalOr extends Constraint
{
    /**
     * @var Constraint[]
     */
    protected array $other = [];

    /**
     * LogicalOr constructor.
     * @param Constraint[] $other
     * @param Data\Factory $data_factory
     * @param ilLanguage $lng
     */
    public function __construct(array $other, Data\Factory $data_factory, ilLanguage $lng)
    {
        $this->other = $other;

        parent::__construct(
            function ($value) {
                foreach ($this->other as $constraint) {
                    if ($constraint->accepts($value)) {
                        return true;
                    }
                }

                return false;
            },
            function ($value) {
                $problems = [];

                foreach ($this->other as $constraint) {
                    $problems[] = $constraint->getErrorMessage($value);
                }

                return 'Please fix one of these: ' . implode(', ', array_filter($problems));
            },
            $data_factory,
            $lng
        );
    }
}
