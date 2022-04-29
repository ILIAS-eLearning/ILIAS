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

namespace ILIAS\Tests\Refinery;

use ILIAS\Refinery\ByTrying as ByTrying;
use ILIAS\Refinery\Container\Group as ContainerGroup;
use ILIAS\Refinery\Custom\Group as CustomGroup;
use ILIAS\Refinery\DateTime\Group as DateTimeGroup;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\IdentityTransformation;
use ILIAS\Refinery\In\Group as InGroup;
use ILIAS\Refinery\Integer\Group as IntegerGroup;
use ILIAS\Refinery\Logical\Group as LogicalGroup;
use ILIAS\Refinery\Numeric\Group as NumericGroup;
use ILIAS\Refinery\Password\Group as PasswordGroup;
use ILIAS\Refinery\String\Group as StringGroup;
use ILIAS\Refinery\To\Group as ToGroup;
use ILIAS\Refinery\URI\Group as URIGroup;
use ilLanguage;

class FactoryTest extends TestCase
{
    private Refinery $basicFactory;

    protected function setUp() : void
    {
        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->basicFactory = new Refinery(new DataFactory(), $language);
    }

    public function testCreateToGroup() : void
    {
        $group = $this->basicFactory->to();

        $this->assertInstanceOf(ToGroup::class, $group);
    }

    public function testCreateInGroup() : void
    {
        $group = $this->basicFactory->in();

        $this->assertInstanceOf(InGroup::class, $group);
    }

    public function testCreateIntegerGroup() : void
    {
        $group = $this->basicFactory->int();

        $this->assertInstanceOf(IntegerGroup::class, $group);
    }

    public function testCreateStringGroup() : void
    {
        $group = $this->basicFactory->string();

        $this->assertInstanceOf(StringGroup::class, $group);
    }

    public function testCreateNumericGroup() : void
    {
        $group = $this->basicFactory->numeric();

        $this->assertInstanceOf(NumericGroup::class, $group);
    }

    public function testCreateLogicalGroup() : void
    {
        $group = $this->basicFactory->logical();

        $this->assertInstanceOf(LogicalGroup::class, $group);
    }

    public function testCreatePasswordGroup() : void
    {
        $group = $this->basicFactory->password();

        $this->assertInstanceOf(PasswordGroup::class, $group);
    }

    public function testCreateCustomGroup() : void
    {
        $group = $this->basicFactory->custom();

        $this->assertInstanceOf(CustomGroup::class, $group);
    }

    public function testCreateContainerGroup() : void
    {
        $group = $this->basicFactory->container();

        $this->assertInstanceOf(ContainerGroup::class, $group);
    }

    public function testCreateDateTimeGroup() : void
    {
        $group = $this->basicFactory->dateTime();
        $this->assertInstanceOf(DateTimeGroup::class, $group);
    }

    public function testCreateUriGrouo() : void
    {
        $group = $this->basicFactory->uri();
        $this->assertInstanceOf(URIGroup::class, $group);
    }

    public function testByTryingInGroup() : void
    {
        $instance = $this->basicFactory->byTrying([
            $this->basicFactory->numeric(),
            $this->basicFactory->string()
        ]);
        $this->assertInstanceOf(ByTrying::class, $instance);
    }

    public function testIdentity() : void
    {
        $instance = $this->basicFactory->identity();
        $this->assertInstanceOf(IdentityTransformation::class, $instance);
    }
}
