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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PermanentLinkGUITest extends TestCase
{
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function setUp() : void
    {
        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", false);
        }

        parent::setUp();

        $db_mock = $this->createMock(ilDBInterface::class);
        $db_mock->method("fetchAssoc")
                ->will(
                    $this->onConsecutiveCalls(
                        [
                            "component" => "Services/EventHandling",
                            "id" => "MyTestComponent"
                        ],
                        null
                    )
                );


        $this->setGlobalVariable(
            "ilDB",
            $db_mock
        );
        $this->setGlobalVariable(
            "ilSetting",
            $this->createMock(ilSetting::class)
        );
        $component_repository = $this->createMock(ilComponentRepository::class);
        $this->setGlobalVariable(
            "component.repository",
            $component_repository
        );
        $component_factory = $this->createMock(ilComponentFactory::class);
        $this->setGlobalVariable(
            "component.factory",
            $component_factory
        );

        $languageMock = $this->getMockBuilder(ilLanguage::class)
                             ->disableOriginalConstructor()
                             ->getMock();
        $this->setGlobalVariable(
            "lng",
            $languageMock
        );

        $ctrl = $this->getMockBuilder('ilCtrl')->disableOriginalConstructor()->onlyMethods(
            ['setParameterByClass', 'redirectByClass', 'forwardCommand']
        )->getMock();
        $ctrl->method('setParameterByClass');
        $ctrl->method('redirectByClass');

        $this->setGlobalVariable('ilCtrl', $ctrl);

        $objectDataCache = $this
            ->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setGlobalVariable('ilObjDataCache', $objectDataCache);
    }

    protected function tearDown() : void
    {
    }

    public function testProperties() : void
    {
        $pm = new ilPermanentLinkGUI(
            "wiki",
            55
        );
        $pm->setAppend("append");
        $this->assertEquals(
            "append",
            $pm->getAppend()
        );
        $pm->setId(66);
        $this->assertEquals(
            66,
            $pm->getId()
        );
        $pm->setTitle("title");
        $this->assertEquals(
            "title",
            $pm->getTitle()
        );
    }
}
