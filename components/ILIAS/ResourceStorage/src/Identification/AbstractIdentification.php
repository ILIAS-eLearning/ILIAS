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

namespace ILIAS\ResourceStorage\Identification;

use Serializable;

/**
 * Class AbstractIdentification
 *
 * @internal
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractIdentification implements Serializable
{
    private string $unique_id;

    final public function __construct(string $unique_id)
    {
        $this->unique_id = $unique_id;
    }


    final public function serialize(): string
    {
        return $this->unique_id;
    }


    final public function unserialize($serialized): void
    {
        $this->unique_id = $serialized;
    }

    /**
     * @return array{unique_id: string}
     */
    final public function __serialize(): array
    {
        return [
            'unique_id' => $this->unique_id
        ];
    }

    final public function __unserialize(array $data): void
    {
        $this->unique_id = $data['unique_id'];
    }


    final public function __toString(): string
    {
        return $this->serialize();
    }
}
