<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Crawler;

use ILIAS\UI\Implementation\Crawler\Entry as Entry;

/**
 * Parses information from UI components. Source are comments in the factories in YAML format.
 */
interface YamlParser
{
    /**
     * Returns an array of all YAML entries as string of the components in the factories in a given file.
     * @return string[]
     */
    public function parseYamlStringArrayFromFile(string $filePath): array;

    /**
     * Returns an array of arrays of the parsed YAML entries in a given file.
     */
    public function parseArrayFromFile(string $filePath): array;

    /**
     * Returns an Entry\ComponentEntries of the parsed YAML entries in a given file.
     */
    public function parseEntriesFromFile(string $filePath): Entry\ComponentEntries;

    /**
     * Returns an array of all YAML entries as string of the components in the factories in a given string.
     */
    public function parseYamlStringArrayFromString(string $content): array;

    /**
     * Returns an array of arrays of the parsed YAML entries in a given string.
     */
    public function parseArrayFromString(string $content): array;

    /**
     * Returns a list UI Component Entries of the parsed YAML entries in a given string.
     */
    public function parseEntriesFromString(string $content): Entry\ComponentEntries;
}
