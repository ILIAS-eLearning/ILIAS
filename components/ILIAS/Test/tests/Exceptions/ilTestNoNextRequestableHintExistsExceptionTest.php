<?php

class ilTestNoNextRequestableHintExistsExceptionTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct(array $input, array $output): void
    {
        $ilTestNoNextRequestableHintExistsException = isset($input['code'])
            ? new ilTestNoNextRequestableHintExistsException($input['msg'], $input['code'])
            : new ilTestNoNextRequestableHintExistsException($input['msg'])
        ;
        $this->assertInstanceOf(ilTestNoNextRequestableHintExistsException::class, $ilTestNoNextRequestableHintExistsException);
        $this->assertEquals($output['msg'], $ilTestNoNextRequestableHintExistsException->getMessage());
        $this->assertEquals($output['code'], $ilTestNoNextRequestableHintExistsException->getCode());
    }

    public function constructDataProvider(): array
    {
        return [
            [['msg' => '', 'code' => -1], ['msg' => ilTestNoNextRequestableHintExistsException::class, 'code' => -1]],
            [['msg' => '', 'code' => 0], ['msg' => ilTestNoNextRequestableHintExistsException::class, 'code' => 0]],
            [['msg' => '', 'code' => 1], ['msg' => ilTestNoNextRequestableHintExistsException::class, 'code' => 1]],
            [['msg' => ''], ['msg' => ilTestNoNextRequestableHintExistsException::class, 'code' => 0]],
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
        $this->expectException(ilTestNoNextRequestableHintExistsException::class);
        $this->expectExceptionMessage($output['msg']);
        $this->expectExceptionCode($output['code']);
        throw isset($input['code'])
            ? new ilTestNoNextRequestableHintExistsException($input['msg'], $input['code'])
            : new ilTestNoNextRequestableHintExistsException($input['msg'])
        ;
    }

    public function exceptionDataProvider(): array
    {
        return [
            [['msg' => '', 'code' => -1], ['msg' => ilTestNoNextRequestableHintExistsException::class, 'code' => -1]],
            [['msg' => '', 'code' => 0], ['msg' => ilTestNoNextRequestableHintExistsException::class, 'code' => 0]],
            [['msg' => '', 'code' => 1], ['msg' => ilTestNoNextRequestableHintExistsException::class, 'code' => 1]],
            [['msg' => ''], ['msg' => ilTestNoNextRequestableHintExistsException::class, 'code' => 0]],
            [['msg' => 'test', 'code' => -1], ['msg' => 'test', 'code' => -1]],
            [['msg' => 'test', 'code' => 0], ['msg' => 'test', 'code' => 0]],
            [['msg' => 'test', 'code' => 1], ['msg' => 'test', 'code' => 1]],
            [['msg' => 'test'], ['msg' => 'test', 'code' => 0]],
        ];
    }
}