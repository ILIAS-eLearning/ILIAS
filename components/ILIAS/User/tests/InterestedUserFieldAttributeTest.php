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

use ILIAS\User\Profile\ChangeListeners\InterestedUserFieldAttribute;
use ILIAS\User\Profile\ChangeListeners\InterestedUserFieldComponent;

/**
 * Class InterestedUserFieldAttributeTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldAttributeTest extends ilUserBaseTestCase
{
    private InterestedUserFieldAttribute $interested_user_field_attribute;

    protected function setUp(): void
    {
        $this->interested_user_field_attribute = new InterestedUserFieldAttribute(
            $this->createMock(ilLanguage::class),
            'ABCD',
            'EFGH'
        );
    }

    public function testGetAttributeName(): void
    {
        $this->assertEquals('ABCD', $this->interested_user_field_attribute->getAttributeName());
    }

    public function testGetFieldName(): void
    {
        $this->assertEquals('INVALID TRANSLATION KEY', $this->interested_user_field_attribute->getName());
    }

    public function testAddGetComponent(): void
    {
        $this->interested_user_field_attribute->addComponent(
            'comp name',
            'Description'
        );

        $this->assertEquals(
            [
                'comp name' => new InterestedUserFieldComponent(
                    'comp name',
                    'Description'
                )
            ],
            $this->interested_user_field_attribute->getComponents()
        );
    }
}
