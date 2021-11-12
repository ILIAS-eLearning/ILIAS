<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlTokenTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlTokenTest extends TestCase
{
    /**
     * @var ilDBInterface
     */
    private ilDBInterface $database;

    /**
     * @var ilObjUser
     */
    private ilObjUser $user;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        $this->database = $this->createMock(ilDBInterface::class);
        $this->user = $this->createMock(ilObjUser::class);
        $this->user->method('getId')->willReturn(1);
    }

    public function testTokenGeneration() : void
    {
        $first_token = new ilCtrlToken($this->database, $this->user, 'test_sid1');
        $first_token_value = $first_token->getToken();
        $this->assertNotEmpty($first_token_value);

        $this->user->method('getId')->willReturn(2);
        $second_token = new ilCtrlToken($this->database, $this->user, 'test_sid2');
        $second_token_value = $second_token->getToken();
        $this->assertNotEmpty($second_token_value);

        $this->assertNotEquals($first_token_value, $second_token_value);
        $this->assertEquals($first_token_value, $first_token->getToken());
        $this->assertEquals($second_token_value, $second_token->getToken());
    }

    public function testTokenDestruction() : void
    {
        $token = new ilCtrlToken($this->database, $this->user, 'test_sid');
        $token_value = $token->getToken();

        $this->assertNotEmpty($token_value);
        $token->destroyToken();
        $this->assertNotEquals($token_value, $token->getToken());
    }

    public function testTokenVerification() : void
    {
        $token = new ilCtrlToken($this->database, $this->user, 'test_sid');
        $token_value = $token->getToken();

        $this->assertTrue($token->verifyWith($token_value));
        $this->assertFalse($token->verifyWith(''));
        $this->assertFalse($token->verifyWith('xyz'));
    }
}