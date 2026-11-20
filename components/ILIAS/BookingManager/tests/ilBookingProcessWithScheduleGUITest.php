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

use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\BookingProcess\BookingProcessManager;
use ILIAS\BookingManager\BookingProcess\ProcessUtilGUI;
use ILIAS\BookingManager\InternalGUIService;
use ILIAS\BookingManager\Reservations\ReservationManager;
use ILIAS\BookingManager\StandardGUIRequest;
use ILIAS\Data\Factory;
use ILIAS\HTTP\Services;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\Repository\Modal\ModalAdapterGUI;
use PHPUnit\Framework\TestCase;

class ilBookingProcessWithScheduleGUITest extends TestCase
{
    private int $slot_from;
    private int $slot_to;

    protected function setUp(): void
    {
        $this->slot_from = mktime(10, 0, 0, 7, 15, 2026);
        $this->slot_to = mktime(12, 0, 0, 7, 15, 2026);
    }

    public function testBookingProcessForwardsInclusiveSlotEndThroughRequests(): void
    {
        $slot = "{$this->slot_from}_{$this->slot_to}";
        $object_id = 42;
        $recorded_ctrl_params = [];

        $reservation = parent::createMock(ReservationManager::class);
        $reservation->expects(parent::exactly(2))
            ->method('getAvailableNr')
            ->with($object_id, $this->slot_from, $this->slot_to)
            ->willReturn(3);

        $ctrl = parent::createMock(ilCtrl::class);
        $ctrl->method('getLinkTarget')->willReturn('http://ilias.test/bookAvailableItems');
        $ctrl->method('setParameter')->willReturnCallback(
            function ($gui, string $name, mixed $value) use (&$recorded_ctrl_params): void {
                $recorded_ctrl_params[$name] = $value;
            }
        );
        $ctrl->method('setParameterByClass')->willReturnCallback(
            function (string $class, string $name, mixed $value) use (&$recorded_ctrl_params): void {
                $recorded_ctrl_params[$name] = $value;
            }
        );
        $ctrl->method('saveParameter')->willReturnSelf();

        $modal = parent::createMock(ModalAdapterGUI::class);
        $modal->method('form')->willReturnSelf();
        $modal->method('send')->willReturnSelf();

        $gui_service = parent::createMock(InternalGUIService::class);
        $gui_service->method('modal')->willReturn($modal);
        $gui_service->method('send')->willReturnCallback(static function (): void {
        });

        $valid_form = parent::createMock(FormAdapterGUI::class);
        $valid_form->method('isValid')->willReturn(true);
        $valid_form->method('getData')->willReturnMap([
            ['origin_cmd', ''],
            ['recurrence', '0'],
            ['nr', 1],
        ]);

        $process = parent::createMock(BookingProcessManager::class);
        $process->expects(parent::once())
            ->method('bookAvailableObjects')
            ->with(
                $object_id,
                7,
                7,
                0,
                $this->slot_from,
                $this->slot_to,
                0,
                1,
                null,
                ''
            )
            ->willReturn([99]);

        $pool = parent::getMockBuilder(ilObjBookingPool::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['usesMessages'])
            ->getMock();
        $pool->method('usesMessages')->willReturn(false);
        $pool->setRefId(15);

        $access = parent::createMock(AccessManager::class);
        $access->method('canManageReservationForUser')->willReturn(true);

        $util_gui = parent::createMock(ProcessUtilGUI::class);
        $util_gui->expects(parent::once())
            ->method('handleBookingSuccess')
            ->with($object_id, 'displayPostInfo', [99]);

        $tabs = parent::createMock(ilTabsGUI::class);
        $lng = parent::createMock(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);
        $log = parent::createMock(ilLogger::class);

        $gui = new ilBookingProcessWithScheduleGUIUnderTest();
        $gui->stubForm = $valid_form;
        $this->inject($gui, 'book_request', $this->createRequest([
            'slot' => $slot,
            'object_id' => (string) $object_id,
        ]));
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'tabs_gui', $tabs);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'log', $log);
        $this->inject($gui, 'gui', $gui_service);
        $this->inject($gui, 'reservation', $reservation);
        $this->inject($gui, 'pool', $pool);
        $this->inject($gui, 'book_obj_id', $object_id);
        $this->inject($gui, 'process', $process);
        $this->inject($gui, 'access', $access);
        $this->inject($gui, 'util_gui', $util_gui);
        $this->inject($gui, 'user_id_to_book', 7);
        $this->inject($gui, 'user_id_assigner', 7);
        $this->inject($gui, 'context_obj_id', 0);
        $this->inject($gui, 'tpl', parent::createMock(ilGlobalTemplateInterface::class));

        $gui->showNumberForm();

        parent::assertSame([$this->slot_from, $this->slot_to], $gui->getNumberFormArgs);
        parent::assertSame($slot, $recorded_ctrl_params['slot'] ?? null);
        parent::assertNotSame($this->slot_to - 1, $gui->getNumberFormArgs[1]);

        $recorded_ctrl_params = [];
        $this->inject($gui, 'book_request', $this->createRequest([
            'slot' => $slot,
            'object_id' => (string) $object_id,
        ]));

        $gui->processNumberForm();

        parent::assertSame($slot, $recorded_ctrl_params['slot'] ?? null);
        parent::assertNotSame($this->slot_from . '_' . ($this->slot_to - 1), $recorded_ctrl_params['slot'] ?? null);

        $this->inject($gui, 'book_request', $this->createRequest([
            'slot' => $slot,
            'object_id' => (string) $object_id,
            'nr' => '1',
            'recurrence' => '0',
        ]));

        $gui->exposeBookAvailableItems();
    }

    private function createRequest(array $get): StandardGUIRequest
    {
        return new StandardGUIRequest(
            parent::createMock(Services::class),
            new RefineryFactory(new Factory(), parent::createMock(ilLanguage::class)),
            $get,
            []
        );
    }

    private function inject(object $object, string $property, mixed $value): void
    {
        (new ReflectionProperty($object, $property))->setValue($object, $value);
    }
}

/**
 * @internal
 */
final class ilBookingProcessWithScheduleGUIUnderTest extends ilBookingProcessWithScheduleGUI
{
    /** @var array{0: int, 1: int}|null */
    public ?array $getNumberFormArgs = null;

    public ?FormAdapterGUI $stubForm = null;

    public function __construct()
    {
    }

    protected function getNumberForm(int $from, int $to): FormAdapterGUI
    {
        $this->getNumberFormArgs = [$from, $to];
        $this->reservation->getAvailableNr($this->book_request->getObjectId(), $from, $to);
        $this->ctrl->setParameter($this, 'slot', $from . '_' . $to);

        return $this->stubForm ?? $this->createMock(FormAdapterGUI::class);
    }

    protected function getBookgingObjectTitle(): string
    {
        return 'Test booking object';
    }

    public function exposeBookAvailableItems(): void
    {
        $this->bookAvailableItems();
    }
}
