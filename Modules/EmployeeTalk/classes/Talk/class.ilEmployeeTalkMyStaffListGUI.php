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

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\Modules\EmployeeTalk\Talk\DAO\EmployeeTalk;
use ILIAS\Modules\EmployeeTalk\Talk\EmployeeTalkPositionAccessLevel;

/**
 * @ilCtrl_IsCalledBy ilEmployeeTalkMyStaffListGUI: ilMyStaffGUI
 * @ilCtrl_IsCalledBy ilEmployeeTalkMyStaffListGUI: ilFormPropertyDispatchGUI
 * @ilCtrl_Calls ilEmployeeTalkMyStaffListGUI: ilObjEmployeeTalkGUI
 * @ilCtrl_Calls ilEmployeeTalkMyStaffListGUI: ilObjEmployeeTalkSeriesGUI
 */
final class ilEmployeeTalkMyStaffListGUI extends ilEmployeeTalkMyStaffBaseGUI
{
    public function getClassPath(): array
    {
        return [
            strtolower(ilDashboardGUI::class),
            strtolower(ilMyStaffGUI::class),
            strtolower(ilEmployeeTalkMyStaffListGUI::class)
        ];
    }

    protected function hasCurrentUserAccess(): bool
    {
        return $this->access->hasCurrentUserAccessToTalks();
    }

    protected function loadHeader(): void
    {
        $this->ui->mainTemplate()->setTitle($this->language->txt('mm_org_etal'));
        $this->ui->mainTemplate()->setTitleIcon(ilUtil::getImagePath('standard/icon_etal.svg'));
    }

    protected function loadTabs(): void
    {
        $this->tabs->addTab("view_content", "Content", "#");
        $this->tabs->activateTab("view_content");
        $this->tabs->setForcePresentationOfSingleTab(true);
    }

    protected function loadTalkData(): array
    {
        /**
         * @var EmployeeTalk[] $talks
         */
        $talks = [];
        if ($this->current_user->getId() === 6) {
            $talks = $this->repository->findAll();
        } else {
            $users = $this->getEmployeeIdsWithValidPermissionRights($this->current_user->getId());
            $talks = $this->repository->findByUserOrTheirEmployees($this->current_user->getId(), $users);
        }
        return $talks;
    }

    private function getEmployeeIdsWithValidPermissionRights(int $userId): array
    {
        $myStaffAccess = ilMyStaffAccess::getInstance();
        //The user has always access to his own talks
        $managedUsers = [$userId];

        /**
         * @var Array<int, Array<string>> $managedOrgUnitUsersOfUserByPosition
         */
        $managedOrgUnitUsersOfUserByPosition = $myStaffAccess->getUsersForUserPerPosition($userId);

        foreach ($managedOrgUnitUsersOfUserByPosition as $position => $managedOrgUnitUserByPosition) {
            // Check if the position has any relevant position rights
            $permissionSet = ilOrgUnitPermissionQueries::getTemplateSetForContextName(ilObjEmployeeTalk::TYPE, strval($position));
            $isAbleToExecuteOperation = array_reduce($permissionSet->getOperations(), function (bool $prev, ilOrgUnitOperation $it) {
                return $prev || $it->getOperationString() === EmployeeTalkPositionAccessLevel::VIEW;
            }, false);

            if (!$isAbleToExecuteOperation) {
                continue;
            }

            foreach ($managedOrgUnitUserByPosition as $managedOrgUnitUser) {
                $managedUsers[] = intval($managedOrgUnitUser);
            }
        }

        $managedUsers = array_unique($managedUsers, SORT_NUMERIC);

        return $managedUsers;
    }
}
