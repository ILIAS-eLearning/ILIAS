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

namespace ILIAS\Data\Privacy\Purpose;

use ILIAS\Data\Privacy\Source\DbTableColumn;
use ILIAS\Data\Privacy\Source\DbTableColumns;
use PHPUnit\Framework\TestCase;

class PurposesTest extends TestCase
{
    public function testStoreInTableWithSingleColumn(): void
    {
        $target = new DbTableColumn('il_mail', 'sender_id');
        $purpose = new StoreInTable($target);

        $this->assertSame($target, $purpose->getTarget());
        $this->assertSame('store_in:il_mail.sender_id', $purpose->describe());
    }

    public function testStoreInTableWithCompoundColumns(): void
    {
        $purpose = new StoreInTable(
            new DbTableColumns('usr_data', 'street', 'city')
        );

        $this->assertSame('store_in:usr_data.(street,city)', $purpose->describe());
    }

    public function testDisplayToUser(): void
    {
        $purpose = new DisplayToUser('public_profile');

        $this->assertSame('public_profile', $purpose->getUiContext());
        $this->assertSame('display_to_user:public_profile', $purpose->describe());
    }

    public function testPassToComponent(): void
    {
        $purpose = new PassToComponent('Mail', 'signature');

        $this->assertSame('Mail', $purpose->getComponent());
        $this->assertSame('signature', $purpose->getReason());
        $this->assertSame('pass_to:Mail (signature)', $purpose->describe());
    }

    public function testTechnicalProcessing(): void
    {
        $purpose = new TechnicalProcessing('pseudonymisation');

        $this->assertSame('pseudonymisation', $purpose->getOperation());
        $this->assertSame('technical:pseudonymisation', $purpose->describe());
    }

    public function testLegacyAccess(): void
    {
        $purpose = new LegacyAccess('profile_data_getter');

        $this->assertSame('profile_data_getter', $purpose->getHint());
        $this->assertSame('legacy:profile_data_getter', $purpose->describe());
    }

    public function testLegacyAccessDefaultsToUnclassified(): void
    {
        $this->assertSame('legacy:unclassified', new LegacyAccess()->describe());
    }

    public function testFactoryBuildsAllPurposes(): void
    {
        $purposes = new Purposes();

        $this->assertSame(
            'store_in:usr_data.street',
            $purposes->storeInTable(new DbTableColumn('usr_data', 'street'))->describe()
        );
        $this->assertSame('display_to_user:public_profile', $purposes->displayToUser('public_profile')->describe());
        $this->assertSame('pass_to:Mail (signature)', $purposes->passToComponent('Mail', 'signature')->describe());
        $this->assertSame('technical:comparison', $purposes->technicalProcessing('comparison')->describe());
        $this->assertSame('legacy:some_getter', $purposes->legacyAccess('some_getter')->describe());
        $this->assertSame('legacy:unclassified', $purposes->legacyAccess()->describe());
    }
}
