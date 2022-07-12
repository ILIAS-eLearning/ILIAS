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
