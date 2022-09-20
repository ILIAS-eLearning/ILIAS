<?php

declare(strict_types=1);

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

use ILIAS\Services\User\InterestedUserFieldComponent;

/**
 * Class InterestedUserFieldComponentTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldComponentTest extends ilUserBaseTest
{
    public function testInterestedUserFieldComponent(): void
    {
        $interestedUserFieldComponent = new InterestedUserFieldComponent(
            "My Component Name",
            "My description"
        );

        $this->assertEquals("My Component Name", $interestedUserFieldComponent->getComponentName());
        $this->assertEquals("My description", $interestedUserFieldComponent->getDescription());
    }
}
