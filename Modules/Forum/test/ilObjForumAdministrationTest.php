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

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ilObjForumAdministrationTest extends TestCase
{
    /** @var MockObject&ilLanguage */
    private $mockLanguage;
    private ?Container $dic = null;

    public function testConstruct(): void
    {
        $this->mockLanguage->expects(self::once())->method('loadLanguageModule')->with('forum');
        new ilObjForumAdministration();
    }

    protected function setUp(): void
    {
        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        $DIC['ilias'] = null; // not used just added received
        $DIC['ilDB'] = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $DIC['ilErr'] = null;
        $DIC['tree'] = $this->getMockBuilder(ilTree::class)->disableOriginalConstructor()->getMock();
        $DIC['ilLog'] = null;
        $DIC['ilAppEventHandler'] = null;
        $DIC['objDefinition'] = null;
        $DIC['lng'] = ($this->mockLanguage = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock());
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }
}
