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
* @ingroup components\ILIASTestQuestionPool
*/
class assErrorTextTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '/../../../../');

        parent::setUp();

        $ilCtrl_mock = $this->createMock('ilCtrl');
        $ilCtrl_mock->expects($this->any())->method('saveParameter');
        $ilCtrl_mock->expects($this->any())->method('saveParameterByClass');
        $this->setGlobalVariable('ilCtrl', $ilCtrl_mock);

        $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
        //$lng_mock->expects( $this->once() )->method( 'txt' )->will( $this->returnValue('Test') );
        $this->setGlobalVariable('lng', $lng_mock);

        $this->setGlobalVariable('ilias', $this->getIliasMock());
        $this->setGlobalVariable('ilDB', $this->getDatabaseMock());
    }

    public function test_instantiateObjectSimple(): void
    {
        $instance = new assErrorText();

        $this->assertInstanceOf(assErrorText::class, $instance);
    }

    public function test_getErrorsFromText(): void
    {
        $instance = new assErrorText();
        $instance->setPointsWrong(-2);

        $errortext = '
			Eine Kündigung kommt durch zwei ((gleichlautende Willenserklärungen zustande)).
			Ein Vertrag kommt durch ((drei gleichlaute)) Willenserklärungen zustande.
			Ein Kaufvertrag an der Kasse im #Supermarkt kommt durch das legen von Ware auf das
			Kassierband und den Kassiervorgang zustande. Dies nennt man ((konsequentes Handeln.))';

        $expected = [
            new assAnswerErrorText('gleichlautende Willenserklärungen zustande.', '', 0.0, 6),
            new assAnswerErrorText('drei gleichlaute', '', 0.0, 13),
            new assAnswerErrorText('Supermarkt', '', 0.0, 23),
            new assAnswerErrorText('konsequentes Handeln.', '', 0.0, 40),
        ];

        $instance->setErrorText($errortext);
        $instance->parseErrorText();
        $instance->setErrorsFromParsedErrorText();
        $actual = $instance->getErrorData();

        $this->assertEquals($expected, $actual);
    }

    public function test_getErrorsFromText_noMatch(): void
    {
        $instance = new assErrorText();
        $instance->setPointsWrong(-2);

        $errortext = '
			Eine Kündigung)) kommt durch zwei gleichlautende (Willenserklärungen) zustande.
			Ein Vertrag kommt durch (drei gleichlaute) Willenserklärungen zustande.
			Ein Kaufvertrag an der Kasse im Supermarkt [kommt] durch das legen von Ware auf das
			Kassierband und den [[Kassiervorgang]] zustande. Dies nennt man *konsequentes Handeln.';

        $expected = [];

        $instance->setErrorText($errortext);
        $instance->parseErrorText();
        $instance->setErrorsFromParsedErrorText();
        $actual = $instance->getErrorData();

        $this->assertEquals($expected, $actual);
    }

    public function test_setErrordata(): void
    {
        $instance = new assErrorText();
        $instance->setPointsWrong(-2);

        $errordata = [new assAnswerErrorText('drei Matrosen')];
        $expected = [new assAnswerErrorText('drei Matrosen', '', 0.0, null)];
        $instance->setErrorData($errordata);
        $actual = $instance->getErrorData();

        $this->assertEquals($expected, $actual);
    }

    public function test_setErrordata_oldErrordataPresent(): void
    {
        $instance = new assErrorText();
        $instance->setPointsWrong(-2);

        $old_errordata = [
            new assAnswerErrorText('gleichlautende Willenserklärungen zustande.', '', 0.0, 6),
            new assAnswerErrorText('drei gleichlaute', '', 0.0, 13),
            new assAnswerErrorText('Supermarkt', '', 0.0, 23),
            new assAnswerErrorText('konsequentes Handeln.', '', 0.0, 40),
        ];
        $new_errordata = [
            new assAnswerErrorText('gleichlautende Willenserklärungen zustande.', '', 0.0, 2),
            new assAnswerErrorText('drei gleichlaute', '', 0.0, 3),
            new assAnswerErrorText('Supermarkt', '', 0.0, 11),
            new assAnswerErrorText('konsequentes Handeln.', '', 0.0, 32),
        ];

        $instance->setErrorData($old_errordata);
        $instance->setErrorData($new_errordata);

        $actual = $instance->getErrorData();

        $this->assertEquals($new_errordata, $actual);
    }
    public function test_removeErrorDataWithoutPosition(): void
    {
        $instance = new assErrorText();
        $instance->setPointsWrong(-2);

        $parsed_errortext = [
            0 => [
                ['text' => '1', 'error_type' => 'none'],
                [
                    'text' => 'gleichlautende',
                    'text_wrong' => 'gleichlautende Willenserklärungen zustande.',
                    'error_type' => 'passage_start',
                    'error_position' => 1,
                    'text_correct' => '',
                    'points' => 1,
                ],
                ['text' => '2', 'error_type' => 'none'],
                [
                    'text' => 'Supermarkt',
                    'text_wrong' => 'Supermarkt',
                    'error_type' => 'word',
                    'error_position' => 3,
                    'text_correct' => '',
                    'points' => 1,
                ],
            ]
        ];

        $errordata = [
            new assAnswerErrorText('gleichlautende Willenserklärungen zustande.', '', 0.0),
            new assAnswerErrorText('drei gleichlaute', '', 0.0),
            new assAnswerErrorText('Supermarkt', '', 0.0),
            new assAnswerErrorText('konsequentes Handeln.', '', ),
        ];
        $expected = [
            new assAnswerErrorText('gleichlautende Willenserklärungen zustande.', '', 0.0, 1),
            new assAnswerErrorText('Supermarkt', '', 0.0, 3),
        ];

        $instance->setParsedErrorText($parsed_errortext);
        $instance->setErrorData($errordata);
        $instance->removeErrorDataWithoutPosition();
        $actual = $instance->getErrorData();

        $this->assertEquals($expected, $actual);
    }
}
