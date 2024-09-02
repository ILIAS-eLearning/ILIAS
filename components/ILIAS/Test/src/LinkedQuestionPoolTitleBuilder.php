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

namespace ILIAS\Test;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Link\Link;

class LinkedQuestionPoolTitleBuilder
{
    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly \ilAccessHandler $access,
        private readonly \ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer
    ) {
        ;
    }
    public function buildPossiblyLinkedTestTitle(
        int $test_id,
        string $title
    ): string {
        return $this->buildPossiblyLinkedTitle(
            $test_id,
            $title,
            \ilObjTestGUI::class
        );
    }

    public function buildPossiblyLinkedQuestionPoolTitle(
        ?int $qpl_id,
        string $title = null,
        bool $reference = false
    ): string {
        if ($qpl_id === null) {
            return $title ?? $this->lng->txt('tst_question_not_from_pool_info');
        }

        if ($title === null) {
            $title = \ilObject::_lookupTitle($qpl_id);
        }

        if (\ilObject::_lookupType($qpl_id, $reference) !== 'qpl') {
            return $this->lng->txt('tst_question_not_from_pool_info');
        }

        $qpl_obj_id = $qpl_id;
        if ($reference) {
            $qpl_obj_id = \ilObject::_lookupObjId($qpl_id);
        }

        return $this->buildPossiblyLinkedTitle(
            $qpl_obj_id,
            $title,
            \ilObjQuestionPoolGUI::class,
            $reference
        );
    }

    private function buildPossiblyLinkedTitle(
        int $obj_id,
        string $title,
        string $target_class_type,
        bool $reference = false
    ): string {
        $ref_id = $this->getFirstReferenceWithCurrentUserAccess(
            $reference,
            $obj_id,
            \ilObject::_getAllReferences($obj_id)
        );

        if ($ref_id === null) {
            return $title . ' (' . $this->lng->txt('status_no_permission') . ')';
        }

        return $this->ui_renderer->render(
            $this->getLinkedTitle($ref_id, $title, $target_class_type)
        );
    }

    private function getLinkedTitle(
        int $ref_id,
        string $title,
        string $target_class_type
    ): Link {
        $this->ctrl->setParameterByClass($target_class_type, 'ref_id', $ref_id);
        $linked_title = $this->ui_factory->link()->standard(
            $title,
            $this->ctrl->getLinkTargetByClass(
                [$target_class_type]
            )
        );
        $this->ctrl->clearParametersByClass($target_class_type);
        return $linked_title;
    }

    private function getFirstReferenceWithCurrentUserAccess(
        bool $reference,
        int $obj_id,
        array $all_ref_ids
    ): ?int {
        if ($reference && $this->access->checkAccess('read', '', $obj_id)) {
            return $obj_id;
        }

        $references_with_access = array_filter(
            array_values($all_ref_ids),
            function (int $ref_id): bool {
                return $this->access->checkAccess('read', '', $ref_id);
            }
        );
        if ($references_with_access !== []) {
            return array_shift($references_with_access);
        }
        return null;
    }
}
