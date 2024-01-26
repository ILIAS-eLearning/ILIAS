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
    public function buildPossiblyLinkedTestTitle(
        \ilCtrl $ctrl,
        \ilAccessHandler $access,
        \ilLanguage $lng,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        int $test_id,
        string $title
    ) : string {
        return $this->buildPossiblyLinkedTitle(
            $ctrl,
            $access,
            $lng,
            $ui_factory,
            $ui_renderer,
            $test_id,
            $title,
            \ilObjTestGUI::class
        );
    }

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

        return $this->buildPossiblyLinkedTitle(
            $ctrl,
            $access,
            $lng,
            $ui_factory,
            $ui_renderer,
            $qpl_obj_id,
            $title,
            \ilObjQuestionPoolGUI::class,
            $reference
        );
    }

    private function buildPossiblyLinkedTitle(
        \ilCtrl $ctrl,
        \ilAccessHandler $access,
        \ilLanguage $lng,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        int $obj_id,
        string $title,
        string $target_class_type,
        bool $reference = false
    ) : string {
        $ref_id = $this->getFirstReferenceWithCurrentUserAccess(
            $access,
            $reference,
            $obj_id,
            ilObject::_getAllReferences($obj_id)
        );

        if ($ref_id === null) {
            return $title . ' (' . $lng->txt('status_no_permission') . ')';
        }

        return $ui_renderer->render(
            $this->getLinkedTitle($ctrl, $ui_factory, $ref_id, $title, $target_class_type)
        );
    }

    private function getLinkedTitle(
        \ilCtrl $ctrl,
        UIFactory $ui_factory,
        int $ref_id,
        string $title,
        string $target_class_type
    ) : Link {
        $linked_title = $ui_factory->link()->standard(
            $title,
            \ilLink::_getStaticLink($ref_id)
        );
        return $linked_title;
    }

    private function getFirstReferenceWithCurrentUserAccess(
        \ilAccessHandler $access,
        bool $reference,
        int $obj_id,
        array $all_ref_ids
    ) : ?int {
        if ($reference && $access->checkAccess('read', '', $obj_id)) {
            return $obj_id;
        }

        $references_with_access = array_filter(
            array_values($all_ref_ids),
            function ($ref_id) use ($access) {
                return $access->checkAccess('read', '', $ref_id);
            }
        );
        if ($references_with_access !== []) {
            return (int) array_shift($references_with_access);
        }
        return null;
    }
}
