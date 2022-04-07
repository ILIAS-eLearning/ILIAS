<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Numeric;

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

    public function isNumeric() : IsNumeric
    {
        return new IsNumeric($this->dataFactory, $this->language);
    }
}
