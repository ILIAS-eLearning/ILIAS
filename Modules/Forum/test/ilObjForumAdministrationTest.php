<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ilObjForumAdministrationTest extends TestCase
{
    /**
     * @var MockObject|\ilLanguage
     */
    private $mockLanguage;

    public function testConstruct() : void
    {
        if (!defined('DEBUG')) {
            define('DEBUG', false);
        }

        $this->mockLanguage->expects(self::once())->method('loadLanguageModule')->with('forum');
        $instance = new \ilObjForumAdministration();
    }

    protected function setUp() : void
    {
        global $DIC;

        $DIC = new Container();

        $DIC['ilias'] = null; // not used just added received
        $DIC['ilDB'] = $this->getMockBuilder(\ilDBInterface::class)->getMock();
        $DIC['ilErr'] = null;
        $DIC['tree'] = $this->getMockBuilder(\ilTree::class)->disableOriginalConstructor()->getMock();
        $DIC['ilLog'] = null;
        $DIC['ilAppEventHandler'] = null;
        $DIC['objDefinition'] = null;
        $DIC['lng'] = ($this->mockLanguage = $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock());
    }
}
