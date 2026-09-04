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

namespace ILIAS\Data\Privacy\PHPStan\Type;

use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression test: PHPStan resolves PrivacyDataType<T>::resolve() to T
 * natively through the generics annotations — no dedicated return type
 * extension is required.
 */
class ResolveReturnTypeInferenceTest extends TypeInferenceTestCase
{
    public static function dataFileAsserts(): iterable
    {
        yield from self::gatherAssertTypes(__DIR__ . '/Fixtures/return-types.php');
    }

    #[DataProvider('dataFileAsserts')]
    public function testFileAsserts(string $assert_type, string $file, mixed ...$args): void
    {
        $this->assertFileAsserts($assert_type, $file, ...$args);
    }
}
