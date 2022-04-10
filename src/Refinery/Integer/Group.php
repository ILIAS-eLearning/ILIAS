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

use ILIAS\Data\Factory;
use ILIAS\Refinery\Constraint;
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
     * Creates a constraint that can be used to check if an integer value is
     * greater than the defined lower limit.
     */
    public function isGreaterThan(int $minimum) : Constraint
    {
        return new GreaterThan($minimum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * less than the defined upper limit.
     */
    public function isLessThan(int $maximum) : Constraint
    {
        return new LessThan($maximum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * greater than or equal the defined lower limit.
     */
    public function isGreaterThanOrEqual(int $minimum) : Constraint
    {
        return new GreaterThanOrEqual($minimum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * less than or equal the defined upper limit.
     */
    public function isLessThanOrEqual(int $maximum) : Constraint
    {
        return new LessThanOrEqual($maximum, $this->dataFactory, $this->language);
    }
}
