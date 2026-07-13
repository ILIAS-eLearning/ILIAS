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

namespace ILIAS\Tests\Refinery\URI;

use ILIAS\Refinery\URI\Group as URIGroup;
use ILIAS\Refinery\URI\Transformation\ToStringTransformation;
use ILIAS\Refinery\URI\Transformation\FromSvgTransformation;
use ILIAS\Refinery\URI\Transformation\ToSvgQrCodeTransformation;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    public function testToStringTransformationInstance(): void
    {
        $group = new URIGroup();
        $transformation = $group->toString();
        $this->assertInstanceOf(ToStringTransformation::class, $transformation);
    }

    public function testToSvgTransformationInstance(): void
    {
        $group = new URIGroup();
        $transformation = $group->toSvgQrCode();
        $this->assertInstanceOf(ToSvgQrCodeTransformation::class, $transformation);
    }

    public function testFromSvgTransformationInstance(): void
    {
        $group = new URIGroup();
        $transformation = $group->fromSvg();
        $this->assertInstanceOf(FromSvgTransformation::class, $transformation);
    }
}
