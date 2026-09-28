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

namespace ILIAS\Logging\Config\ByComponent;

use ILIAS\Logging\ILIASLogLevel;

interface RepositoryInterface
{
    public function addComponent(string $component_id): void;

    /**
     * @return array<string, ILIASLogLevel>
     */
    public function getAllLevelsForComponents(): array;

    public function getLevelForComponent(string $component_id): ?ILIASLogLevel;

    public function updateLevelForComponent(string $component_id, ILIASLogLevel $level): void;

    public function resetLevelForComponent(string $component_id): void;

    public function resetLevelsForAllComponents(): void;
}
