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

require_once("vendor/composer/vendor/autoload.php");

class PositiveIntegerTest extends TestCase
{
    /**
     * @throws ConstraintViolationException
     */
    public function testCreatePositiveInteger(): void
    {
        $integer = new PositiveInteger(6);
        $this->assertSame(6, $integer->getValue());
    }

    public function testNegativeIntegerThrowsException(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $integer = new PositiveInteger(-6);
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }

    /**
     * @throws ConstraintViolationException
     */
    public function testMaximumIntegerIsAccepted(): void
    {
        $integer = new PositiveInteger(PHP_INT_MAX);
        $this->assertSame(PHP_INT_MAX, $integer->getValue());
    }
}
