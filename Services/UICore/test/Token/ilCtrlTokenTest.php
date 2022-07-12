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
    public function testTokenRetrieval() : void
    {
        $expected_value = 'test_token_1';
        $token = new ilCtrlToken($expected_value);

        $this->assertEquals(
            $expected_value,
            $token->getToken()
        );
    }

    public function testTokenVerification() : void
    {
        $expected_value = 'test_token_2';
        $token = new ilCtrlToken($expected_value);
        $token_value = $token->getToken();

        $this->assertTrue($token->verifyWith($token_value));
        $this->assertFalse($token->verifyWith(''));
        $this->assertFalse($token->verifyWith('xyz'));
    }
}
