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

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Services;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * All three entry points must agree on when counting pages is possible,
 * see https://mantis.ilias.de/view.php?id=47827
 */
final class ilCountPDFPagesTest extends TestCase
{
    public static function provideAvailability(): array
    {
        return [
            'neither' => [false, false, false],
            'ghostscript only' => [true, false, true],
            'imagick only' => [false, true, true],
            'both' => [true, true, true],
        ];
    }

    #[DataProvider('provideAvailability')]
    public function testIsAvailable(bool $postscript, bool $imagick, bool $expected): void
    {
        $counter = $this->counterWith($postscript, $imagick, $this->createStub(Services::class));

        $this->assertSame($expected, $counter->isAvailable());
    }

    #[DataProvider('provideAvailability')]
    public function testRIDIsOnlyLookedUpWhenCountingIsAvailable(
        bool $postscript,
        bool $imagick,
        bool $expected
    ): void {
        $irss = $this->createMock(Services::class);
        $irss->expects($expected ? $this->once() : $this->never())
             ->method('manage')
             ->willThrowException(new LogicException('resource looked up'));

        $counter = $this->counterWith($postscript, $imagick, $irss);

        if ($expected) {
            $this->expectExceptionMessage('resource looked up');
        }

        $this->assertNull($counter->extractAmountOfPagesByRID(new ResourceIdentification('rid')));
    }

    public function testNothingIsCountedByPathWithoutAnyTool(): void
    {
        $counter = $this->counterWith(false, false, $this->createStub(Services::class));

        $this->assertNull($counter->extractAmountOfPagesByPath('/does/not/matter.pdf'));
    }

    private function counterWith(bool $postscript, bool $imagick, Services $irss): ilCountPDFPages
    {
        // the constructor reads PATH_TO_GHOSTSCRIPT and class_exists('Imagick'),
        // neither of which can be controlled in a test
        $reflection = new ReflectionClass(ilCountPDFPages::class);
        $counter = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('irss')->setValue($counter, $irss);
        $reflection->getProperty('postscript_available')->setValue($counter, $postscript);
        $reflection->getProperty('imagick_available')->setValue($counter, $imagick);

        return $counter;
    }
}
