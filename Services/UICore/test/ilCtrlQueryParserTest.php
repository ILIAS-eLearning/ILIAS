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

use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilCtrlQueryParserTest extends TestCase
{
    private ?ilCtrlQueryRegexParser $query_parser = null;

    protected function setUp(): void
    {
        $this->query_parser = new ilCtrlQueryRegexParser();
    }

    public function queryStringProvider(): array
    {
        return [
            [
                'ilias.php?',
                [],
            ],
            [
                'ilias.php?ref_id=123&parent_ref_id=456',
                [
                    'ref_id' => '123',
                    'parent_ref_id' => '456',
                ],
            ],
            [
                'ilias.php?param1=some%26value&param2=other_value',
                [
                    'param1' => 'some%26value',
                    'param2' => 'other_value',
                ]
            ],
            [
                'ilias.php?baseClass=ilwikihandlergui&cmdNode=161:r9:7n:kl&cmdClass=ilnotegui&ref_id=2731&page=Meine_%26_Seite',
                [
                    'ref_id' => '2731',
                    'baseClass' => 'ilwikihandlergui',
                    'cmdNode' => '161:r9:7n:kl',
                    'cmdClass' => 'ilnotegui',
                    'page' => 'Meine_%26_Seite',
                ]
            ],
            [
                'ilias.php?baseClass=ilwikihandlergui&cmdNode=161:r9:164&cmdClass=ilwikipagegui&cmd=whatLinksHere&ref_id=2731&page=Meine_%26_Seite',
                [
                    'ref_id' => '2731',
                    'baseClass' => 'ilwikihandlergui',
                    'cmdNode' => '161:r9:164',
                    'cmdClass' => 'ilwikipagegui',
                    'cmd' => 'whatLinksHere',
                    'page' => 'Meine_%26_Seite',
                ]
            ],
            [
                'ilias.php?baseClass=ilwikihandlergui&cmdNode=161:r9:164:7n:128&cmdClass=iltagginggui&ref_id=2731&page=Meine_%26_Seite&cmdMode=asynch',
                [
                    'ref_id' => '2731',
                    'baseClass' => 'ilwikihandlergui',
                    'cmdNode' => '161:r9:164:7n:128',
                    'cmdClass' => 'iltagginggui',
                    'page' => 'Meine_%26_Seite',
                    'cmdMode' => 'asynch',
                ]
            ],
        ];
    }

    /**
     * @dataProvider queryStringProvider
     */
    public function testQueryParser(string $query_string, array $expected_queries): void
    {
        $parsed_queries = $this->query_parser->parseQueriesOfURL($query_string);
        $this->assertEquals($expected_queries, $parsed_queries);
    }
}
