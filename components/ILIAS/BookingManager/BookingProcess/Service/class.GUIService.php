<?php declare(strict_types = 1);

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

namespace ILIAS\BookingManager\BookingProcess;

use ILIAS\BookingManager\InternalDataService;
use ILIAS\BookingManager\InternalDomainService;
use ILIAS\BookingManager\InternalGUIService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;
    protected InternalGUIService $gui_service;

    public function __construct(
        InternalDataService $data_service,
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->gui_service = $gui_service;
    }

    public function ProcessUtilGUI(\ilObjBookingPool $pool, object $parent_gui) : ProcessUtilGUI
    {
        return new ProcessUtilGUI(
            $this->domain_service,
            $this->gui_service,
            $pool,
            $parent_gui
        );
    }

    public function ilBookingProcessWithScheduleGUI(
        \ilObjBookingPool $pool,
        int $book_obj_id,
        int $context_obj_id,
        string $seed
    ) : \ilBookingProcessWithScheduleGUI
    {
        return new \ilBookingProcessWithScheduleGUI(
            $pool,
            $book_obj_id,
            $seed,
            $context_obj_id
        );
    }

    public function ilBookingProcessWithoutScheduleGUI(
        \ilObjBookingPool $pool,
        int $book_obj_id,
        int $context_obj_id
    ) : \ilBookingProcessWithoutScheduleGUI
    {
        return new \ilBookingProcessWithoutScheduleGUI(
            $pool,
            $book_obj_id,
            $context_obj_id
        );
    }

    public function getProcessClassForPool(\ilObjBookingPool $pool) : string
    {
        return $this->getProcessClass($pool->getScheduleType() === \ilObjBookingPool::TYPE_FIX_SCHEDULE);
    }

    public function getProcessClass($with_schedule = true) : string
    {
        if ($with_schedule) {
            return \ilBookingProcessWithScheduleGUI::class;
        }
        return \ilBookingProcessWithoutScheduleGUI::class;
    }

}
