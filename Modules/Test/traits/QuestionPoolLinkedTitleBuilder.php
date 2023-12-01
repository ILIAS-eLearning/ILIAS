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

namespace ILIAS\Modules\Test;

use ilObject;
use ilObjQuestionPoolGUI;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Link\Link;

trait QuestionPoolLinkedTitleBuilder
{
    public function buildPossiblyLinkedQuestonPoolTitle(
        \ilCtrl $ctrl,
        \ilAccessHandler $access,
        \ilLanguage $lng,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        ?int $qpl_id,
        string $title,
        bool $reference = false
    ) : string {
        if ($qpl_id === null) {
            return $title;
        }

        if (ilObject::_lookupType($qpl_id, $reference) !== 'qpl') {
            return $lng->txt('tst_question_not_from_pool_info');
        }

        $qpl_obj_id = $qpl_id;
        if ($reference) {
            $qpl_obj_id = ilObject::_lookupObjId($qpl_id);
        }

        $qpl_ref_id = $this->getFirstQuestionPoolReferenceWithCurrentUserAccess(
            $access,
            $reference,
            $qpl_id,
            ilObject::_getAllReferences($qpl_obj_id)
        );

        if ($qpl_ref_id === null) {
            return $title . ' (' . $lng->txt('status_no_permission') . ')';
        }

        return $ui_renderer->render(
            $this->getLinkedQuestonPoolTitle($ctrl, $ui_factory, $qpl_ref_id, $title)
        );
    }

    private function getLinkedQuestonPoolTitle(
        \ilCtrl $ctrl,
        UIFactory $ui_factory,
        int $qpl_ref_id,
        string $title
    ) : Link {
        $ctrl->setParameterByClass(ilObjQuestionPoolGUI::class, 'ref_id', $qpl_ref_id);
        $linked_title = $ui_factory->link()->standard(
            $title,
            $ctrl->getLinkTargetByClass(
                [ilObjQuestionPoolGUI::class]
            )
        );
        $ctrl->clearParametersByClass(ilObjQuestionPoolGUI::class);
        return $linked_title;
    }

    private function getFirstQuestionPoolReferenceWithCurrentUserAccess(
        \ilAccessHandler $access,
        bool $reference,
        int $qpl_id,
        array $all_qpl_ref_ids
    ) : ?int {
        if ($reference && $access->checkAccess('read', '', $qpl_id)) {
            return $qpl_id;
        }

        $qpl_references_with_access = array_filter(
            array_values($all_qpl_ref_ids),
            function ($ref_id) use ($access) {
                return $access->checkAccess('read', '', $ref_id);
            }
        );
        if ($qpl_references_with_access !== []) {
            return (int) array_shift($qpl_references_with_access);
        }
        return null;
    }
}
