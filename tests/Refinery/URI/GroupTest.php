<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\URI;

require_once('./libs/composer/vendor/autoload.php');

namespace ILIAS\Tests\Refinery\URI;

use ILIAS\Refinery\URI\Group;
use ILIAS\Refinery\URI\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

class GroupTest extends TestCase
{
    public function testStringTransformationInstance()
    {
        $group = new Group();
        $transformation = $group->toString();
        $this->assertInstanceOf(StringTransformation::class, $transformation);
    }
}
