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

namespace ILIAS\UI\Implementation\Crawler;

use Symfony\Component\Yaml;
use ILIAS\UI\Implementation\Crawler\Entry as Entry;

class ExamplesYamlParser extends EntriesYamlParser
{
    public const PARSER_STATE_END = 5;

    protected function getYamlEntriesFromString(string $content): array
    {
        $parser_state = self::PARSER_STATE_OUTSIDE;
        $entry = "";

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $content) as $line) {

            if ($parser_state === self::PARSER_STATE_OUTSIDE) {
                if (preg_match('/---/', $line)) {
                    $entry = "";
                    $parser_state = self::PARSER_STATE_ENTRY;
                }

            } elseif ($parser_state === self::PARSER_STATE_ENTRY) {
                if (!preg_match('/(\*$)|(---)/', $line)) {
                    $entry .= $this->purifyYamlLine($line) . "\n";
                }
                if (preg_match('/---/', $line)) {
                    $parser_state = self::PARSER_STATE_END;
                }
            }
        }
        if ($parser_state !== self::PARSER_STATE_END) {
            return [];
        }

        $entry = $this->getPHPArrayFromYamlArray([$entry]);
        return array_shift($entry);
    }
}
