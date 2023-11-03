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

namespace ILIAS\LegalDocuments\test\ConsumerSlots\SelfRegistration;

use ilObjUser;
use ilPropertyFormGUI;
use ilFormPropertyGUI;
use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration\Bundle;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../ContainerMock.php';

class BundleTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Bundle::class, new Bundle([]));
    }

    public function testLegacyInputGUIs(): void
    {
        $a = $this->mock(ilFormPropertyGUI::class);
        $b = $this->mock(ilFormPropertyGUI::class);
        $c = $this->mock(ilFormPropertyGUI::class);

        $bundle = new Bundle([
            $this->mockTree(SelfRegistration::class, ['legacyInputGUIs' => [$a]]),
            $this->mockTree(SelfRegistration::class, ['legacyInputGUIs' => [$b, $c]])
        ]);
        $this->assertSame([$a, $b, $c], $bundle->legacyInputGUIs());
    }

    public function testSaveLegacyForm(): void
    {
        $form = $this->mock(ilPropertyFormGUI::class);

        $bundle = new Bundle([
            $this->mockMethod(SelfRegistration::class, 'saveLegacyForm', [$form], true),
            $this->mockMethod(SelfRegistration::class, 'saveLegacyForm', [$form], true)
        ]);
        $this->assertTrue($bundle->saveLegacyForm($form));

    }

    public function testUserCreation(): void
    {
        $user = $this->mock(ilObjUser::class);
        $self_registration = $this->mock(SelfRegistration::class);
        $self_registration->expects(self::exactly(2))->method('userCreation')->with($user);

        $bundle = new Bundle([
            $self_registration,
            $self_registration,
        ]);

        $bundle->userCreation($user);
    }
}
