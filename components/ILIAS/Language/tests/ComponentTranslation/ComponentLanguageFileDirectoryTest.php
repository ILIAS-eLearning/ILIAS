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
 * getPath() used to recompute its ReflectionClass/realpath lookup on every
 * call even though the result only ever depends on $this->component, which
 * never changes for the object's lifetime. It is now cached in
 * $this->base_directory on first access. This test does not attempt to
 * observe the caching mechanism itself (e.g. by counting Reflection calls) -
 * per the task, the point is a non-regression guard: for an unchanged
 * object, repeated getPath() calls must keep returning the exact same
 * value, and that value must still be the one the original (uncached)
 * computation would have produced.
 */
class ComponentLanguageFileDirectoryTest extends TestCase
{
    public function testGetPathReturnsTheSameValueOnRepeatedCallsAndMatchesTheUncachedComputation(): void
    {
        // A real (non-mock) class defined right here, so
        // ReflectionClass::getFileName() resolves to *this* test file's real
        // path on disk - deterministic, and independently computable below
        // without reusing ComponentLanguageFileDirectory's own internals.
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

        // Independently reproduce what getPath() is documented to compute,
        // from __DIR__ of this test file rather than from the production
        // class's internals.
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
