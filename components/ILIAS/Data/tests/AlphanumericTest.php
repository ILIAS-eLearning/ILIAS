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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
namespace ILIAS\Data;

use ILIAS\Refinery\ConstraintViolationException;
use PHPUnit\Framework\TestCase;

require_once('./vendor/composer/vendor/autoload.php');

class AlphanumericTest extends TestCase
{
    public function testSimpleStringIsCorrectAlphanumericValueAndCanBeConvertedToString(): void
    {
        $value = new Alphanumeric('hello');

        $this->assertSame('hello', $value->asString());
    }

    public function testIntegerIsAlphanumericValueAndCanBeConvertedToString(): void
    {
        $value = new Alphanumeric(6);

        $this->assertSame('6', $value->asString());
    }

    public function testIntegerIsAlphanumericValue(): void
    {
        $value = new Alphanumeric(6);

        $this->assertSame(6, $value->getValue());
    }

    public function testFloatIsAlphanumericValueAndCanBeConvertedToString(): void
    {
        $value = new Alphanumeric(6.0);

        $this->assertSame('6', $value->asString());
    }

    public function testFloatIsAlphanumericValue(): void
    {
        $value = new Alphanumeric(6.0);

        $this->assertSame(6.0, $value->getValue());
    }

    public function testTextIsNotAlphanumericAndWillThrowException(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $value = new Alphanumeric('hello world');
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }
}
