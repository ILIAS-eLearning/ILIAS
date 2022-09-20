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

namespace ILIAS\Tests\Refinery;

use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\Refinery\IdentityTransformation;
use PHPUnit\Framework\TestCase;

class IdentityTransformationTest extends TestCase
{
    public function testTransform(): void
    {
        $value = 'hejaaa';

        $actual = (new IdentityTransformation())->transform($value);

        $this->assertEquals($value, $actual);
    }

    public function testApplyToOk(): void
    {
        $value = ['im in an array'];
        $result = (new IdentityTransformation())->applyTo(new Ok($value));
        $this->assertInstanceOf(Ok::class, $result);
        $this->assertEquals($value, $result->value());
    }

    public function testApplyToError(): void
    {
        $error = new Error('some error');
        $result = (new IdentityTransformation())->applyTo($error);
        $this->assertEquals($error, $result);
    }
}
