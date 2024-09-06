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

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestPassDeletionConfirmationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPassDeletionConfirmationGUITest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $il_test_pass_deletion_confirmation_gui = $this->createInstanceOf(ilTestPassDeletionConfirmationGUI::class);
        $this->assertInstanceOf(ilTestPassDeletionConfirmationGUI::class, $il_test_pass_deletion_confirmation_gui);
    }

    /**
     * @dataProvider buildDataProvider
     * @throws ReflectionException|Exception
     */
    public function testBuild(array $input): void
    {
        $context_is_valid = $input[2] === 'invalid';
        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) use ($context_is_valid) {
            $mock->expects($context_is_valid ? $this->never() : $this->exactly(3))
                ->method('txt')
                ->willReturnOnConsecutiveCalls('cancel', 'delete', 'conf_delete_pass');
        });
        $il_test_pass_deletion_confirmation_gui = $this->createInstanceOf(ilTestPassDeletionConfirmationGUI::class);

        if ($context_is_valid) {
            $this->expectException(ilTestException::class);
        }

        $this->assertNull($il_test_pass_deletion_confirmation_gui->build(...$input));
    }

    public static function buildDataProvider(): array
    {
        return [
            '-1_-1_contPassOverview' => [[-1, -1, 'contPassOverview']],
            '-1_-1_contInfoScreen' => [[-1, -1, 'contInfoScreen']],
            '-1_-1_invalid' => [[-1, -1, 'invalid']],
            '0_0_contPassOverview' => [[0, 0, 'contPassOverview']],
            '0_0_contInfoScreen' => [[0, 0, 'contInfoScreen']],
            '0_0_invalid' => [[0, 0, 'invalid']],
            '1_1_contPassOverview' => [[1, 1, 'contPassOverview']],
            '1_1_contInfoScreen' => [[1, 1, 'contInfoScreen']],
            '1_1_invalid' => [[1, 1, 'invalid']]
        ];
    }
}
