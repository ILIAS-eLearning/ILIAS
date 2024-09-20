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

namespace ILIAS;

class Session implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        // currently this is will be a session storage because we cannot store
        // data on the client, see https://mantis.ilias.de/view.php?id=38503.
        // @todo: please implement this inside a proper service.
        $implement[UI\Component\Table\Storage::class] = static fn() =>
            new class () implements UI\Component\Table\Storage {
                public function offsetExists(mixed $offset): bool
                {
                    return \ilSession::has($offset);
                }
                public function offsetGet(mixed $offset): mixed
                {
                    return \ilSession::get($offset);
                }
                public function offsetSet(mixed $offset, mixed $value): void
                {
                    if (!is_string($offset)) {
                        throw new \InvalidArgumentException('Offset needs to be of type string.');
                    }
                    \ilSession::set($offset, $value);
                }
                public function offsetUnset(mixed $offset): void
                {
                    \ilSession::clear($offset);
                }
            };

        $contribute[\ILIAS\Setup\Agent::class] = static fn() =>
            new \ilSessionSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class]
            );
    }
}
