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

namespace ILIAS\LegalDocuments\test\ConsumerToolbox\KeyValueStore;

use ILIAS\LegalDocuments\test\ContainerMock;
use ilObjUser;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore\UserPreferenceStore;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../ContainerMock.php';

class UserPreferenceStoreTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(UserPreferenceStore::class, new UserPreferenceStore($this->mock(ilObjUser::class)));
    }

    public function testValue(): void
    {
        $this->assertSame('bar', (new UserPreferenceStore($this->mockMethod(ilObjUser::class, 'getPref', ['foo'], 'bar')))->value('foo'));
    }

    public function testNullValue(): void
    {
        $this->assertSame('', (new UserPreferenceStore($this->mockMethod(ilObjUser::class, 'getPref', ['foo'], null)))->value('foo'));
    }

    public function testUpdate(): void
    {
        $user = $this->mock(ilObjUser::class);
        $user->expects(self::once())->method('writePref')->with('foo', 'bar');

        (new UserPreferenceStore($user))->update('foo', 'bar');
    }
}
