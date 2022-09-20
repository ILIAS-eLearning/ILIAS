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

/**
 * Class ilGroupNameAsMailValidatorTest
 * @author Niels Theen <ntheen@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilGroupNameAsMailValidatorTest extends ilMailBaseTest
{
    public function testGroupIsDetectedIfGroupNameExists(): void
    {
        $validator = new ilGroupNameAsMailValidator('someHost', static function (string $groupName): bool {
            return true;
        });

        $this->assertTrue($validator->validate(new ilMailAddress('phpunit', 'someHost')));
    }

    public function testGroupIsNotDetectedIfGroupNameDoesNotExists(): void
    {
        $validator = new ilGroupNameAsMailValidator('someHost', static function (string $groupName): bool {
            return false;
        });

        $this->assertFalse($validator->validate(new ilMailAddress('someHost', 'someHost')));
    }
}
