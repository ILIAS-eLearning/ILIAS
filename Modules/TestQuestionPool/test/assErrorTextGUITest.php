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

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assErrorTextGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        parent::setUp();

        $ilCtrl_mock = $this->getMockBuilder(ilCtrl::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $ilCtrl_mock->method('saveParameter');
        $ilCtrl_mock->method('saveParameterByClass');
        $this->setGlobalVariable('ilCtrl', $ilCtrl_mock);

        $lng_mock = $this->getMockBuilder(ilLanguage::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['txt'])
                         ->getMock();
        $lng_mock->method('txt')->will($this->returnValue('Test'));
        $this->setGlobalVariable('lng', $lng_mock);

        $this->setGlobalVariable('ilias', $this->getIliasMock());
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
        $this->setGlobalVariable('ilDB', $this->getDatabaseMock());

        global $DIC;
        $user_mock = $this->createMock('ilObjUser', array(), array(), '', false);
        $DIC['ilUser'] = $user_mock;
        $GLOBALS['ilUser'] = $user_mock;
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        //$this->markTestIncomplete('Needs mock.');
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assErrorTextGUI.php';

        // Act
        $instance = new assErrorTextGUI();

        $this->assertInstanceOf('assErrorTextGUI', $instance);
    }
}
