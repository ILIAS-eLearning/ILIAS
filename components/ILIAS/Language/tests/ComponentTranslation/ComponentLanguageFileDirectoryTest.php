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

namespace ILIAS\Language\ComponentTranslation;

use ILIAS\Component\Component;
use PHPUnit\Framework\TestCase;

/**
 * Non-regression guard for the base directory getPath() caches: repeated calls on an unchanged object return the same
 * value, and it is the one an uncached computation would produce. The caching itself is not observed.
 */
class ComponentLanguageFileDirectoryTest extends TestCase
{
    public function testGetPathReturnsTheSameValueOnRepeatedCallsAndMatchesTheUncachedComputation(): void
    {
        // a real (non-mock) class defined here, so ReflectionClass::getFileName() resolves to this file - deterministic
        // and computable below without ComponentLanguageFileDirectory's internals
        $component = new class () implements Component {
            public function init(
                array|\ArrayAccess &$define,
                array|\ArrayAccess &$implement,
                array|\ArrayAccess &$use,
                array|\ArrayAccess &$contribute,
                array|\ArrayAccess &$seek,
                array|\ArrayAccess &$provide,
                array|\ArrayAccess &$pull,
                array|\ArrayAccess &$internal,
            ): void {
            }
        };

        $directory = new ComponentLanguageFileDirectory($component, 'tst', 'lang/');

        // independently reproduces getPath()'s computation from __DIR__ of this file
        $ilias_root = (string) realpath(__DIR__ . '/../../../../../');
        self::assertNotSame('', $ilias_root, 'Could not resolve the ILIAS root from this test file - did it move?');
        $expected_base_directory = str_replace($ilias_root . '/', '', __DIR__);
        $expected_path = $expected_base_directory . '/lang/';

        $first_call = $directory->getPath();
        $second_call = $directory->getPath();
        $third_call = $directory->getPath();

        self::assertSame($expected_path, $first_call);
        self::assertSame($first_call, $second_call);
        self::assertSame($first_call, $third_call);
    }

    public function testGetPathAppendsTheConfiguredPathInsideComponentToTheBaseDirectory(): void
    {
        $component = new class () implements Component {
            public function init(
                array|\ArrayAccess &$define,
                array|\ArrayAccess &$implement,
                array|\ArrayAccess &$use,
                array|\ArrayAccess &$contribute,
                array|\ArrayAccess &$seek,
                array|\ArrayAccess &$provide,
                array|\ArrayAccess &$pull,
                array|\ArrayAccess &$internal,
            ): void {
            }
        };

        $directory = new ComponentLanguageFileDirectory($component, 'tst', 'custom/lang/path/');

        self::assertStringEndsWith('/custom/lang/path/', $directory->getPath());
    }
}
