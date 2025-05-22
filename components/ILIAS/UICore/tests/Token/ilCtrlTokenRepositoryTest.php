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
 * Class ilCtrlTokenRepositoryTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlTokenRepositoryTest extends TestCase
{
    public function testTokenStorage(): void
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

    public function testTokenGeneration(): void
    {
        $repository = new class () extends ilCtrlTokenRepository {
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
