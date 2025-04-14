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

namespace ILIAS\ContentPage;

use ILIAS\ContentPage\PageMetrics\ValueObject\PageReadingTime;
use PHPUnit\Framework\TestCase;
use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\DataProvider;

class PageReadingTimeTest extends TestCase
{
    public static function mixedReadingTypesProvider(): array
    {
        return [
            'Float Type' => [4.0],
            'String Type' => ['4'],
            'Array Type' => [[4]],
            'Object Type' => [new stdClass()],
            'Boolean Type' => [false],
            'Null Type' => [null],
            'Ressource Type' => [fopen('php://temp', 'rb')]
        ];
    }

    #[DataProvider('mixedReadingTypesProvider')]
    public function testPageReadingTimeValueThrowsExceptionWhenConstructedWithInvalidTypes(mixed $mixedType): void
    {
        $this->expectException(TypeError::class);

        $readingTime = new PageReadingTime($mixedType);
    }

    public function testRawReadingTimeCanBeRetrievedFromValueObject(): void
    {
        $readingTime = new PageReadingTime(5);
        $this->assertSame(5, $readingTime->minutes());
    }
}
