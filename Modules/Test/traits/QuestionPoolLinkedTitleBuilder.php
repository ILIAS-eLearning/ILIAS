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

namespace ILIAS\Modules\Test\traits;

use ilObject;
use ilObjQuestionPoolGUI;

trait QuestionPoolLinkedTitleBuilder
{
    public function buildPossiblyLinkedQuestonPoolTitle(int $qpl_id, string $title, bool $reference = false): string
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_factory = $DIC->ui()->factory();
        $this->lng = $DIC->language();

        if (ilObject::_lookupType($qpl_id, $reference) === 'qpl') {
            if ($reference && $this->access->checkAccess('read', '', $qpl_id)) {
                $qpl_ref_id = $qpl_id;
            } else {
                $qpl_ref_id = $this->getFirstQuestionPoolReferenceWithCurrentUserAccess(
                    ilObject::_getAllReferences($qpl_id)
                );
            }
            if ($qpl_ref_id === null) {
                return $title . ' (' . $this->lng->txt('status_no_permission') . ')';
            } else {
                return $this->getLinkedQuestonPoolTitle($qpl_ref_id, $title);
            }
        }
        return $title;
    }

    private function getLinkedQuestonPoolTitle(int $qpl_ref_id, string $title): string
    {
        $this->ctrl->setParameterByClass(ilObjQuestionPoolGUI::class, 'ref_id', $qpl_ref_id);
        $title = $this->ui_renderer->render(
            $this->ui_factory->link()->standard(
                $title,
                $this->ctrl->getLinkTargetByClass(
                    [ilObjQuestionPoolGUI::class]
                )
            )
        );
        $this->ctrl->clearParametersByClass(ilObjQuestionPoolGUI::class);
        return $title;
    }

    private function getFirstQuestionPoolReferenceWithCurrentUserAccess(array $all_qpl_ref_ids): ?int
    {
        $qpl_references_with_access = array_filter(array_values($all_qpl_ref_ids), function ($ref_id) {
            return $this->access->checkAccess('read', '', $ref_id);
        });
        if ($qpl_references_with_access !== []) {
            return $qpl_references_with_access[0];
        }
        return null;
    }
}
