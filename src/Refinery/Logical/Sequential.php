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

class Sequential extends Constraint
{
    /**
     * @var Constraint[]
     */
    protected array $constraints;

    /**
     * There's a test to show this state will never be visible
     * SequentialTest::testCorrectErrorMessagesAfterMultiAccept
     *
     * @var Constraint
     */
    protected Constraint $failed_constraint;

    public function __construct(array $constraints, Data\Factory $data_factory, ilLanguage $lng)
    {
        $this->constraints = $constraints;
        parent::__construct(
            function ($value) {
                foreach ($this->constraints as $constraint) {
                    if (!$constraint->accepts($value)) {
                        $this->failed_constraint = $constraint;
                        return false;
                    }
                }

                return true;
            },
            function ($txt, $value) {
                return $this->failed_constraint->getErrorMessage($value);
            },
            $data_factory,
            $lng
        );
    }
}
