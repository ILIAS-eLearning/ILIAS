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

namespace ILIAS\ResourceStorage\Events;

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 */
class CollectionData extends Data
{
    public function __construct(array $data = [])
    {
        // check fopr array to have two keys: rcid and rid
        if (!array_key_exists('rcid', $data) || !array_key_exists('rid', $data)) {
            throw new \InvalidArgumentException('CollectionData must contain rcid and rid');
        }

        parent::__construct($data, \ArrayObject::ARRAY_AS_PROPS);
    }

    public function getRcid(): string
    {
        return $this['rcid'];
    }

    public function getRid(): string
    {
        return $this['rid'];
    }
}
