<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Integer;

use ILIAS\Data\Factory;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class Group
{
    private Factory $dataFactory;
    private \ilLanguage $language;

    public function __construct(Factory $dataFactory, \ilLanguage $language)
    {
        $this->dataFactory = $dataFactory;
        $this->language = $language;
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * greater than the defined lower limit.
     *
     * @param int $minimum - lower limit for the new constraint
     * @return GreaterThan
     */
    public function isGreaterThan(int $minimum) : GreaterThan
    {
        return new GreaterThan($minimum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * less than the defined upper limit.
     *
     * @param int $maximum - upper limit for the new constraint
     * @return LessThan
     */
    public function isLessThan(int $maximum) : LessThan
    {
        return new LessThan($maximum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * greater than or equal the defined lower limit.
     *
     * @param int $minimum
     * @return GreaterThanOrEqual
     */
    public function isGreaterThanOrEqual(int $minimum) : GreaterThanOrEqual
    {
        return new GreaterThanOrEqual($minimum, $this->dataFactory, $this->language);
    }
}
