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

namespace ILIAS\Cache\Container;

use ILIAS\Cache\Adaptor\Adaptor;
use ILIAS\Cache\Config;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ByTrying;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class VoidContainer implements Container
{
    public function __construct(
        private Request $request
    ) {
    }

    public function lock(float $seconds): void
    {
    }

    public function isLocked(): bool
    {
        return true;
    }

    public function has(string $key): bool
    {
        return false;
    }

    public function get(string $key, Transformation $transformation): string|int|array|bool|null
    {
        return null;
    }

    public function set(string $key, array|bool|int|string|null $value): void
    {
        // To have a proper InvalidArgumentException, we loop through the array and convert a TypeError to an InvalidArgumentException
        try {
            if (is_array($value)) {
                array_walk_recursive($value, function (&$item) use ($key): void {
                    $this->set($key, $item);
                });
            }
        } catch (\TypeError) {
            throw new \InvalidArgumentException(
                'Only strings, integers and arrays containing those values are allowed, ' . gettype($value) . ' given.'
            );
        }
    }

    public function delete(string $key): void
    {
    }

    public function flush(): void
    {
    }

    public function getAdaptorName(): string
    {
        return 'null';
    }

    public function getContainerName(): string
    {
        return $this->request->getContainerName();
    }
}
