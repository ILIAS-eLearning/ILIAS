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

namespace ILIAS\Data;

use ilObject2;

/**
 * Class ReferenceId
 *
 * @package ILIAS\Data
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ReferenceId
{
    private int $ref_id;

    public function __construct(int $ref_id)
    {
        $this->ref_id = $ref_id;
    }

    public function toInt(): int
    {
        return $this->ref_id;
    }

    public function toObjectId(): ObjectId
    {
        return new ObjectId(ilObject2::_lookupObjectId($this->ref_id));
    }
}
