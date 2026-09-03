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

namespace ILIAS\BookingManager\BookingProcess;

use ilBookingProcessWithScheduleGUI;
use ILIAS\BookingManager\InternalDomainService;
use ILIAS\BookingManager\InternalGUIService;
use ILIAS\BookingManager\Objects\ObjectsManager;
use ILIAS\BookingManager\StandardGUIRequest;

class ProcessUtilGUI
{
    protected ObjectsManager $objects_manager;
    protected StandardGUIRequest $request;
    protected \ilCtrl $ctrl;
    protected \ilLogger $log;
    protected \ilObjBookingPool $pool;
    protected \ilBookingHelpAdapter $help;
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;
    protected object $parent_gui;
    protected \ilLanguage $lng;
    protected \ilGlobalTemplateInterface $tpl;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service,
        \ilObjBookingPool $pool,
        object $parent_gui
    ) {
        $this->gui = $gui_service;
        $this->domain = $domain_service;
        $this->log = $domain_service->log();
        $this->help = $gui_service->bookingHelp($pool);
        $this->parent_gui = $parent_gui;
        $this->pool = $pool;
        $this->ctrl = $this->gui->ctrl();
        $this->request = $this->gui->standardRequest();
        $this->objects_manager = $domain_service->objects($pool->getId());
        $this->lng = $domain_service->lng();
        $this->tpl = $this->gui->mainTemplate();
    }

    // Back to parent
    public function back(): void
    {
        $this->log->debug("back");
        $retCmd = $this->request->getReturnCmd();
        $this->log->debug("returnCmd is " . $retCmd);

        $class = get_class($this->parent_gui);
        if ($retCmd === 'week' && $class === ilBookingProcessWithScheduleGUI::class) {
            if ($this->request->getOriginCmd() === 'book') {
                $this->domain->objectSelection($this->pool->getId())->setSelectedObjects([$this->request->getObjectId()]);
            }
            $this->ctrl->setParameterByClass($class, 'seed', $this->request->getSeed());
            $this->ctrl->redirectByClass($class, $retCmd);
            return;
        }

        if ($retCmd === 'render') {
            $this->redirectToRender();
            return;
        }

        if ($retCmd !== "") {
            $this->ctrl->redirectByClass(get_class($this->parent_gui), $retCmd);
        } else {
            //var_dump(get_class($this->parent_gui));
            //exit;
            $this->ctrl->returnToParent($this->parent_gui);
        }
    }

    public function setHelpId(string $a_id): void
    {
        $this->help->setHelpId($a_id);
    }

    // Table to assign participants to an object.
    public function assignParticipants(int $book_obj_id): void
    {
        $tabs = $this->gui->tabs();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $main_tpl = $this->gui->mainTemplate();

        $tabs->clearTargets();
        $tabs->setBackTarget($lng->txt('book_back_to_list'), $ctrl->getLinkTarget($this->parent_gui, 'back'));

        $table = new \ilBookingAssignParticipantsTableGUI($this->parent_gui, 'assignParticipants', $this->pool->getRefId(), $this->pool->getId(), $book_obj_id);
        $main_tpl->setContent($table->getHTML());
    }


    /*
    public function checkPermissionBool(string $a_perm) : bool
    {
        $access = $this->domain->access();
        if (!$access->checkAccess($a_perm, "", $this->pool->getRefId())) {
            return false;
        }
        return true;
    }

    protected function checkPermission(string $a_perm) : void
    {
        $main_tpl = $this->gui->mainTemplate();
        $lng = $this->domain->lng();

        if (!$this->checkPermissionBool($a_perm)) {
            $main_tpl->setOnScreenMessage('failure', $lng->txt("no_permission"), true);
            $this->back();
        }
    }*/

    public function handleBookingSuccess(
        int $a_obj_id,
        string $post_info_cmd,
        ?array $a_rsv_ids = null,
        ?string $message = null
    ): void {
        $this->log->debug('handleBookingSuccess');
        $this->tpl->setOnScreenMessage(
            'success',
            $message ?: $this->lng->txt('book_reservation_confirmed'),
            true
        );

        $this->redirectToReturnCmdIfAsynchronous();
        $this->redirectToBookingInformationIfAvailable($a_obj_id, $post_info_cmd, $a_rsv_ids);
        $this->redirectToRender();
    }

    public function redirectToBookingInformationIfAvailable(int $a_obj_id, string $post_info_cmd, ?array $a_rsv_ids = null): void
    {
        $obj = new \ilBookingObject($a_obj_id);
        if (trim($obj->getPostText()) === '' && $this->objects_manager->getBookingInfoFilename($a_obj_id) === '') {
            return;
        }

        $a_rsv_ids ??= [];
        if ($a_rsv_ids !== []) {
            $this->ctrl->setParameter($this->parent_gui, 'rsv_ids', implode(';', $a_rsv_ids));
        }
        $this->ctrl->redirect($this->parent_gui, $post_info_cmd);
    }

    private function redirectToReturnCmdIfAsynchronous(): void
    {
        if (!$this->ctrl->isAsynch()) {
            return;
        }

        $this->gui->send(
            "<script>window.location.href = '{$this->ctrl->getLinkTarget($this->parent_gui, $this->request->getReturnCmd())}';</script>"
        );
    }

    private function redirectToRender(): void
    {
        $this->ctrl->saveParameterByClass(\ilObjBookingPoolGUI::class, 'ref_id');
        $this->ctrl->redirectByClass(\ilObjBookingPoolGUI::class, 'render');
    }

    /**
     * Display post booking informations
     */
    public function displayPostInfo(
        int $book_obj_id,
        int $user_id,
        string $file_deliver_cmd
    ): void {
        $this->log->debug('displayPostInfo');
        $booking_ids = $this->request->getReservationIdsFromString();

        $objects_with_periods = $this->resolveObjectsWithPeriodsForPostInfo(
            $book_obj_id,
            $user_id,
            $booking_ids
        );

        if ($objects_with_periods === []) {
            $this->ctrl->redirect($this->parent_gui, 'back');
            return;
        }

        $booking_objects = [];
        $booking_info_filenames = [];

        $objects_with_info = [];
        foreach ($objects_with_periods as $object_id => $periods) {
            $booking_objects[$object_id] = new \ilBookingObject($object_id);
            $booking_info_filenames[$object_id] = trim($this->objects_manager->getBookingInfoFilename($object_id));

            if (
                trim($booking_objects[$object_id]->getPostText()) === ''
                && $booking_info_filenames[$object_id] === ''
            ) {
                continue;
            }

            $objects_with_info[$object_id] = $periods;
        }

        if ($objects_with_info === []) {
            $this->ctrl->redirect($this->parent_gui, 'back');
            return;
        }

        $template = new \ilTemplate(
            'tpl.booking_reservation_post.html',
            true,
            true,
            'components/ILIAS/BookingManager/BookingProcess'
        );
        $multiple_objects = count($objects_with_info) > 1;
        $submit_url = $this->ctrl->getLinkTarget($this->parent_gui, 'back');

        foreach ($objects_with_info as $object_id => $periods) {
            $booking_object = $booking_objects[$object_id];
            $post_text = trim($booking_object->getPostText());

            $template->setCurrentBlock('info_block');
            $title = $multiple_objects
                ? sprintf($this->lng->txt('book_post_booking_information_for'), $booking_object->getTitle())
                : $this->lng->txt('book_post_booking_information');
            $template->setVariable('TITLE', $title);

            if ($post_text !== '') {
                $post_text = str_replace(
                    ['[OBJECT]', '[PERIOD]'],
                    [$booking_object->getTitle(), implode('<br />', $periods)],
                    $post_text
                );
                $template->setVariable('POST_TEXT', nl2br($post_text));
            }

            if ($booking_info_filenames[$object_id] === '') {
                $template->parseCurrentBlock();
                continue;
            }

            $this->ctrl->setParameter($this->parent_gui, 'object_id', $object_id);
            $url = $this->ctrl->getLinkTarget($this->parent_gui, $file_deliver_cmd);
            $this->ctrl->clearParameterByClass($this->parent_gui::class, 'object_id');

            $template->setCurrentBlock('download');
            $template->setVariable('DOWNLOAD', $this->lng->txt('download'));
            $template->setVariable('URL_FILE', $url);
            $template->setVariable('TXT_FILE', $booking_info_filenames[$object_id]);
            $template->parseCurrentBlock();
            $template->setCurrentBlock('info_block');

            $template->parseCurrentBlock();
        }

        $template->setCurrentBlock('submit');
        $template->setVariable('TXT_SUBMIT', $this->lng->txt('ok'));
        $template->setVariable('URL_SUBMIT', $submit_url);
        $template->parseCurrentBlock();

        $this->tpl->setContent($template->get());
    }

    /**
     * @return array<int, string[]>
     */
    protected function resolveObjectsWithPeriodsForPostInfo(
        int $book_obj_id,
        int $user_id,
        array $booking_ids
    ): array {
        if ($booking_ids !== []) {
            return $this->resolvePeriodsFromReservationIds($booking_ids);
        }

        if ($book_obj_id <= 0) {
            return [];
        }

        $period_slots = [];
        if ($this->request->getReservationId() !== '') {
            $reservation_id_parts = explode('_', $this->request->getReservationId());
            $user_id = (int) ($reservation_id_parts[1] ?? 0);
            $from = (int) ($reservation_id_parts[2] ?? 0);

            if ($from > time()) {
                $to = (int) ($reservation_id_parts[3] ?? 0);
                $period_slots["$from-$to"] = 1;
            }
            $booking_ids = [0];
        }

        $book_ids = \ilBookingReservation::getObjectReservationForUser($book_obj_id, $user_id);
        foreach ($book_ids as $book_id) {
            if (!in_array($book_id, $booking_ids, true) && $booking_ids !== []) {
                continue;
            }

            $reservation = new \ilBookingReservation($book_id);
            $from = $reservation->getFrom();
            $to = $reservation->getTo();
            if ($from > time()) {
                $key = "$from-$to";
                $period_slots[$key] = ($period_slots[$key] ?? 0) + 1;
            }
        }

        return $period_slots === []
            ? []
            : [$book_obj_id => $this->formatPeriodSlots($period_slots)];
    }

    /**
     * @param string[] $booking_ids
     * @return array<int, string[]>
     */
    protected function resolvePeriodsFromReservationIds(array $booking_ids): array
    {
        $period_slots_by_object = [];

        foreach ($booking_ids as $booking_id) {
            $booking_id = (int) $booking_id;
            if ($booking_id <= 0) {
                continue;
            }

            $reservation = new \ilBookingReservation($booking_id);
            $object_id = $reservation->getObjectId();
            $key = "{$reservation->getFrom()}-{$reservation->getTo()}";
            $period_slots_by_object[$object_id][$key] = ($period_slots_by_object[$object_id][$key] ?? 0) + 1;
        }

        $objects_with_periods = [];
        foreach ($period_slots_by_object as $object_id => $period_slots) {
            $objects_with_periods[$object_id] = $this->formatPeriodSlots($period_slots);
        }

        ksort($objects_with_periods);

        return $objects_with_periods;
    }

    /**
     * @param array<string, int> $period_slots
     * @return string[]
     */
    protected function formatPeriodSlots(array $period_slots): array
    {
        $olddt = \ilDatePresentation::useRelativeDates();
        \ilDatePresentation::setUseRelativeDates(false);

        $period = [];
        ksort($period_slots);
        foreach ($period_slots as $time => $counter) {
            $time_parts = explode('-', $time);
            $formatted = \ilDatePresentation::formatPeriod(
                new \ilDateTime((int) $time_parts[0], IL_CAL_UNIX),
                new \ilDateTime((int) $time_parts[1], IL_CAL_UNIX)
            );

            if ($counter > 1) {
                $formatted .= " ($counter)";
            }

            $period[] = $formatted;
        }

        \ilDatePresentation::setUseRelativeDates($olddt);

        return $period;
    }

    /**
     * Deliver post booking file
     */
    public function deliverPostFile(
        int $book_obj_id,
        int $user_id
    ): void {
        $this->log->debug("deliverPostFile");
        $id = $book_obj_id;
        if (!$id) {
            return;
        }

        $book_ids = \ilBookingReservation::getObjectReservationForUser($id, $user_id);
        $book_id = current($book_ids);
        $obj = new \ilBookingReservation($book_id);
        if ($obj->getUserId() !== $user_id) {
            return;
        }

        $this->domain->objects($this->pool->getId())->deliverBookingInfo($id);
    }

}
