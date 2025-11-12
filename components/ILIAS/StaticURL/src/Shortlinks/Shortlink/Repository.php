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

namespace ILIAS\StaticURL\Shortlinks\Shortlink;

use ILIAS\StaticURL\Shortlinks\Shortlink\Target\Type;
use ILIAS\StaticURL\Shortlinks\Shortlink\Target\TypeDataResolver;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface Repository
{
    public const string VALID_ALIAS_PATTERN = "/^([A-Za-z0-9_-]+)\/?$/";

    public function hasId(string $id): bool;
    public function has(string $shortlink): bool;

    public function getByAlias(string $shortlink): ?Shortlink;
    public function getById(string $string): ?Shortlink;
    public function blank(Type $type = Type::REPO): Shortlink;

    public function store(Shortlink $shortlink): Shortlink;
    public function increaseUsage(Shortlink $shortlink): Shortlink;

    public function delete(Shortlink $shortlink): bool;
    public function getRange(int $start, int $limit): \Generator;
    public function getAll(): \Generator;

    public function count(): int;
    public function typeDataRevolver(): TypeDataResolver;

}
