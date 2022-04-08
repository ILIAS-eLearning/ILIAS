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

use ILIAS\Data\Factory;
use ILIAS\Refinery\Custom\Constraint;
use ILIAS\Refinery\Constraint as ConstraintInterface;
use ilLanguage;

class Group
{
    private Factory $dataFactory;
    private ilLanguage $language;

    public function __construct(Factory $dataFactory, ilLanguage $language)
    {
        $this->dataFactory = $dataFactory;
        $this->language = $language;
    }

    /**
     * @param Constraint[] $other
     * @return ConstraintInterface
     */
    public function logicalOr(array $other) : ConstraintInterface
    {
        return new LogicalOr($other, $this->dataFactory, $this->language);
    }

    public function not(Constraint $constraint) : ConstraintInterface
    {
        return new Not($constraint, $this->dataFactory, $this->language);
    }

    /**
     * @param Constraint[] $constraints
     * @return ConstraintInterface
     */
    public function parallel(array $constraints) : ConstraintInterface
    {
        return new Parallel($constraints, $this->dataFactory, $this->language);
    }

    /**
     * @param Constraint[] $constraints
     * @return ConstraintInterface
     */
    public function sequential(array $constraints) : ConstraintInterface
    {
        return new Sequential($constraints, $this->dataFactory, $this->language);
    }
}
