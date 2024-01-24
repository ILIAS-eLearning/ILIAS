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

/**
 * @author            Nicolas Schaefli <ns@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilEmployeeTalkMyStaffUserGUI: ilMStShowUserGUI
 * @ilCtrl_IsCalledBy ilEmployeeTalkMyStaffUserGUI: ilFormPropertyDispatchGUI
 * @ilCtrl_Calls ilEmployeeTalkMyStaffUserGUI: ilObjEmployeeTalkGUI
 * @ilCtrl_Calls ilEmployeeTalkMyStaffUserGUI: ilObjEmployeeTalkSeriesGUI
 */
final class ilEmployeeTalkMyStaffUserGUI extends ilEmployeeTalkMyStaffBaseGUI
{
    private int $usr_id;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        if ($DIC->http()->wrapper()->query()->has('usr_id')) {
            $this->usr_id = $DIC->http()->wrapper()->query()->retrieve(
                'usr_id',
                $DIC->refinery()->kindlyTo()->int()
            );
            $this->ctrl->setParameter($this, 'usr_id', $this->usr_id);
        }
    }

    protected function hasCurrentUserAccess(): bool
    {
        if (!$this->usr_id) {
            return false;
        }

        if (
            !$this->access->hasCurrentUserAccessToTalks() ||
            !$this->access->hasCurrentUserAccessToUser($this->usr_id)
        ) {
            return false;
        }

        return true;
    }

    public function getClassPath(): array
    {
        return [
            strtolower(ilDashboardGUI::class),
            strtolower(ilMyStaffGUI::class),
            strtolower(ilMStShowUserGUI::class),
            strtolower(ilEmployeeTalkMyStaffUserGUI::class)
        ];
    }

    protected function loadHeader(): void
    {
        // header is set by MyStaff
    }

    protected function loadTabs(): void
    {
        // tabs are set by MyStaff
    }

    protected function loadTalkData(): array
    {
        $talks = [];
        if ($this->talk_access->hasPermissionToReadUnownedTalksOfUser($this->usr_id)) {
            $talks = $this->repository->findByEmployee($this->usr_id);
        } else {
            $talks = $this->repository->findTalksBetweenEmployeeAndOwner(
                $this->usr_id,
                $this->current_user->getId()
            );
        }
        return $talks;
    }
}
