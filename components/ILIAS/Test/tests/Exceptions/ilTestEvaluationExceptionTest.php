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

class ilTestEvaluationExceptionTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct(array $input, array $output): void
    {
        $ilTestEvaluationException = isset($input['code'])
            ? new ilTestEvaluationException($input['msg'], $input['code'])
            : new ilTestEvaluationException($input['msg'])
        ;
        $this->assertInstanceOf(ilTestEvaluationException::class, $ilTestEvaluationException);
        $this->assertEquals($output['msg'], $ilTestEvaluationException->getMessage());
        $this->assertEquals($output['code'], $ilTestEvaluationException->getCode());
    }

    public function constructDataProvider(): array
    {
        return [
            [['msg' => '', 'code' => -1], ['msg' => ilTestEvaluationException::class, 'code' => -1]],
            [['msg' => '', 'code' => 0], ['msg' => ilTestEvaluationException::class, 'code' => 0]],
            [['msg' => '', 'code' => 1], ['msg' => ilTestEvaluationException::class, 'code' => 1]],
            [['msg' => ''], ['msg' => ilTestEvaluationException::class, 'code' => 0]],
            [['msg' => 'test', 'code' => -1], ['msg' => 'test', 'code' => -1]],
            [['msg' => 'test', 'code' => 0], ['msg' => 'test', 'code' => 0]],
            [['msg' => 'test', 'code' => 1], ['msg' => 'test', 'code' => 1]],
            [['msg' => 'test'], ['msg' => 'test', 'code' => 0]],
        ];
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testException(array $input, array $output): void
    {
        $this->expectException(ilTestEvaluationException::class);
        $this->expectExceptionMessage($output['msg']);
        $this->expectExceptionCode($output['code']);
        throw isset($input['code'])
            ? new ilTestEvaluationException($input['msg'], $input['code'])
            : new ilTestEvaluationException($input['msg'])
        ;
    }

    public function exceptionDataProvider(): array
    {
        return [
            [['msg' => '', 'code' => -1], ['msg' => ilTestEvaluationException::class, 'code' => -1]],
            [['msg' => '', 'code' => 0], ['msg' => ilTestEvaluationException::class, 'code' => 0]],
            [['msg' => '', 'code' => 1], ['msg' => ilTestEvaluationException::class, 'code' => 1]],
            [['msg' => ''], ['msg' => ilTestEvaluationException::class, 'code' => 0]],
            [['msg' => 'test', 'code' => -1], ['msg' => 'test', 'code' => -1]],
            [['msg' => 'test', 'code' => 0], ['msg' => 'test', 'code' => 0]],
            [['msg' => 'test'], ['msg' => 'test', 'code' => 0]],
        ];
    }
}