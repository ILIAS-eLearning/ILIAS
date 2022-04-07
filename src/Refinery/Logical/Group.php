<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Logical;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Custom\Constraint;
use ilLanguage;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class Group
{
    private Factory $dataFactory;
    private ilLanguage $language;

    public function __construct(Factory $dataFactory, ilLanguage $language)
    {
        $this->dataFactory = $dataFactory;
        $this->language = $language;
    }

    public function logicalOr(array $other) : LogicalOr
    {
        return new LogicalOr($other, $this->dataFactory, $this->language);
    }

    public function not(Constraint $constraint) : Not
    {
        return new Not($constraint, $this->dataFactory, $this->language);
    }

    public function parallel(array $constraints) : Parallel
    {
        return new Parallel($constraints, $this->dataFactory, $this->language);
    }

    public function sequential(array $constraints) : Sequential
    {
        return new Sequential($constraints, $this->dataFactory, $this->language);
    }
}
