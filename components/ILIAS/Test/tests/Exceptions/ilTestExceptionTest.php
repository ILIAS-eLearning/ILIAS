<?php

class ilTestExceptionTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct(array $input, array $output): void
    {
        $ilTestException = isset($input['code'])
            ? new ilTestException($input['msg'], $input['code'])
            : new ilTestException($input['msg'])
        ;
        $this->assertInstanceOf(ilTestException::class, $ilTestException);
        $this->assertEquals($output['msg'], $ilTestException->getMessage());
        $this->assertEquals($output['code'], $ilTestException->getCode());
    }

    public function constructDataProvider(): array
    {
        return [
            [['msg' => '', 'code' => -1], ['msg' => ilTestException::class, 'code' => -1]],
            [['msg' => '', 'code' => 0], ['msg' => ilTestException::class, 'code' => 0]],
            [['msg' => '', 'code' => 1], ['msg' => ilTestException::class, 'code' => 1]],
            [['msg' => ''], ['msg' => ilTestException::class, 'code' => 0]],
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
        $this->expectException(ilTestException::class);
        $this->expectExceptionMessage($output['msg']);
        $this->expectExceptionCode($output['code']);
        throw isset($input['code'])
            ? new ilTestException($input['msg'], $input['code'])
            : new ilTestException($input['msg'])
        ;
    }

    public function exceptionDataProvider(): array
    {
        return [
            [['msg' => '', 'code' => -1], ['msg' => ilTestException::class, 'code' => -1]],
            [['msg' => '', 'code' => 0], ['msg' => ilTestException::class, 'code' => 0]],
            [['msg' => '', 'code' => 1], ['msg' => ilTestException::class, 'code' => 1]],
            [['msg' => ''], ['msg' => ilTestException::class, 'code' => 0]],
            [['msg' => 'test', 'code' => -1], ['msg' => 'test', 'code' => -1]],
            [['msg' => 'test', 'code' => 0], ['msg' => 'test', 'code' => 0]],
            [['msg' => 'test', 'code' => 1], ['msg' => 'test', 'code' => 1]],
            [['msg' => 'test'], ['msg' => 'test', 'code' => 0]],
        ];
    }
}