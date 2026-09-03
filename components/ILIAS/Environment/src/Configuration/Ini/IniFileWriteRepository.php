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
 * Read-write access to an INI file. Extends {@see IniFileReadRepository} with
 * mutation and persistence.
 *
 * The format is kept intact across a write cycle by two complementary steps:
 *  - {@see set()} validates fail-fast and rejects characters that cannot be
 *    represented in a single INI line (NUL, CR, LF) as well as structurally
 *    invalid keys/sections.
 *  - {@see persist()} escapes the remaining special characters (backslash and
 *    double quote) so that every value round-trips losslessly through the
 *    NORMAL ini scanner used by {@see IniFileReadRepository::load()}.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class IniFileWriteRepository extends IniFileReadRepository implements ConfigurationWriteRepository
{
    public function addSection(string $section): void
    {
        $this->assertValidSection($section);
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
        $this->assertValidSection($section);
        $this->assertValidKey($key);
        $this->assertValidValue($value);
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
                    fwrite($fp, $key . ' = "' . $this->escapeValue($value) . "\"\r\n");
                }
            }
        } finally {
            fclose($fp);
        }
    }

    /**
     * Escapes a value for a double-quoted INI entry. {@see load()} reads with
     * the NORMAL ini scanner, under which "\\" decodes back to "\" and "\""
     * to a literal double quote. Escaping these two characters (backslash
     * first, so the escapes we add for quotes are not doubled) therefore makes
     * the value survive a write/read round-trip unchanged.
     */
    private function escapeValue(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }

    private function assertValidValue(string $value): void
    {
        if (preg_match('/[\x00\r\n]/', $value) === 1) {
            throw new \InvalidArgumentException(
                'INI values must not contain NUL, CR or LF characters.'
            );
        }
    }

    private function assertValidKey(string $key): void
    {
        if ($key === '' || preg_match('/[\x00\r\n=\[\]]/', $key) === 1) {
            throw new \InvalidArgumentException(
                "Invalid INI key '$key': keys must be non-empty and must not contain '=', '[', ']' or line breaks."
            );
        }
    }

    private function assertValidSection(string $section): void
    {
        if ($section === '' || preg_match('/[\x00\r\n\[\]]/', $section) === 1) {
            throw new \InvalidArgumentException(
                "Invalid INI section '$section': sections must be non-empty and must not contain '[', ']' or line breaks."
            );
        }
    }
}
