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

namespace ILIAS\Environment\Configuration\Ini;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface ConfigurationReadRepository
{
    /**
     * Returns all section names.
     *
     * @return list<string>
     */
    public function getSections(): array;

    public function hasSection(string $section): bool;

    /**
     * Returns all key-value pairs within a section.
     *
     * @return array<string, string>
     * @throws \InvalidArgumentException if section does not exist
     */
    public function getSection(string $section): array;

    /**
     * @throws \InvalidArgumentException if section or key does not exist
     */
    public function get(string $section, string $key): string;

    public function has(string $section, string $key): bool;
}
