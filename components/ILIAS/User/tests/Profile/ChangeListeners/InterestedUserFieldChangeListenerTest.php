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

namespace ILIAS\User\Tests;

use ILIAS\User\Profile\ChangeListeners\InterestedUserFieldChangeListener;

/**
 * Class InterestedUserFieldChangeListenerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldChangeListenerTest extends BaseTestCase
{
    private InterestedUserFieldChangeListener $interestedUserFieldChangeListener;

    protected function setUp(): void
    {
        $this->interestedUserFieldChangeListener = new InterestedUserFieldChangeListener(
            $this->createMock(\ILIAS\Language\Language::class),
            'Test name',
            'Test fieldName'
        );
    }

    public function testGetName(): void
    {
        $this->assertEquals(
            'Test name',
            $this->interestedUserFieldChangeListener->getName()
        );
    }

    public function testGetFieldName(): void
    {
        $this->assertEquals(
            'Test fieldName',
            $this->interestedUserFieldChangeListener->getFieldName()
        );
    }

    public function testAddGetAttribute(): void
    {
        $interestedUserFieldAttribute = $this->interestedUserFieldChangeListener->addAttribute('ABCD');

        $this->assertEquals(['ABCD' => $interestedUserFieldAttribute], $this->interestedUserFieldChangeListener->getAttributes());
    }
}
