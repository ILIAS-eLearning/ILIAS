<?php

declare(strict_types=1);

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

namespace ILIAS\ContentPage;

use ILIAS\ContentPage\PageMetrics\ValueObject\PageReadingTime;
use PHPUnit\Framework\TestCase;
use TypeError;
use stdClass;

/**
 * Class PageReadingTimeTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class PageReadingTimeTest extends TestCase
{
    public function mixedReadingTypesProvider(): array
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

    /**
     * @param mixed $mixedType
     * @dataProvider mixedReadingTypesProvider
     */
    public function testPageReadingTimeValueThrowsExceptionWhenConstructedWithInvalidTypes($mixedType): void
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
