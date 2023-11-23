<?php

class ilTestMissingQuestionPoolIdParameterExceptionTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct(array $input, array $output): void
    {
        $ilTestMissingQuestionPoolIdParameterException = isset($input['code'])
            ? new ilTestMissingQuestionPoolIdParameterException($input['msg'], $input['code'])
            : new ilTestMissingQuestionPoolIdParameterException($input['msg'])
        ;
        $this->assertInstanceOf(ilTestMissingQuestionPoolIdParameterException::class, $ilTestMissingQuestionPoolIdParameterException);
        $this->assertEquals($output['msg'], $ilTestMissingQuestionPoolIdParameterException->getMessage());
        $this->assertEquals($output['code'], $ilTestMissingQuestionPoolIdParameterException->getCode());
    }

    public function constructDataProvider(): array
    {
        return [
            [['msg' => '', 'code' => -1], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => '', 'code' => 0], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => '', 'code' => 1], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => ''], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => 'test', 'code' => -1], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => 'test', 'code' => 0], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => 'test', 'code' => 1], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => 'test'], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
        ];
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testException(array $input, array $output): void
    {
        $this->expectException(ilTestMissingQuestionPoolIdParameterException::class);
        $this->expectExceptionMessage($output['msg']);
        $this->expectExceptionCode($output['code']);
        throw isset($input['code'])
            ? new ilTestMissingQuestionPoolIdParameterException($input['msg'], $input['code'])
            : new ilTestMissingQuestionPoolIdParameterException($input['msg'])
        ;
    }

    public function exceptionDataProvider(): array
    {
        return [
            [['msg' => '', 'code' => -1], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => '', 'code' => 0], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => '', 'code' => 1], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => ''], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => 'test', 'code' => -1], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => 'test', 'code' => 0], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => 'test', 'code' => 1], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
            [['msg' => 'test'], ['msg' => ilTestMissingQuestionPoolIdParameterException::class, 'code' => 0]],
        ];
    }
}