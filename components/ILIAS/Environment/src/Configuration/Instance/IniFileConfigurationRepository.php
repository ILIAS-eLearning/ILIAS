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
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class IniFileConfigurationRepository implements ConfigurationReadRepository, ConfigurationWriteRepository
{
    /** @var array<string, array<string, string>> */
    private array $data = [];

    public function __construct(private readonly string $path)
    {
        if (is_file($path)) {
            $this->load();
        }
    }

    // — ConfigurationReadRepository —

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

    // — ConfigurationWriteRepository —

    public function addSection(string $section): void
    {
        if ($this->hasSection($section)) {
            throw new \InvalidArgumentException("Section '$section' already exists.");
        }
        $this->data[$section] = [];
    }

    public function removeSection(string $section): void
    {
        if (!$this->hasSection($section)) {
            throw new \InvalidArgumentException("Section '$section' does not exist.");
        }
        unset($this->data[$section]);
    }

    public function set(string $section, string $key, string $value): void
    {
        if (!$this->hasSection($section)) {
            $this->data[$section] = [];
        }
        $this->data[$section][$key] = $value;
    }

    public function remove(string $section, string $key): void
    {
        if (!$this->has($section, $key)) {
            throw new \InvalidArgumentException("Key '$key' does not exist in section '$section'.");
        }
        unset($this->data[$section][$key]);
    }

    public function persist(): void
    {
        $fp = fopen($this->path, 'wb');
        if ($fp === false) {
            throw new \RuntimeException("Cannot open '$this->path' for writing.");
        }

        try {
            fwrite($fp, "; <?php exit; ?>\r\n");
            $first = true;
            foreach ($this->data as $section => $values) {
                fwrite($fp, ($first ? '' : "\r\n") . "[$section]\r\n");
                $first = false;
                foreach ($values as $key => $value) {
                    fwrite($fp, "$key = \"$value\"\r\n");
                }
            }
        } finally {
            fclose($fp);
        }
    }

    // — Internal —

    private function load(): void
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
