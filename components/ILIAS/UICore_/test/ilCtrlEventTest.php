<?php

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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilCtrlEventTest extends TestCase
{
    private ilCtrlSubject $subject;

    protected function setUp(): void
    {
        $this->subject = new ilCtrlSubject();
    }

    public function testObserverAttachment(): void
    {
        // Default Attachment
        $mocked_observer = $this->createMock(ilCtrlObserver::class);
        $mocked_subject = $this->createMock(ilCtrlSubject::class);
        $mocked_ilctrl = $this->getMockedCtrl($mocked_subject);
        $mocked_subject->expects($this->once())
                       ->method('attach')
                       ->with($mocked_observer, ilCtrlEvent::ALL);

        $mocked_ilctrl->attachObserver($mocked_observer);

        $mocked_subject->expects($this->once())
                       ->method('detach')
                       ->with($mocked_observer, ilCtrlEvent::ALL);
        $mocked_ilctrl->detachObserver($mocked_observer);

        // Command Class Attachment
        $mocked_observer = $this->createMock(ilCtrlObserver::class);
        $mocked_subject = $this->createMock(ilCtrlSubject::class);
        $mocked_ilctrl = $this->getMockedCtrl($mocked_subject);
        $mocked_subject->expects($this->once())
                       ->method('attach')
                       ->with($mocked_observer, ilCtrlEvent::COMMAND_CLASS_FORWARD);

        $mocked_ilctrl->attachObserver($mocked_observer, ilCtrlEvent::COMMAND_CLASS_FORWARD);

        $mocked_subject->expects($this->once())
                       ->method('detach')
                       ->with($mocked_observer, ilCtrlEvent::COMMAND_CLASS_FORWARD);
        $mocked_ilctrl->detachObserver($mocked_observer, ilCtrlEvent::COMMAND_CLASS_FORWARD);

        // Command Determination Attachment
        $mocked_observer = $this->createMock(ilCtrlObserver::class);
        $mocked_subject = $this->createMock(ilCtrlSubject::class);
        $mocked_ilctrl = $this->getMockedCtrl($mocked_subject);
        $mocked_subject->expects($this->once())
                       ->method('attach')
                       ->with($mocked_observer, ilCtrlEvent::COMMAND_DETERMINATION);

        $mocked_ilctrl->attachObserver($mocked_observer, ilCtrlEvent::COMMAND_DETERMINATION);

        $mocked_subject->expects($this->once())
                       ->method('detach')
                       ->with($mocked_observer, ilCtrlEvent::COMMAND_DETERMINATION);
        $mocked_ilctrl->detachObserver($mocked_observer, ilCtrlEvent::COMMAND_DETERMINATION);
    }

    public function testNotifyEvents(): void
    {
        require_once __DIR__ . '/Data/GUI/class.ilCtrlBaseClass1TestGUI.php';
        $base_class = new ilCtrlBaseClass1TestGUI();

        $mocked_ilctrl = $this->getMockedCtrl();

        $mocked_observer = $this->createMock(ilCtrlObserver::class);
        $mocked_ilctrl->attachObserver($mocked_observer, ilCtrlEvent::ALL);

        $mocked_observer->expects($this->exactly(2))
                        ->method('update')
                        ->withConsecutive(
                            [ilCtrlEvent::COMMAND_CLASS_FORWARD, ilCtrlBaseClass1TestGUI::class],
                            [ilCtrlEvent::COMMAND_DETERMINATION, 'fallback']
                        );

        $mocked_ilctrl->forwardCommand($base_class);
        $command = $mocked_ilctrl->getCmd('fallback');
    }

    /**
     * Helper function that returns an ilCtrl instance with mocked
     * dependencies.
     */
    private function getMockedCtrl(?ilCtrlSubject $subject = null): ilCtrlInterface
    {
        return new ilCtrl(
            $this->createMock(ilCtrlStructureInterface::class),
            $this->createMock(ilCtrlTokenRepositoryInterface::class),
            $this->createMock(ilCtrlPathFactoryInterface::class),
            $this->createMock(ilCtrlContextInterface::class),
            $this->createMock(ResponseSenderStrategy::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(Refinery::class),
            $this->createMock(ilComponentFactory::class),
            $subject ?? $this->subject
        );
    }
}
