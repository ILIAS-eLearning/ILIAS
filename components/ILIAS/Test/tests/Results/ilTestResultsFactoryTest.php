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

namespace ILIAS\Tests\Tests\Results;

use ilObjTest;
use ilTestBaseTestCase;
use ilTestPassResult;
use ilTestResultsFactory;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class ilTestResultsFactoryTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestResultsFactory::class, $this->createInstanceOf(ilTestResultsFactory::class));
    }

    /**
     * @dataProvider getPassResultsForDataProvider
     * @throws Exception|ReflectionException
     */
    public function testGetPassResultsFor(array $IO): void
    {
        $active_id = $IO['active_id'];
        $pass_id = $IO['pass_id'];
        $is_user_output = $IO['is_user_output'];

        $il_test_results_factory = $this->createInstanceOf(ilTestResultsFactory::class);
        $il_obj_test = $this->createMock(ilObjTest::class);

        if ($is_user_output === null) {
            $il_test_pass_result = $il_test_results_factory->getPassResultsFor(
                $il_obj_test,
                $active_id,
                $pass_id,
            );
        } else {
            $il_test_pass_result = $il_test_results_factory->getPassResultsFor(
                $il_obj_test,
                $active_id,
                $pass_id,
                $is_user_output
            );
        }

        $this->assertInstanceOf(ilTestPassResult::class, $il_test_pass_result);
        $this->assertEquals($active_id, $il_test_pass_result->getActiveId());
        $this->assertEquals($pass_id, $il_test_pass_result->getPass());
    }

    public static function getPassResultsForDataProvider(): array
    {
        return [
            'zero_zero_default' => [['active_id' => 0, 'pass_id' => 0, 'is_user_output' => null]],
            'zero_zero_true' => [['active_id' => 0, 'pass_id' => 0, 'is_user_output' => true]],
            'zero_zero_false' => [['active_id' => 0, 'pass_id' => 0, 'is_user_output' => false]],
            'one_zero_default' => [['active_id' => 1, 'pass_id' => 0, 'is_user_output' => null]],
            'one_zero_true' => [['active_id' => 1, 'pass_id' => 0, 'is_user_output' => true]],
            'one_zero_false' => [['active_id' => 1, 'pass_id' => 0, 'is_user_output' => false]],
            'zero_one_default' => [['active_id' => 0, 'pass_id' => 1, 'is_user_output' => null]],
            'zero_one_true' => [['active_id' => 0, 'pass_id' => 1, 'is_user_output' => true]],
            'zero_one_false' => [['active_id' => 0, 'pass_id' => 1, 'is_user_output' => false]],
            'one_one_default' => [['active_id' => 1, 'pass_id' => 1, 'is_user_output' => null]],
            'one_one_true' => [['active_id' => 1, 'pass_id' => 1, 'is_user_output' => true]],
            'one_one_false' => [['active_id' => 1, 'pass_id' => 1, 'is_user_output' => false]]
        ];
    }
}
