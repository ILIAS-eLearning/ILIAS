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

namespace ILIAS\LegalDocuments\test\ConsumerToolbox\ConsumerSlots;

use DateTimeImmutable;
use ilObjUser;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\PublicApi;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../ContainerMock.php';

class PublicApiTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(PublicApi::class, new PublicApi(false, $this->fail(...)));
    }

    public function testActive(): void
    {
        $this->assertTrue((new PublicApi(true, $this->fail(...)))->active());
        $this->assertFalse((new PublicApi(false, $this->fail(...)))->active());
    }

    public function testAgreed(): void
    {
        $this->assertTrueFalseForMethod('agreed', fn(bool $b) => ['neverAgreed' => !$b]);
    }

    public function testEverAgreed(string $method = 'everAgreed'): void
    {
        $this->assertTrueFalseForMethod('agreed', fn(bool $b) => ['neverAgreed' => !$b]);
    }

    public function testAgreedToCurrentlyMatchingDocument(): void
    {
        $this->assertTrueFalseForMethod('agreedToCurrentlyMatchingDocument', fn(bool $b) => [
            'needsToAcceptNewDocument' => !$b,
        ]);
    }

    public function testCanAgree(): void
    {
        $this->assertTrueFalseForMethod('canAgree', fn(bool $b) => [
            'cannotAgree' => !$b,
        ]);
    }

    public function testNeedsToAgree(): void
    {
        $this->assertSameValues('needsToAgree', [
            [false,  ['cannotAgree' => true, 'needsToAcceptNewDocument' => true]],
            [true, ['cannotAgree' => false, 'needsToAcceptNewDocument' => true]],
            [false, ['cannotAgree' => true, 'needsToAcceptNewDocument' => false]],
            [false, ['cannotAgree' => false, 'needsToAcceptNewDocument' => false]],
        ]);
    }

    public function testAgreeDate(): void
    {
        $d = new DateTimeImmutable();
        $this->assertSameValues('agreeDate', [
            [$d, ['agreeDate' => ['value' => $d]]],
            [null, ['agreeDate' => ['value' => null]]],
        ]);
    }

    private function assertTrueFalseForMethod(string $method, callable $dpro_user_tree)
    {
        $this->assertSameValues($method, [
            [true, $dpro_user_tree(true)],
            [false, $dpro_user_tree(false)]
        ]);
    }

    private function assertSameValues(string $method, array $compare): void
    {
        $user = $this->mock(ilObjUser::class);
        $dpro_user = fn($ret) => $this->mockTree(User::class, $dpro_user_tree($ret));

        foreach ($compare as $pair) {
            $this->assertSame($pair[0], (new PublicApi(true, fn() => $this->mockTree(User::class, $pair[1])))->$method($user));
        }
    }
}
