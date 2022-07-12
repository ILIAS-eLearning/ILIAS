<?php declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\Password;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Password\HasLowerChars;
use ILIAS\Refinery\Password\HasMinLength;
use ILIAS\Refinery\Password\HasNumbers;
use ILIAS\Refinery\Password\HasSpecialChars;
use ILIAS\Refinery\Password\HasUpperChars;
use ILIAS\Refinery\Password\Group as PasswordGroup;
use ILIAS\Tests\Refinery\TestCase;
use ilLanguage;

class GroupTest extends TestCase
{
    private PasswordGroup $group;
    private DataFactory $dataFactory;
    private ilLanguage $language;

    protected function setUp() : void
    {
        $this->dataFactory = new DataFactory();
        $this->language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->group = new PasswordGroup($this->dataFactory, $this->language);
    }
    
    public function testHasMinLength() : void
    {
        $instance = $this->group->hasMinLength(4);
        $this->assertInstanceOf(HasMinLength::class, $instance);
    }

    public function testHasLowerChars() : void
    {
        $instance = $this->group->hasLowerChars();
        $this->assertInstanceOf(HasLowerChars::class, $instance);
    }

    public function testHasNumbers() : void
    {
        $instance = $this->group->hasNumbers();
        $this->assertInstanceOf(HasNumbers::class, $instance);
    }

    public function testHasSpecialChars() : void
    {
        $instance = $this->group->hasSpecialChars();
        $this->assertInstanceOf(HasSpecialChars::class, $instance);
    }

    public function testHasUpperChars() : void
    {
        $instance = $this->group->hasUpperChars();
        $this->assertInstanceOf(HasUpperChars::class, $instance);
    }
}
