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

use ILIAS\UI\Implementation\Crawler as Crawler;
use PHPUnit\Framework\TestCase;

class ExamplesYamlParserTest extends TestCase
{
    protected Crawler\EntriesYamlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new class () extends Crawler\ExamplesYamlParser {
            public function _getYamlEntriesFromString(string $content): array
            {
                return $this->getYamlEntriesFromString($content);
            }
        };
    }

    public function testProperEntry(): void
    {
        $doc = <<<EOT
        some other code here

        /**
         * ---
         * description: >
         *   Example showing...
         *
         * expected output: >
         *   ILIAS shows the expected output.
         *   At least, it should.
         * ---
         */

        function something()
        {
        }
EOT;
        $expected = [
            'description' => "\nExample showing...",
            'expected output' => "\nILIAS shows the expected output.\nAt least, it should."
        ];

        $this->assertEquals(
            $expected,
            $this->parser->_getYamlEntriesFromString($doc)
        );

    }

    public function testMissingBoundaries(): void
    {
        $doc = <<<EOT
        /**
         * description:
         *   Example showing...
         * ---
         */
EOT;
        $this->assertEquals([], $this->parser->_getYamlEntriesFromString($doc));
    }


}
