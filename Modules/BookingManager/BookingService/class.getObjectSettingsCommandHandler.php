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

namespace ILIAS\BookingManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class getObjectSettingsCommandHandler
{
    protected getObjectSettingsCommand $cmd;
    protected \ilObjUseBookDBRepository $use_book_repo;

    public function __construct(
        getObjectSettingsCommand $cmd,
        \ilObjUseBookDBRepository $use_book_repo
    ) {
        $this->cmd = $cmd;
        $this->use_book_repo = $use_book_repo;
    }

    public function handle() : getObjectSettingsResponse
    {
        $obj_id = $this->cmd->getObjectId();
        $repo = $this->use_book_repo;

        $used_book_ids = $repo->getUsedBookingPools($obj_id);

        return new getObjectSettingsResponse(new \ilObjBookingServiceSettings($obj_id, $used_book_ids));
    }
}
