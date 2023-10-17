<?php

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

declare(strict_types=1);

namespace ILIAS\MetaData\Paths\Filters;

interface FilterInterface
{
    /**
     * The filter type specifies how elements are filtered:
     *  * **mdid:** Filters elements by their ID from the database tables.
     *  * **data:** Filters elements by the value of their data,
     *  * **index:** Filters elements by their index, starting with 0. Non-numeric values are
     *    interpreted as referring to the last index.
     */
    public function type(): FilterType;

    /**
     * When there are multiple values, an element passes the filter
     * if it would pass it for one of the values.
     * @return string[]
     */
    public function values(): \Generator;
}
