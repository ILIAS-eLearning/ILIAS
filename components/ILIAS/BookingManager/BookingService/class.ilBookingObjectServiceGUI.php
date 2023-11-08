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

/**
 * Service (e.g. being used in a course) UI wrapper for booking objects
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilBookingObjectServiceGUI: ilPropertyFormGUI, ilBookingProcessWithScheduleGUI, ilBookingProcessWithoutScheduleGUI
 */
class ilBookingObjectServiceGUI extends ilBookingObjectGUI
{
    protected int $host_obj_ref_id;
    protected ilObjUseBookDBRepository $use_book_repo;

    public function __construct(
        int $host_obj_ref_id,
        int $current_pool_ref_id,
        \ilObjUseBookDBRepository $use_book_repo,
        string $seed,
        string $sseed,
        ilBookingHelpAdapter $help
    ) {
        $this->pool_gui = new ilObjBookingPoolGUI([], $current_pool_ref_id, true, false);
        parent::__construct(
            $this->pool_gui,
            $seed,
            $sseed,
            $help,
            ilObject::_lookupObjId($host_obj_ref_id)
        );
        $this->host_obj_ref_id = $host_obj_ref_id;
        $this->use_book_repo = $use_book_repo;

        $this->activateManagement(false);
    }
}
