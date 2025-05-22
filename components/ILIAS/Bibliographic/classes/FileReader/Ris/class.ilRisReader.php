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

/**
 * This class has been adapted from the original RISReader class in the abandoned technosophos/LibRIS library.
 * Many thanks to technosophos for the original work!
 * @see https://github.com/technosophos/LibRIS/blob/master/src/LibRIS/RISReader.php
 */
class ilRisReader
{
    public const RIS_EOL = "\r\n";
    public const LINE_REGEX = '/^(([A-Z1-9]{2})\s+-(.*))|(.*)$/';

    protected array $data = [];

    public function __construct($options = [])
    {
    }

    /**
     * Parse a string of RIS data.
     *
     * This will parse an RIS record into a representative data structure.
     *
     * @param string        $string
     *  RIS-formatted data in a string.
     * @param StreamContext $context
     *  The stream context (in desired) for handling the file.
     * @retval array
     *  An indexed array of individual sources, each of which is an
     *  associative array of entry details. (See {@link LibRIS})
     */
    public function parseString(string $string): void
    {
        $contents = explode(self::RIS_EOL, $string);
        $this->parseArray($contents);
    }

    /**
     * Take an array of lines and parse them into an RIS record.
     */
    protected function parseArray(array $lines): void
    {
        $recordset = [];

        // Do any cleaning and normalizing.
        $this->cleanData($lines);

        $record = [];
        $lastTag = null;
        foreach ($lines as $line) {
            $line = trim((string) $line);
            $matches = [];

            preg_match(self::LINE_REGEX, $line, $matches);
            if (!empty($matches[3])) {
                $lastTag = $matches[2];
                $record[$matches[2]][] = trim($matches[3]);
            } // End record and prep a new one.
            elseif (!empty($matches[2]) && $matches[2] === 'ER') {
                $lastTag = null;
                $recordset[] = $record;
                $record = [];
            } elseif (!empty($matches[4])) {
                // Append to the last one.
                // We skip leading info (like BOMs).
                if (!empty($lastTag)) {
                    $lastEntry = count($record[$lastTag]) - 1;
                    // We trim because some encoders add tabs or multiple spaces.
                    // Standard is silent on how this should be handled.
                    $record[$lastTag][$lastEntry] .= ' ' . trim($matches[4]);
                }
            }
        }
        if ($record !== []) {
            $recordset[] = $record;
        }

        $this->data = $recordset;
    }

    public function getRecords(): array
    {
        return $this->data;
    }

    /**
     * Clean up the data before processing.
     *
     * @param array $lines
     *   Indexed array of lines of data.
     */
    protected function cleanData(array &$lines): void
    {
        if ($lines === []) {
            return;
        }

        // Currently, we only need to strip a BOM if it exists.
        // Thanks to Derik Badman (http://madinkbeard.com/) for finding the
        // bug and suggesting this fix:
        // http://blog.philipp-michels.de/?p=32
        $first = $lines[0];
        if (substr((string) $first, 0, 3) === pack('CCC', 0xef, 0xbb, 0xbf)) {
            $lines[0] = substr((string) $first, 3);
        }
    }

}
