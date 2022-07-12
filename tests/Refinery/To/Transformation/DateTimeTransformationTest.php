<?php declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\To\Transformation;

use DateTimeImmutable;
use ILIAS\Refinery\To\Transformation\DateTimeTransformation;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class DateTimeTransformationTest extends TestCase
{
    private DateTimeTransformation $trans;

    protected function setUp() : void
    {
        $this->trans = new DateTimeTransformation();
    }

    public function testTransform() : void
    {
        $value = '26.05.1977';
        $expected = new DateTimeImmutable($value);

        $this->assertEquals(
            $expected,
            $this->trans->transform($value)
        );
    }

    public function testInvalidTransform() : void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->trans->transform('erroneous');
    }

    public function testInvoke() : void
    {
        $value = '2019/05/26';
        $expected = new DateTimeImmutable($value);
        $t = $this->trans;

        $this->assertEquals($expected, $t($value));
    }
}
