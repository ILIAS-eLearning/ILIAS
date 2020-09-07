<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceEntityFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceEntityFactoryTest extends \ilTermsOfServiceBaseTest
{
    /**
     *
     */
    public function testInstanceCanBeCreated()
    {
        $factory = new \ilTermsOfServiceEntityFactory();

        $this->assertInstanceOf('ilTermsOfServiceEntityFactory', $factory);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsRaisedWhenUnknownEntityIsRequested()
    {
        $this->assertException(\InvalidArgumentException::class);

        $factory = new \ilTermsOfServiceEntityFactory();
        $factory->getByName('PHP Unit');
    }

    /**
     *
     */
    public function testAcceptanceEntityIsReturnedWhenRequestedByName()
    {
        $factory = new \ilTermsOfServiceEntityFactory();

        $this->assertInstanceOf(
            'ilTermsOfServiceAcceptanceEntity',
            $factory->getByName('ilTermsOfServiceAcceptanceEntity')
        );
    }
}
