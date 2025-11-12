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
use ILIAS\StaticURL\Shortlinks\Shortlink\Target\TypeData;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface Shortlink
{
    public function withId(string $id): Shortlink;

    public function getId(): ?string;

    public function withAlias(string $alias): Shortlink;

    public function getAlias(): string;
    public function getAliasForPresentation(string $prefix = ''): string;

    public function withTargetType(): Type;

    public function getTargetType(): Type;

    public function withTargetData(TypeData $data): Shortlink;

    public function getTargetData(): TypeData;

    public function withPosition(int $position): Shortlink;

    public function getPosition(): int;

    public function withActive(bool $active): Shortlink;

    public function isActive(): bool;
    public function increaseUsage(): Shortlink;
    public function getUsed(): int;

}
