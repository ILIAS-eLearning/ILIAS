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
* Unit tests for assErrorTextTest
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assErrorTextTest extends assBaseTestCase
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

    public function test_instantiateObjectSimple(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';

        // Act
        $instance = new assErrorText();

        // Assert
        $this->assertInstanceOf('assErrorText', $instance);
    }

    public function test_getErrorsFromText(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
        $instance = new assErrorText();

        $errortext = '
			Eine ((Kündigung)) kommt durch zwei gleichlautende Willenserklärungen zustande.
			Ein Vertrag kommt durch ((drei gleichlaute)) Willenserklärungen zustande.
			Ein Kaufvertrag an der Kasse im #Supermarkt kommt durch das legen von Ware auf das
			Kassierband und den Kassiervorgang zustande. Dies nennt man ((konsequentes)) Handeln.';

        $expected = array(
            'passages' => array( 0 => 'Kündigung',  1 => 'drei gleichlaute', 3 => 'konsequentes'),
            'words' => array( 2 => 'Supermarkt')
        );

        // Act
        $actual = $instance->getErrorsFromText($errortext);

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_getErrorsFromText_noMatch(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
        $instance = new assErrorText();

        $errortext = '
			Eine Kündigung)) kommt durch zwei gleichlautende (Willenserklärungen) zustande.
			Ein Vertrag kommt durch (drei gleichlaute) Willenserklärungen zustande.
			Ein Kaufvertrag an der Kasse im Supermarkt [kommt] durch das legen von Ware auf das
			Kassierband und den [[Kassiervorgang]] zustande. Dies nennt man *konsequentes Handeln.';

        $expected = array();

        // Act
        $actual = $instance->getErrorsFromText($errortext);

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /* Removed by @kergomard 17 NOV 2022, we should introduce this again
    public function test_getErrorsFromText_emptyArgShouldPullInternal(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
        $instance = new assErrorText();

        $errortext = '
            Eine ((Kündigung)) kommt durch zwei gleichlautende Willenserklärungen zustande.
            Ein Vertrag kommt durch ((drei gleichlaute)) Willenserklärungen zustande.
            Ein Kaufvertrag an der Kasse im #Supermarkt kommt durch das legen von Ware auf das
            Kassierband und den Kassiervorgang zustande. Dies nennt man ((konsequentes)) Handeln.';

        $expected = array(
            'passages' => array( 0 => 'Kündigung',  1 => 'drei gleichlaute', 3 => 'konsequentes'),
            'words' => array( 2 => 'Supermarkt')
        );

        // Act
        $instance->setErrorText($errortext);
        $actual = $instance->getErrorsFromText('');

        // Assert
        $this->assertEquals($expected, $actual);
    } */

    public function test_setErrordata_newError(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
        $instance = new assErrorText();

        $errordata = array('passages' => array( 0 => 'drei Matrosen'), 'words' => array());
        require_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
        $expected = new assAnswerErrorText($errordata['passages'][0], '', 0.0);

        // Act
        $instance->setErrorData($errordata);

        $all_errors = $instance->getErrorData();
        $actual = $all_errors[0];

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setErrordata_oldErrordataPresent(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
        $instance = new assErrorText();

        $errordata = array('passages' => array( 0 => 'zwei Matrosen'), 'words' => array());
        $expected = array('passages' => array( 0 => 'drei Matrosen'), 'words' => array());
        $instance->setErrorData($expected);

        // Act
        $instance->setErrorData($errordata);

        $all_errors = $instance->getErrorData();
        /** @var assAnswerErrorText $actual */
        $actual = $all_errors[0];
        // Assert
        $this->assertEquals($errordata['passages'][0], $actual->text_wrong);
    }
}
