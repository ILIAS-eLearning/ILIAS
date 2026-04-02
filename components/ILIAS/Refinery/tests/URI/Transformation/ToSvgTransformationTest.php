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
 */

declare(strict_types=1);

namespace ILIAS\Tests\Refinery\URI\Transformation;

use ILIAS\Refinery\URI\Transformation\ToSvgQrCodeTransformation;
use ILIAS\Data\QR\ErrorCorrectionLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ToSvgTransformationTest extends TestCase
{
    public function testConstructorWithZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $transformation = new ToSvgQrCodeTransformation(ErrorCorrectionLevel::LOW, 0);
    }

    public function testConstructorWithNegativeNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $transformation = new ToSvgQrCodeTransformation(ErrorCorrectionLevel::LOW, -1);
    }

    public function testConstructorWithPositiveNumber(): void
    {
        $this->expectNotToPerformAssertions();
        $transformation = new ToSvgQrCodeTransformation(ErrorCorrectionLevel::LOW, 1);
    }

    #[Depends('testConstructorWithPositiveNumber')]
    public function testTransformWithoutUriInstance(): void
    {
        $transformation = new ToSvgQrCodeTransformation(ErrorCorrectionLevel::LOW, 1);
        $this->expectException(\InvalidArgumentException::class);
        $transformation->transform('https://ilias.ch');
    }

    #[Depends('testConstructorWithPositiveNumber')]
    public function testTransformWithUriInstance(): void
    {
        $transformation = new ToSvgQrCodeTransformation(ErrorCorrectionLevel::LOW, 1);
        $this->expectNotToPerformAssertions();
        $transformation->transform($this->createUriMock());
    }

    /** @return ErrorCorrectionLevel */
    public static function getErrorCorrectionLevels(): array
    {
        return [
            [ErrorCorrectionLevel::LOW],
            [ErrorCorrectionLevel::MEDIUM],
            [ErrorCorrectionLevel::QUARTILE],
            [ErrorCorrectionLevel::HIGH],
        ];
    }

    #[Depends('testConstructorWithPositiveNumber')]
    #[DataProvider('getErrorCorrectionLevels')]
    public function testTransformWithErrorCorrectionLevels(ErrorCorrectionLevel $level): void
    {
        $transformation = new ToSvgQrCodeTransformation($level, 1);
        $this->expectNotToPerformAssertions();
        $code = $transformation->transform($this->createUriMock());
    }

    /** @return array<int[]> */
    public static function getSizesInPx(): array
    {
        return [
            [10],
            [100],
            [400],
            [1_000],
        ];
    }

    #[Depends('testConstructorWithPositiveNumber')]
    #[DataProvider('getSizesInPx')]
    public function testTransformWithSizes(int $size_in_px): void
    {
        $transformation = new ToSvgQrCodeTransformation(ErrorCorrectionLevel::LOW, $size_in_px);
        $this->expectNotToPerformAssertions();
        $code = $transformation->transform($this->createUriMock());
    }

    protected function createUriMock(): \ILIAS\Data\URI & MockObject
    {
        $uri_mock = $this->createMock(\ILIAS\Data\URI::class);
        $uri_mock->method('__toString')->willReturn('https://ilias.ch');
        return $uri_mock;
    }
}
