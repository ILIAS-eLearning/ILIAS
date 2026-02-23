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

use PHPUnit\Framework\Attributes\DataProvider;
use ILIAS\StaticURL\Builder\StandardURIBuilder;
use ILIAS\Data\ReferenceId;
use ILIAS\StaticURL\Configuration;
use ILIAS\StaticURL\Config;

require_once "Base.php";

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class URIBuilderTest extends Base
{
    public static function getILIAS_HTTP_Paths(): \Iterator
    {
        yield ['https://ilias.de/ilias', 'https://ilias.de/ilias'];
        yield ['https://ilias.de/ilias/', 'https://ilias.de/ilias'];
        yield ['http://ilias.de/ilias', 'http://ilias.de/ilias'];
        yield ['https://test9.ilias.de/goto.php', 'https://test9.ilias.de'];
        yield ['https://test9.ilias.de/goto.php/', 'https://test9.ilias.de'];
        yield ['https://test9.ilias.de/goto.php/wiki', 'https://test9.ilias.de'];
        yield ['https://test9.ilias.de/goto.php/wiki/22', 'https://test9.ilias.de'];
        yield ['http://test9.ilias.de/goto.php', 'http://test9.ilias.de'];
        yield ['http://test9.ilias.de/go/hello', 'http://test9.ilias.de'];
        yield ['http://test9.ilias.de/go/hello', 'http://test9.ilias.de'];
        yield ['http://test9.ilias.de/Customizing/plugins/Services/index.php', 'http://test9.ilias.de'];
    }

    #[DataProvider('getILIAS_HTTP_Paths')]
    public function testBaseURI(string $ILIAS_HTTP_PATH, string $expected): void
    {
        $uri_builder = new StandardURIBuilder(
            $this->getConfig($ILIAS_HTTP_PATH)
        );
        $this->assertSame($expected, (string) $uri_builder->getBaseURI());
    }

    public static function getBuilderParts(): \Iterator
    {
        yield ['wiki', 42, [], 'https://test9.ilias.de/goto.php/wiki/42'];
        yield ['file', 42, ['download'], 'https://test9.ilias.de/goto.php/file/42/download'];
        yield ['dashboard', null, [], 'https://test9.ilias.de/goto.php/dashboard'];
    }

    #[DataProvider('getBuilderParts')]
    public function testFullBuilder(string $namespace, ?int $ref_id, array $params, string $expected): void
    {
        $uri_builder = new StandardURIBuilder(
            $this->getConfig('https://test9.ilias.de')
        );
        $uri = $uri_builder->build(
            $namespace,
            $ref_id === null ? null : new ReferenceId($ref_id),
            $params
        );
        $this->assertSame($expected, (string) $uri);
    }

    private function getConfig(
        string $ILIAS_HTTP_PATH
    ): Configuration {
        return new class ($ILIAS_HTTP_PATH) implements Configuration {
            public function __construct(
                private string $ILIAS_HTTP_PATH
            ) {
            }

            public function get(Config $config): mixed
            {
                return match ($config) {
                    Config::BASE_URL => $this->ILIAS_HTTP_PATH,
                    Config::REWRITE_POSSIBLE => false,
                    Config::SHORTLINK_NAMESPACE => 'shortlink',
                    Config::STATIC_LINK_ENDPOINT => StandardURIBuilder::LONG,
                    Config::ULTRA_SHORT => false,
                    default => null,
                };
            }

        };
    }

}
