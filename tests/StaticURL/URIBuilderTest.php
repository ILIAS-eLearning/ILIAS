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

namespace ILIAS\StaticURL\Tests;

use ILIAS\StaticURL\Builder\StandardURIBuilder;
use ILIAS\Data\ReferenceId;

require_once "Base.php";

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class URIBuilderTest extends Base
{
    private ?string $ilias_http_path_backup = null;
    private StandardURIBuilder $uri_builder;

    public function getILIAS_HTTP_Paths(): array
    {
        return [
            ['https://ilias.de/ilias', 'https://ilias.de/ilias'],
            ['https://ilias.de/ilias/', 'https://ilias.de/ilias'],
            ['http://ilias.de/ilias', 'http://ilias.de/ilias'],
            ['https://test9.ilias.de/goto.php', 'https://test9.ilias.de'],
            ['https://test9.ilias.de/goto.php/', 'https://test9.ilias.de'],
            ['https://test9.ilias.de/goto.php/wiki', 'https://test9.ilias.de'],
            ['https://test9.ilias.de/goto.php/wiki/22', 'https://test9.ilias.de'],
            ['http://test9.ilias.de/goto.php', 'http://test9.ilias.de'],
            ['http://test9.ilias.de/go/hello', 'http://test9.ilias.de'],
            ['http://test9.ilias.de/go/hello', 'http://test9.ilias.de'],
            ['http://test9.ilias.de/Customizing/global/plugins/Services/index.php', 'http://test9.ilias.de'],
        ];
    }

    /**
     * @dataProvider getILIAS_HTTP_Paths
     */
    public function testBaseURI(string $ILIAS_HTTP_PATH, string $expected): void
    {
        $uri_builder = new StandardURIBuilder($ILIAS_HTTP_PATH);
        $this->assertEquals($expected, (string) $uri_builder->getBaseURI());
    }

    public function getBuilderParts(): array
    {
        return [
            ['wiki', 42, [], 'https://test9.ilias.de/goto.php/wiki/42'],
            ['file', 42, ['download'], 'https://test9.ilias.de/goto.php/file/42/download'],
            ['dashboard', null, [], 'https://test9.ilias.de/goto.php/dashboard'],

        ];
    }

    /**
     * @dataProvider getBuilderParts
     */
    public function testFullBuilder(string $namespace, ?int $ref_id, array $params, string $expected): void
    {
        $uri_builder = new StandardURIBuilder('https://test9.ilias.de');
        $uri = $uri_builder->build(
            $namespace,
            $ref_id === null ? null : new ReferenceId($ref_id),
            $params
        );
        $this->assertEquals($expected, (string) $uri);
    }
}
