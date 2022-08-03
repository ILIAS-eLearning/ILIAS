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

/**
 * Class ilTestVerificationTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestVerificationTableGUITest extends ilTestBaseTestCase
{
    private ilTestVerificationTableGUI $tableGui;

    protected function setUp() : void
    {
        parent::setUp();

        $lng_mock = $this->createMock(ilLanguage::class);
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
                  ->method("getFormAction")
                  ->willReturnCallback(function () {
                      return "testFormAction";
                  });

        $this->setGlobalVariable("lng", $lng_mock);
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);
        $this->setGlobalVariable("tpl", $this->createMock(ilGlobalPageTemplate::class));
        $this->setGlobalVariable("component.repository", $this->createMock(ilComponentRepository::class));
        $this->setGlobalVariable("component.factory", $this->createMock(ilComponentFactory::class));
        $this->setGlobalVariable("ilDB", $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable("ilUser", $this->createMock(ilObjUser::class));

        $this->setGlobalVariable("ilLoggerFactory", new class() extends ilLoggerFactory {
            public function __construct()
            {
            }

            public static function getRootLogger() : ilLogger
            {
                return new class() extends ilLogger {
                    public function __construct()
                    {
                    }

                    public function write(string $a_message, $a_level = ilLogLevel::INFO) : void
                    {
                    }

                    public function info(string $a_message) : void
                    {
                    }

                    public function debug(string $a_message, array $a_context = array()) : void
                    {
                    }
                };
            }

            public static function getLogger(string $a_component_id) : ilLogger
            {
                return new class() extends ilLogger {
                    public function __construct()
                    {
                    }

                    public function write(string $a_message, $a_level = ilLogLevel::INFO) : void
                    {
                    }

                    public function debug(string $a_message, array $a_context = array()) : void
                    {
                    }
                };
            }
        });

        $test_gui = $this->getMockBuilder(ilObjTestVerificationGUI::class)->disableOriginalConstructor()->getMock();
        $this->tableGui = new ilTestVerificationTableGUI($test_gui);
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestVerificationTableGUI::class, $this->tableGui);
    }
}
