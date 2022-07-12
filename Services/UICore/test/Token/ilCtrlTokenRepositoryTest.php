<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlTokenRepositoryTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlTokenRepositoryTest extends TestCase
{
    public function testTokenStorage() : void
    {
        $repository = new ilCtrlTokenRepository();
        $token_one = $repository->getToken();
        $token_two = $repository->getToken();

        $this->assertNotEmpty($token_one->getToken());
        $this->assertNotEmpty($token_two->getToken());
        $this->assertEquals(
            $token_one->getToken(),
            $token_two->getToken()
        );
    }

    public function testTokenGeneration() : void
    {
        $repository = new class() extends ilCtrlTokenRepository {
            public function generate()
            {
                return $this->generateToken();
            }
        };

        $token_one = $repository->generate();
        $token_two = $repository->generate();

        $this->assertNotEmpty($token_one->getToken());
        $this->assertNotEmpty($token_two->getToken());
        $this->assertNotEquals(
            $token_one->getToken(),
            $token_two->getToken()
        );
    }
}
