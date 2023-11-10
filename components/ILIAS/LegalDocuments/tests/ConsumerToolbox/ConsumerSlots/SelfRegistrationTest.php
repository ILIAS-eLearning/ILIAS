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

namespace ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots;

use ilObjUser;
use ilCheckboxInputGUI;
use ilPropertyFormGUI;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\Data\Result\Ok;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\SelfRegistration;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../ContainerMock.php';

class SelfRegistrationTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(SelfRegistration::class, new SelfRegistration(
            'foo',
            $this->mock(UI::class),
            $this->mock(User::class),
            $this->mock(Provide::class),
            $this->fail(...),
            $this->fail(...)
        ));
    }

    public function testLegacyInputGUIs(): void
    {
        $instance = new SelfRegistration(
            'foo',
            $this->mock(UI::class),
            $this->mockTree(User::class, ['matchingDocument' => new Ok($this->mock(Document::class))]),
            $this->mock(Provide::class),
            fn() => 'rendered',
            $this->fail(...),
            $this->mock(...)
        );

        $guis = $instance->legacyInputGUIs();
        $this->assertSame(3, count($guis));
    }

    public function testSaveLegacyForm(): void
    {
        $instance = new SelfRegistration(
            'foo',
            $this->mock(UI::class),
            $this->mockTree(User::class, ['matchingDocument' => new Ok($this->mock(Document::class))]),
            $this->mock(Provide::class),
            $this->fail(...),
            $this->fail(...),
            $this->fail(...)
        );

        $this->assertTrue($instance->saveLegacyForm($this->mockTree(ilPropertyFormGUI::class, ['getInput' => true])));
    }

    public function testSaveLegacyFormFailed(): void
    {
        $instance = new SelfRegistration(
            'foo',
            $this->mock(UI::class),
            $this->mockTree(User::class, ['matchingDocument' => new Ok($this->mock(Document::class))]),
            $this->mock(Provide::class),
            $this->fail(...),
            $this->fail(...),
            $this->fail(...)
        );

        $checkbox = $this->mock(ilCheckboxInputGUI::class);
        $checkbox->expects(self::once())->method('setAlert');

        $form = $this->mockTree(ilPropertyFormGUI::class, ['getInput' => false]);
        $form->expects(self::once())->method('getItemByPostVar')->with('accept_foo')->willReturn($checkbox);

        $this->assertFalse($instance->saveLegacyForm($form));
    }

    public function testUserCreation(): void
    {
        $user = $this->mock(ilObjUser::class);
        $ldoc_user = $this->mock(User::class);
        $ldoc_user->expects(self::once())->method('acceptMatchingDocument');

        $instance = new SelfRegistration(
            'foo',
            $this->mock(UI::class),
            $this->mock(User::class),
            $this->mock(Provide::class),
            $this->fail(...),
            function (ilObjUser $u) use ($user, $ldoc_user): User {
                $this->assertSame($user, $u);
                return $ldoc_user;
            },
            $this->fail(...)
        );

        $instance->userCreation($user);
    }
}
