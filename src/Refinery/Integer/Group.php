<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Integer;

use ILIAS\Data\Factory;
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

    /**
     * Creates a constraint that can be used to check if an integer value is
     * greater than the defined lower limit.
     */
    public function isGreaterThan(int $minimum) : GreaterThan
    {
        return new GreaterThan($minimum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * less than the defined upper limit.
     */
    public function isLessThan(int $maximum) : LessThan
    {
        return new LessThan($maximum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * greater than or equal the defined lower limit.
     */
    public function isGreaterThanOrEqual(int $minimum) : GreaterThanOrEqual
    {
        return new GreaterThanOrEqual($minimum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * less than or equal the defined upper limit.
     */
    public function isLessThanOrEqual(int $maximum) : LessThanOrEqual
    {
        return new LessThanOrEqual($maximum, $this->dataFactory, $this->language);
    }
}
