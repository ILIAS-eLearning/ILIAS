<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler;

use ILIAS\UI\Implementation\Crawler\Entry as Entry;

/**
 * Parses information from UI components. Source are comments in the factories in YAML format.
 */
interface YamlParser
{
    /**
     * Returns an array of all YAML entries as string of the components in the factories in a given file.
     * @param string $filePath
     * @return string[]
     */
    public function parseYamlStringArrayFromFile($filePath);

    /**
     * Returns an array of arrays of the parsed YAML entries in a given file.
     * @param string $filePath
     * @return []
     */
    public function parseArrayFromFile($filePath);

    /**
     * Returns a Entry\ComponentEntries of the parsed YAML entries in a given file.
     * @param string $filePath
     * @return Entry\ComponentEntries
     */
    public function parseEntriesFromFile($filePath);

    /**
     * Returns an array of all YAML entries as string of the components in the factories in a given string.
     * @param string $content
     * @return string
     */
    public function parseYamlStringArrayFromString($content);

    /**
     * Returns an array of arrays of the parsed YAML entries in a given string.
     * @param string $content
     * @return []
     */
    public function parseArrayFromString($content);

    /**
     * Returns a list UI Component Entries of the parsed YAML entries in a given string.
     * @param string $content
     * @return Entry\ComponentEntries
     */
    public function parseEntriesFromString($content);
}
