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
 *
 * @ilCtrl_IsCalledBy ilLearningSequenceBlockGUI: ilColumnGUI
 * @ilCtrl_Calls      ilLearningSequenceBlockGUI: ilCommonActionDispatcherGUI
 */
class ilLearningSequenceBlockGUI extends ilDashboardBlockGUI
{
    protected function getIcon(string $title): \ILIAS\UI\Component\Symbol\Icon\Standard
    {
        if (!isset($this->icon) || is_null($this->icon)) {
            $this->icon = $this->factory->symbol()->icon()->standard(
                'lso',
                $title,
                'medium'
            );
        }

        return $this->icon;
    }

    public function emptyHandling(): string
    {
        return '';
    }

    public function initViewSettings(): void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_LEARNING_SEQUENCES
        );

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function initData(): void
    {
        $data = [];
        $assignments = ilParticipants::_getMembershipByType($this->user->getId(), ['lso']);
        foreach ($assignments as $assignment) {
            $ref_ids = ilObject::_getAllReferences($assignment);
            $lso_ref_id = array_shift($ref_ids);

            /** @var ilObjLearningSequence $lso_obj */
            $lso_obj = ilObjLearningSequence::getInstanceByRefId($lso_ref_id);

            if (!$lso_obj) {
                continue;
            }

            if (!$this->isRelevantLso($lso_obj)) {
                continue;
            }

            if (!$this->access->checkAccess('read', '', $lso_ref_id)) {
                continue;
            }

            $data[] = new ilBlockDTO(
                'lso',
                $lso_ref_id,
                $lso_obj->getId(),
                $lso_obj->getDescription(),
                $lso_obj->getTitle(),
            );
        }

        $this->setData(['' => $data]);
    }

    protected function isRelevantLso(ilObjLearningSequence $obj): bool
    {
        $relevant = false;

        $ls_lp_items = $obj->getLSLearnerItems($this->user->getId());
        if ($ls_lp_items === []) {
            return $relevant;
        }

        foreach ($ls_lp_items as $item) {
            if ($item->getLearningProgressStatus() === ilLPStatus::LP_STATUS_IN_PROGRESS_NUM) {
                return true;
            }
        }

        return $relevant;
    }

    public function getBlockType(): string
    {
        return 'pdlern';
    }

    public function addCustomCommandsToActionMenu(ilObjectListGUI $itemListGui, int $ref_id): void
    {
        return;
    }

    public function confirmedRemoveObject(): void
    {
        $refIds = (array) ($this->http->request()->getParsedBody()['ref_id'] ?? []);
        if (0 === count($refIds)) {
            $this->ctrl->redirect($this, 'manage');
        }

        foreach ($refIds as $ref_id) {
            if ($this->access->checkAccess('leave', '', (int) $ref_id)) {
                if (ilObject::_lookupType((int) $ref_id, true) === 'lso') {
                    $lso = ilObjLearningSequence::getInstanceByRefId((int) $ref_id);
                    if ($lso instanceof ilObjLearningSequence) {
                        $lso->getLSRoles()->leave($this->user->getId());
                    }
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
