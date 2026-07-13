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

namespace ILIAS\ResourceStorage\Manager;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ContainerManagerTest extends TestCase
{
    /**
     * Regression for Mantis 0045580 / 0047237: paths inside ZIP containers
     * must be stored as relative paths without a leading slash. Windows Explorer
     * and other tools hide entries whose names start with "/".
     */
    #[DataProvider('pathProvider')]
    public function testNormalizePathProducesRelativePath(string $input, string $expected): void
    {
        $manager = (new \ReflectionClass(ContainerManager::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(ContainerManager::class, 'normalizePath');

        $this->assertSame($expected, $method->invoke($manager, $input));
    }

    public static function pathProvider(): \Iterator
    {
        yield 'file at root, no prefix' => ['index.html', 'index.html'];
        yield 'file at root, leading slash' => ['/index.html', 'index.html'];
        yield 'file at root, dot-slash' => ['./index.html', 'index.html'];
        yield 'file in subdir, no prefix' => ['dir/file.html', 'dir/file.html'];
        yield 'file in subdir, leading slash' => ['/dir/file.html', 'dir/file.html'];
        yield 'nested file, leading slash' => ['/assets/css/style.css', 'assets/css/style.css'];
        yield 'nested file, no prefix' => ['assets/css/style.css', 'assets/css/style.css'];
        yield 'trailing slash preserved as empty' => ['/', ''];
        yield 'empty input' => ['', ''];
        yield 'directory with trailing slash' => ['dir/', 'dir'];
        yield 'directory with both slashes' => ['/dir/', 'dir'];
    }
}
