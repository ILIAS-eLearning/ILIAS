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

namespace ILIAS\Refinery\Custom;

use ILIAS\Data\Factory;
use ilLanguage;
use ILIAS\Refinery\Constraint as ConstraintInterface;
use ILIAS\Refinery\Transformation as TransformationInterface;

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
     * @param callable $callable
     * @param string|callable $error
     * @return ConstraintInterface
     */
    public function constraint(callable $callable, $error) : ConstraintInterface
    {
        return new Constraint(
            $callable,
            $error,
            $this->dataFactory,
            $this->language
        );
    }

    public function transformation(callable $transform) : TransformationInterface
    {
        return new Transformation($transform);
    }
}
