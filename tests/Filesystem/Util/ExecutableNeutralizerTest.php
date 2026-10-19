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

namespace ILIAS\Filesystem\Util;

use ILIAS\Filesystem\Util\Archive\ExecutableNeutralizer;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ExecutableNeutralizerTest extends TestCase
{
    public static function executableProvider(): \Iterator
    {
        yield 'php' => ['shell.php', 'shell.sec'];
        yield 'php5' => ['a.php5', 'a.sec'];
        yield 'phtml' => ['a.phtml', 'a.sec'];
        yield 'phar' => ['a.phar', 'a.sec'];
        yield 'pht' => ['a.pht', 'a.sec'];
        yield 'upper case' => ['A.PHP', 'A.sec'];
        yield 'htaccess' => ['.htaccess', '.sec'];
        yield 'trailing dot' => ['a.php.', 'a.sec'];
        yield 'trailing space' => ['a.php ', 'a.sec'];
        yield 'in a sub directory' => ['a/b/x.php', 'a/b/x.sec'];
    }

    /**
     * @dataProvider executableProvider
     */
    public function testExecutablesAreNeutralised(string $given, string $expected): void
    {
        $neutralizer = new ExecutableNeutralizer();

        $this->assertTrue($neutralizer->isExecutable($given));
        $this->assertSame($expected, $neutralizer->neutralize($given));
    }

    public static function harmlessProvider(): \Iterator
    {
        yield 'html' => ['index.html'];
        yield 'js' => ['app.js'];
        yield 'sec' => ['.sec'];
        yield 'lang' => ['ilias_de.lang'];
        yield 'inc' => ['config.inc'];
        yield 'php in the name only' => ['php.txt'];
        yield 'directory' => ['folder.php/'];
    }

    /**
     * @dataProvider harmlessProvider
     */
    public function testHarmlessPathsAreKept(string $given): void
    {
        $neutralizer = new ExecutableNeutralizer();

        $this->assertFalse($neutralizer->isExecutable($given));
        $this->assertSame($given, $neutralizer->neutralize($given));
    }
}
