<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilGroupNameAsMailValidatorTest
 * @author Niels Theen <ntheen@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilGroupNameAsMailValidatorTest extends \ilMailBaseTest
{
    /**
     *
     */
    public function testGroupIsDetectedIfGroupNameExists()
    {
        $validator = new \ilGroupNameAsMailValidator('someHost', function (string $groupName) {
            return true;
        });

        $this->assertTrue($validator->validate(new ilMailAddress('phpunit', 'someHost')));
    }

    /**
     *
     */
    public function testGroupIsNotDetectedIfGroupNameDoesNotExists()
    {
        $validator = new \ilGroupNameAsMailValidator('someHost', function (string $groupName) {
            return false;
        });

        $this->assertFalse($validator->validate(new ilMailAddress('someHost', 'someHost')));
    }
}
