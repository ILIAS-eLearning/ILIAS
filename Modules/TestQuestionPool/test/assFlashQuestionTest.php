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
class assFlashQuestionTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');

        parent::setUp();

        require_once './Services/UICore/classes/class.ilCtrl.php';
        $ilCtrl_mock = $this->createMock('ilCtrl');
        $ilCtrl_mock->expects($this->any())->method('saveParameter');
        $ilCtrl_mock->expects($this->any())->method('saveParameterByClass');
        $this->setGlobalVariable('ilCtrl', $ilCtrl_mock);

        require_once './Services/Language/classes/class.ilLanguage.php';
        $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
        //$lng_mock->expects( $this->once() )->method( 'txt' )->will( $this->returnValue('Test') );
        $this->setGlobalVariable('lng', $lng_mock);

        $this->setGlobalVariable('ilias', $this->getIliasMock());
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
        $this->setGlobalVariable('ilDB', $this->getDatabaseMock());
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assFlashQuestion.php';

        // Act
        $instance = new assFlashQuestion();

        $this->assertInstanceOf('assFlashQuestion', $instance);
    }
}
