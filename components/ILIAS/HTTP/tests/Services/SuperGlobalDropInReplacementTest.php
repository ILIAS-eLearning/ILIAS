<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\HTTP;

/** @noRector */
require_once "AbstractBaseTest.php";

use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\Wrapper\SuperGlobalDropInReplacement;
use ILIAS\Refinery\Factory as Refinery;
use ilLanguage;
use OutOfBoundsException;

class SuperGlobalDropInReplacementTest extends AbstractBaseTest
{
    private function getRefinery(): Refinery
    {
        return new Refinery(
            new DataFactory(),
            $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock()
        );
    }

    public function testValueCanBeAssignedIfSuperGlobalIsMutable(): void
    {
        $super_global = new SuperGlobalDropInReplacement($this->getRefinery(), ['foo' => 'bar']);
        $super_global['foo'] = 'phpunit';

        self::assertEquals('phpunit', $super_global['foo']);
    }

    public function testExceptionIsRaisedIfValueIsAssignedButSuperGlobalIsImmutable(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $super_global = new SuperGlobalDropInReplacement($this->getRefinery(), ['foo' => 'bar'], true);
        $super_global['foo'] = 'phpunit';
    }
}
