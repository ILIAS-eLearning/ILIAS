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

use ILIAS\Authentication\Domain\AuthenticatedUserSubjectId;
use ILIAS\DI\Container;
use ILIAS\KeyValueStorage\Services;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ilAuthenticationAppEventListenerTest extends TestCase
{
    private ?Container $dic_backup = null;

    protected function setUp(): void
    {
        global $DIC;

        $this->dic_backup = $DIC instanceof Container ? $DIC : null;
        $DIC = new Container();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic_backup;

        parent::tearDown();
    }

    #[DataProvider('userDeletedComponents')]
    public function testADeletedUserIsPurged(string $component): void
    {
        $storage = $this->createMock(Services::class);
        $storage->expects($this->once())
            ->method('purgeSubject')
            ->with(AuthenticatedUserSubjectId::fromUserId(42));
        $this->dic()[Services::class] = static fn(): Services => $storage;

        ilAuthenticationAppEventListener::handleEvent($component, 'deleteUser', ['usr_id' => 42]);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function userDeletedComponents(): array
    {
        return [
            'legacy component' => ['Services/User'],
            'component revision' => ['components/ILIAS/User'],
        ];
    }

    public function testAnotherEventDoesNotPurge(): void
    {
        $this->dic()[Services::class] = $this->storageThatMustNotPurge();

        ilAuthenticationAppEventListener::handleEvent('Services/User', 'afterCreate', ['usr_id' => 42]);
    }

    public function testAnotherComponentDoesNotPurge(): void
    {
        $this->dic()[Services::class] = $this->storageThatMustNotPurge();

        ilAuthenticationAppEventListener::handleEvent('components/ILIAS/Course', 'deleteUser', ['usr_id' => 42]);
    }

    public function testANonPositiveUserIdDoesNotPurge(): void
    {
        $this->dic()[Services::class] = $this->storageThatMustNotPurge();

        ilAuthenticationAppEventListener::handleEvent('Services/User', 'deleteUser', ['usr_id' => 0]);
    }

    public function testAMissingUserIdDoesNotPurge(): void
    {
        $this->dic()[Services::class] = $this->storageThatMustNotPurge();

        ilAuthenticationAppEventListener::handleEvent('Services/User', 'deleteUser', []);
    }

    public function testAMissingKeyValueStorageServiceDoesNotPurge(): void
    {
        $this->expectNotToPerformAssertions();

        ilAuthenticationAppEventListener::handleEvent('Services/User', 'deleteUser', ['usr_id' => 42]);
    }

    private function dic(): Container
    {
        global $DIC;

        return $DIC;
    }

    private function storageThatMustNotPurge(): Services
    {
        $storage = $this->createMock(Services::class);
        $storage->expects($this->never())->method('purgeSubject');

        return $storage;
    }
}
