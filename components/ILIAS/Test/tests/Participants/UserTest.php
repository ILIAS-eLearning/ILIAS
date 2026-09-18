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

namespace ILIAS\Test\Tests\Participants;

use ILIAS\Language\Language;
use ILIAS\Test\Participants\User;

class UserTest extends \ilTestBaseTestCase
{
    private Language $lng;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lng = $this->createMock(Language::class);
        $this->lng->method('txt')->willReturnCallback(static fn(string $key): string => $key);
    }

    public function testGetters(): void
    {
        // Arrange
        $user = new User(42, 'jdoe', 'John', 'Doe', 'M123', 'imported name');

        // Act
        // (getters are exercised directly in the assertions)

        // Assert
        $this->assertSame(42, $user->getUserId());
        $this->assertSame('imported name', $user->getImportname());
        $this->assertSame('John', $user->getFirstname());
        $this->assertSame('Doe', $user->getLastname());
        $this->assertSame('jdoe', $user->getLogin());
        $this->assertSame('M123', $user->getMatriculation());
    }

    public function testGetDisplayNameForImportedAnonymousUser(): void
    {
        // Arrange
        $user = new User(ANONYMOUS_USER_ID, '', '', '', '', 'Imported Participant');

        // Act
        $display_name = $user->getDisplayName($this->lng);

        // Assert
        $this->assertSame('Imported Participant (imported)', $display_name);
    }

    public function testGetDisplayNameForImportedAnonymousUserInAnonymousTest(): void
    {
        // Arrange
        $user = new User(ANONYMOUS_USER_ID, '', '', '', '', 'Imported Participant');

        // Act
        $display_name = $user->getDisplayName($this->lng, true);

        // Assert
        $this->assertSame('Imported Participant (imported)', $display_name);
    }

    public function testGetDisplayNameForAnonymousTest(): void
    {
        // Arrange
        $user = new User(99, 'jdoe', 'John', 'Doe');

        // Act
        $display_name = $user->getDisplayName($this->lng, true);

        // Assert
        $this->assertSame('anonymous', $display_name);
    }

    public function testGetDisplayNameForDeletedUser(): void
    {
        // Arrange
        $user = new User(99);

        // Act
        $display_name = $user->getDisplayName($this->lng);

        // Assert
        $this->assertSame('user_deleted', $display_name);
    }

    public function testGetDisplayNameForRegularUser(): void
    {
        // Arrange
        $user = new User(99, 'jdoe', 'John', 'Doe');

        // Act
        $display_name = $user->getDisplayName($this->lng);

        // Assert
        $this->assertSame('John Doe', $display_name);
    }
}
