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

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

abstract class ilBookingManagerBaseTestCase extends TestCase
{
    use ilBookingManagerTestTrait;

    private ?Container $previous_dic = null;

    protected function setUp(): void
    {
        global $DIC;

        $this->previous_dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();
        $GLOBALS['DIC'] = $DIC;

        $this->setGlobalVariable('lng', $this->createMock(ilLanguage::class));
        $this->setGlobalVariable('ilCtrl', $this->createMock(ilCtrl::class));

        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->previous_dic;
        $GLOBALS['DIC'] = $DIC;

        parent::tearDown();
    }
}
