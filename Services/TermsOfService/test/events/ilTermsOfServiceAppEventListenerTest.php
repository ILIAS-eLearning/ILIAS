<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAppEventListenerTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAppEventListenerTest extends ilTermsOfServiceBaseTest
{
    public function testAcceptanceHistoryDeletionIsDelegatedWhenUserIsDeleted() : void
    {
        $helper = $this->getMockBuilder(ilTermsOfServiceHelper::class)->disableOriginalConstructor()->getMock();

        $helper
            ->expects($this->once())
            ->method('deleteAcceptanceHistoryByUser')
            ->with($this->isType('integer'));

        $listener = new ilTermsOfServiceAppEventListener($helper);
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

    public function testStaticEventListeningWorksAsExpected() : void
    {
        $database = $this
            ->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $this->setGlobalVariable('ilDB', $database);

        $evaluator = $this
            ->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)
            ->getMock();
        $this->setGlobalVariable('tos.document.evaluator', $evaluator);

        $criterionFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();
        $this->setGlobalVariable('tos.criteria.type.factory', $criterionFactory);

        $helper = $this->getMockBuilder(ilTermsOfServiceHelper::class)->disableOriginalConstructor()->getMock();

        $helper
            ->expects($this->once())
            ->method('deleteAcceptanceHistoryByUser')
            ->with($this->isType('integer'));

        ilTestableTermsOfServiceAppEventListener::$mockHelper = $helper;
        ilTestableTermsOfServiceAppEventListener::handleEvent('Services/User', 'deleteUser', ['usr_id' => 6]);
    }
}

/**
 * Class ilTestableTermsOfServiceAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTestableTermsOfServiceAppEventListener extends ilTermsOfServiceAppEventListener
{
    public static ilTermsOfServiceHelper $mockHelper;

    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        $listener = new self(self::$mockHelper);
        $listener
            ->withComponent($a_component)
            ->withEvent($a_event)
            ->withParameters($a_parameter)
            ->handle();
    }
}
