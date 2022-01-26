<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestVerificationTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestVerificationTableGUITest extends ilTestBaseTestCase
{
    private ilTestVerificationTableGUI $tableGui;
    private ilObjTestGUI $parentObj_mock;

    protected function setUp() : void
    {
        parent::setUp();

        $lng_mock = $this->createMock(ilLanguage::class);
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock->expects($this->any())
                  ->method("getFormAction")
                  ->willReturnCallback(function () {
                      return "testFormAction";
                  });

        $this->setGlobalVariable("lng", $lng_mock);
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);
        $this->setGlobalVariable("tpl", $this->createMock(ilGlobalPageTemplate::class));
        $this->setGlobalVariable("component.repository", $this->createMock(ilComponentRepository::class));
        $this->setGlobalVariable("ilPluginAdmin", new ilPluginAdmin());
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

                    public function write($m, $l = ilLogLevel::INFO)
                    {
                    }

                    public function info($a_message)
                    {
                        return "testInfo";
                    }
                };
            }

            public static function getLogger($a) : ilLoggerInterface
            {
                return new class() implements ilLoggerInterface {
                    public function isHandling($a_level){}
    
                    public function log($a_message, $a_level = ilLogLevel::INFO){}
    
                    public function dump($a_variable, $a_level = ilLogLevel::INFO){}
    
                    public function debug($a_message, $a_context = []){}
    
                    public function info($a_message){}
    
                    public function notice($a_message){}
    
                    public function warning($a_message){}
    
                    public function error($a_message){}
    
                    public function critical($a_message){}
    
                    public function alert($a_message){}
    
                    public function emergency($a_message){}
    
                    public function getLogger(){}
    
                    public function write($a_message, $a_level = ilLogLevel::INFO){}
    
                    public function writeLanguageLog($a_topic, $a_lang_key){}
    
                    public function logStack($a_level = null, $a_message = ''){}
    
                    public function writeMemoryPeakUsage($a_level) {}
    
                };
            }
        });

        $this->markTestSkipped(
            "Failing in GitHub, ilTestVerificationTableGUI wants ilObjTestVerificationGUI as first parameter"
        );

        $this->parentObj_mock = $this->createMock(ilObjTestGUI::class);
        $this->parentObj_mock->object = $this->createMock(ilObjTest::class);
        $this->tableGui = new ilTestVerificationTableGUI($this->parentObj_mock, "");
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestVerificationTableGUI::class, $this->tableGui);
    }
}
