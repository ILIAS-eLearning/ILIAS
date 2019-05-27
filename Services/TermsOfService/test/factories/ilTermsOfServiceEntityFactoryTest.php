<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceEntityFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceEntityFactoryTest extends ilTermsOfServiceBaseTest
{
    /**
     *
     */
    public function testInstanceCanBeCreated() : void
    {
        $factory = new ilTermsOfServiceEntityFactory();

        $this->assertInstanceOf('ilTermsOfServiceEntityFactory', $factory);
    }

    /**
     *
     */
    public function testExceptionIsRaisedWhenUnknownEntityIsRequested() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $factory = new ilTermsOfServiceEntityFactory();
        $factory->getByName('PHP Unit');
    }

    /**
     *
     */
    public function testAcceptanceEntityIsReturnedWhenRequestedByName() : void
    {
        $factory = new ilTermsOfServiceEntityFactory();

        $this->assertInstanceOf(
            'ilTermsOfServiceAcceptanceEntity',
            $factory->getByName('ilTermsOfServiceAcceptanceEntity')
        );
    }
}
