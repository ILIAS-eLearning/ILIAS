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

namespace ILIAS\Environment\Configuration\Instance;

/**
 * Read-only access to an INI file. The file is parsed once on construction and
 * all lookups operate on the resulting in-memory representation.
 *
 * Mutation and persistence are intentionally kept out of this class; they live
 * in {@see IniFileWriteRepository}, so a consumer that only needs to read
 * configuration can depend on a type that cannot change it.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class IniFileReadRepository implements ConfigurationReadRepository
{
    /** @var array<string, array<string, string>> */
    protected array $data = [];

    public function __construct(protected readonly string $path)
    {
        if (is_file($path)) {
            $this->load();
        }
    }

    public function getSections(): array
    {
        return array_keys($this->data);
    }

    public function hasSection(string $section): bool
    {
        return isset($this->data[$section]);
    }

    public function getSection(string $section): array
    {
        if (!$this->hasSection($section)) {
            throw new \InvalidArgumentException("Section '$section' does not exist.");
        }
        return $this->data[$section];
    }

    public function get(string $section, string $key): string
    {
        if (!$this->has($section, $key)) {
            throw new \InvalidArgumentException("Key '$key' does not exist in section '$section'.");
        }
        return $this->data[$section][$key];
    }

    public function has(string $section, string $key): bool
    {
        return isset($this->data[$section][$key]);
    }

    protected function load(): void
    {
        $parsed = parse_ini_file($this->path, process_sections: true);
        if ($parsed === false) {
            throw new \RuntimeException("Cannot parse ini file '$this->path'.");
        }
        foreach ($parsed as $section => $values) {
            $this->data[$section] = array_map(strval(...), (array) $values);
        }
    }
}
