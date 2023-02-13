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

use ILIAS\UI\Component\MessageBox\MessageBox;

/**
 *
 * @ilCtrl_IsCalledBy ilLearningSequenceBlockGUI: ilColumnGUI
 * @ilCtrl_Calls      ilLearningSequenceBlockGUI: ilCommonActionDispatcherGUI
 */
class ilLearningSequenceBlockGUI extends ilDashboardBlockGUI
{
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

            if (!$this->access->checkAccess('read', '', $lso_ref_id)) {
                continue;
            }

            if (!$this->isRelevantLso($lso_obj)) {
                continue;
            }

            $data[] = [
                'title' => $lso_obj->getTitle(),
                'description' => $lso_obj->getDescription(),
                'ref_id' => $lso_ref_id,
                'obj_id' => $lso_obj->getId(),
                'url' => '',
                'obj' => $lso_obj,
                'type' => 'lso',
                'start' => null,
                'end' => null,
            ];
        }

        $this->setData(['' => $data]);
    }

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

    public function getBlockType(): string
    {
        return 'pdlern';
    }
}
