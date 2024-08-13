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

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestAnswerOptionalQuestionsConfirmationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestAnswerOptionalQuestionsConfirmationGUITest extends ilTestBaseTestCase
{
    protected MockObject $lng_mock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lng_mock = $this->createMock(ilLanguage::class);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new ilTestAnswerOptionalQuestionsConfirmationGUI($this->lng_mock);

        $this->assertInstanceOf(ilTestAnswerOptionalQuestionsConfirmationGUI::class, $instance);
    }

    public function testGetAndSetCancelCmd(): void
    {
        $expect = 'testCancelCmd';

        $gui = new ilTestAnswerOptionalQuestionsConfirmationGUI($this->lng_mock);

        $gui->setCancelCmd($expect);

        $this->assertEquals($expect, $gui->getCancelCmd());
    }

    public function testGetAndSetConfirmCmd(): void
    {
        $expect = 'testConfirmCmd';

        $gui = new ilTestAnswerOptionalQuestionsConfirmationGUI($this->lng_mock);

        $gui->setConfirmCmd($expect);

        $this->assertEquals($expect, $gui->getConfirmCmd());
    }

    /**
     * @dataProvider buildDataProvider
     * @throws Exception|\PHPUnit\Framework\MockObject\Exception
     */
    public function testBuild(bool $input): void
    {
        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) {
            $mock
                ->expects($this->exactly(3))
                ->method('txt')
                ->willReturn('');
        });

        $il_test_answer_optional_questions_confirmation_gui = $this->createInstanceOf(ilTestAnswerOptionalQuestionsConfirmationGUI::class);
        $il_test_answer_optional_questions_confirmation_gui->setCancelCmd('');
        $il_test_answer_optional_questions_confirmation_gui->setConfirmCmd('');
        $this->assertNull($il_test_answer_optional_questions_confirmation_gui->build($input));
    }

    public static function buildDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider buildHeaderTextDataProvider
     * @throws ReflectionException|\PHPUnit\Framework\MockObject\Exception|Exception
     */
    public function testBuildHeaderText(bool $input, string $output): void
    {
        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) use ($input) {
            if ($input) {
                $mock
                    ->expects($this->once())
                    ->method('txt')
                    ->with('tst_optional_questions_confirmation_fixed_test')
                    ->willReturn('tst_optional_questions_confirmation_fixed_test_x');
                return;
            }

            $mock
                ->expects($this->once())
                ->method('txt')
                ->with('tst_optional_questions_confirmation_non_fixed_test')
                ->willReturn('tst_optional_questions_confirmation_non_fixed_test_x');
        });

        $il_test_answer_optional_questions_confirmation_gui = $this->createInstanceOf(ilTestAnswerOptionalQuestionsConfirmationGUI::class);
        $this->assertEquals($output, self::callMethod($il_test_answer_optional_questions_confirmation_gui, 'buildHeaderText', [$input]));
    }

    public static function buildHeaderTextDataProvider(): array
    {
        return [
            'true' => [true, 'tst_optional_questions_confirmation_fixed_test_x'],
            'false' => [false, 'tst_optional_questions_confirmation_non_fixed_test_x']
        ];
    }
}
