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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;
use ILIAS\ResourceStorage\Services;

/**
 * Class assBaseTestCase
 */
abstract class assBaseTestCase extends TestCase
{
    use ilTestBaseTestCaseTrait;

    protected ?Container $dic = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        $this->addGlobal_tpl();
        $this->addGlobal_lng();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilHelp();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilTabs();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_rbacreview();
        $this->addGlobal_refinery();
        $this->addGlobal_ilDB();
        $this->addGlobal_tree();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_ilComponentFactory();
        $this->addGlobal_http();
        $this->addGlobal_upload();
        $this->addGlobal_ilCtrl();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    protected function getIRSSMock()
    {
        return $this->getMockBuilder(Services::class)->disableOriginalConstructor()->getMock();
    }

    protected function getFileDeliveryMock()
    {
        return $this->getMockBuilder(\ILIAS\FileDelivery\Services::class)->disableOriginalConstructor()->getMock();
    }
}
