<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAppEventListenerTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAppEventListenerTest extends \ilTermsOfServiceBaseTest
{
    /**
     *
     */
    public function testAcceptanceHistoryDeletionIsDelegatedWhenUserIsDeleted()
    {
        $helper = $this->getMockBuilder(\ilTermsOfServiceHelper::class)->disableOriginalConstructor()->getMock();

        $helper
            ->expects($this->once())
            ->method('deleteAcceptanceHistoryByUser')
            ->with($this->isType('integer'));

        $listener = new \ilTermsOfServiceAppEventListener($helper);
        $listener
            ->withComponent('Services/User')
            ->withEvent('deleteUser')
            ->withParameters(['usr_id' => 6])
            ->handle();

        $listener
            ->withComponent('Modules/Course')
            ->withEvent('deleteUser')
            ->withParameters(['usr_id' => 6])
            ->handle();

        $listener
            ->withComponent('Services/User')
            ->withEvent('afterCreate')
            ->withParameters(['usr_id' => 6])
            ->handle();
    }

    /**
     *
     */
    public function testStaticEventListeningWorksAsExpected()
    {
        $database = $this
            ->getMockBuilder(\ilDBInterface::class)
            ->getMock();

        $this->setGlobalVariable('ilDB', $database);

        $helper = $this->getMockBuilder(\ilTermsOfServiceHelper::class)->disableOriginalConstructor()->getMock();

        $helper
            ->expects($this->once())
            ->method('deleteAcceptanceHistoryByUser')
            ->with($this->isType('integer'));


        \ilTestableTermsOfServiceAppEventListener::$mockHelper = $helper;
        \ilTestableTermsOfServiceAppEventListener::handleEvent('Services/User', 'deleteUser', ['usr_id' => 6]);
    }
}

/**
 * Class ilTestableTermsOfServiceAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTestableTermsOfServiceAppEventListener extends \ilTermsOfServiceAppEventListener
{
    /** @var \ilTermsOfServiceHelper */
    public static $mockHelper;

    /**
     * ilTestableTermsOfServiceAppEventListener constructor.
     * @param ilTermsOfServiceHelper $helper
     */
    public function __construct(\ilTermsOfServiceHelper $helper)
    {
        parent::__construct(self::$mockHelper);
    }
}
