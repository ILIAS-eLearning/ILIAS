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

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlTokenTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlTokenTest extends TestCase
{
    public function testTokenRetrieval(): void
    {
        $expected_value = 'test_token_1';
        $token = new ilCtrlToken($expected_value);

        $this->assertEquals(
            $expected_value,
            $token->getToken()
        );
    }

    public function testTokenVerification(): void
    {
        $expected_value = 'test_token_2';
        $token = new ilCtrlToken($expected_value);
        $token_value = $token->getToken();

        $this->assertTrue($token->verifyWith($token_value));
        $this->assertFalse($token->verifyWith(''));
        $this->assertFalse($token->verifyWith('xyz'));
    }
}
