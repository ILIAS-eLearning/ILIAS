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

use ILIAS\Services\Dashboard\Block\BlockDTO;

class ilMembershipBlockGUI extends ilDashboardBlockGUI
{
    public function initViewSettings(): void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_MY_MEMBERSHIPS
        );

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function emptyHandling(): string
    {
        $this->lng->loadLanguageModule('rep');
        return $this->renderer->render(
            $this->factory->panel()->standard(
                $this->getTitle(),
                $this->factory->legacy($this->lng->txt("rep_mo_mem_dash"))
            )
        );
    }

    public function initData(): void
    {
        $provider = new ilPDSelectedItemsBlockMembershipsProvider($this->user);
        $data = $provider->getItems();
        $data = array_map(static function (array $item): BlockDTO {
            $start = isset($item['start']) && $item['start'] instanceof ilDateTime ? $item['start'] : null;
            $end = isset($item['end']) && $item['end'] instanceof ilDateTime ? $item['end'] : null;
            return new BlockDTO(
                $item['type'],
                (int) $item['ref_id'],
                (int) $item['obj_id'],
                $item['title'],
                $item['description'],
                $start,
                $end,
            );
        }, $data);

        $this->setData(['' => $data]);
    }

    public function getBlockType(): string
    {
        return 'pdmem';
    }

    public function confirmedRemoveObject(): void
    {
        $refIds = (array) ($this->http->request()->getParsedBody()['ref_id'] ?? []);
        if ($refIds === []) {
            $this->ctrl->redirect($this, 'manage');
        }

        foreach ($refIds as $ref_id) {
            if ($this->access->checkAccess('leave', '', (int) $ref_id)) {
                switch (ilObject::_lookupType((int) $ref_id, true)) {
                    case 'crs':
                        $members = new ilCourseParticipants(ilObject::_lookupObjId((int) $ref_id));
                        $members->delete($this->user->getId());

                        $members->sendUnsubscribeNotificationToAdmins($this->user->getId());
                        $members->sendNotification(
                            ilCourseMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER,
                            $this->user->getId()
                        );
                        break;

                    case 'grp':
                        $members = new ilGroupParticipants(ilObject::_lookupObjId((int) $ref_id));
                        $members->delete($this->user->getId());

                        $members->sendNotification(
                            ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER,
                            $this->user->getId()
                        );
                        $members->sendNotification(
                            ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE,
                            $this->user->getId()
                        );
                        break;
                    default:
                        continue 2;
                }

                ilForumNotification::checkForumsExistsDelete((int) $ref_id, $this->user->getId());
            }
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('mmbr_unsubscribed_from_objs'), true);
        $this->ctrl->returnToParent($this);
    }

    public function removeMultipleEnabled(): bool
    {
        return true;
    }

    public function getRemoveMultipleActionText(): string
    {
        return $this->lng->txt('pd_unsubscribe_multiple_memberships');
    }
}
