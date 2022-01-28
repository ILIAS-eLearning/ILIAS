<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\User\InterestedUserFieldComponent;

/**
 * Class InterestedUserFieldComponentTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldComponentTest extends ilUserBaseTest
{
    public function testInterestedUserFieldComponent() : void
    {
        $interestedUserFieldComponent = new InterestedUserFieldComponent(
            "My Component Name",
            "My description"
        );

        $this->assertEquals("My Component Name", $interestedUserFieldComponent->getComponentName());
        $this->assertEquals("My description", $interestedUserFieldComponent->getDescription());
    }
}
