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

namespace ILIAS\LegalDocuments;

use Closure;

class Internal
{
    /** @var array<string, list|array<string, mixed>> */
    private readonly array $map;

    /**
     * @param Closure(string): Provide $create_provide
     * @param Closure(string): Wiring $create_wiring
     * @param null|list<class-string> $consumer_classes
     */
    public function __construct(Closure $create_provide, Closure $create_wiring, ?array $consumer_classes = null)
    {
        $lens = fn($consumer) => $consumer->uses($create_wiring($consumer->id()), new LazyProvide(fn() => $create_provide($consumer->id())));
        $this->map = array_reduce(
            $consumer_classes ?? require self::path(),
            fn($map, $consumer) => $map->append($lens(new $consumer())->map()),
            new Map()
        )->value();
    }

    public function all(string $name): array
    {
        return $this->map[$name] ?? [];
    }

    public function get(string $name, string $key)
    {
        return $this->map[$name][$key] ?? null;
    }

    public static function path(): string
    {
        return '../components/ILIAS/LegalDocuments/artifacts/consumers.php';
    }
}
