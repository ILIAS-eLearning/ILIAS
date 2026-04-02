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

use ILIAS\Refinery\URI\Transformation\FromSvgTransformation;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FromSvgTransformationTest extends TestCase
{
    public function testTransformWithoutSvgInstance(): void
    {
        $transformation = new FromSvgTransformation();
        $this->expectException(\InvalidArgumentException::class);
        $transformation->transform('<svg></svg>');
    }

    public function testTransformWithSvgInstance(): void
    {
        $transformation = new FromSvgTransformation();
        $this->expectNotToPerformAssertions();
        $transformation->transform($this->createSvgMock());
    }

    #[Depends('testTransformWithSvgInstance')]
    public function testTransformResult(): void
    {
        $transformation = new FromSvgTransformation();
        $svg_mock = $this->createSvgMock();
        $result = $transformation->transform($svg_mock);
        $this->assertIsString($result);
        $this->assertStringStartsWith("data:image/svg+xml;base64,", $result); // ensure correct data uri format
        $this->assertStringEndsWith(base64_encode($svg_mock->__toString()), $result); // ensure base64 encoded value
    }

    protected function createSvgMock(): \ILIAS\Data\SVG & MockObject
    {
        $svg_mock = $this->createMock(\ILIAS\Data\SVG::class);
        $svg_mock->method('__toString')->willReturn('<svg></svg>');
        return $svg_mock;
    }
}
