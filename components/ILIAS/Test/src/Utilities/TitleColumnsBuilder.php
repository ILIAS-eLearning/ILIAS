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

namespace ILIAS\Test\Utilities;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\Data\ReferenceId;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Link\Standard as StandardLink;

class TitleColumnsBuilder
{
    public function __construct(
        private readonly GeneralQuestionPropertiesRepository $properties_repository,
        private readonly \ilCtrl $ctrl,
        private readonly \ilAccessHandler $access,
        private readonly \ilLanguage $lng,
        private readonly StaticURLServices $static_url,
        private readonly UIFactory $ui_factory
    ) {
    }

    public function buildQuestionTitleAsLink(
        int $question_id,
        int $test_ref_id
    ): StandardLink {
        $question_title = $this->properties_repository->getForQuestionId($question_id)?->getTitle();

        if ($question_title === null) {
            return $this->ui_factory->link()->standard(
                "{$this->lng->txt('deleted')} ({$this->lng->txt('id')}: {$question_id})",
                ''
            )->withDisabled();
        }

        return $this->ui_factory->link()->standard(
            $this->properties_repository->getForQuestionId($question_id)?->getTitle(),
            $this->static_url->builder()->build(
                'tst',
                new ReferenceId($test_ref_id),
                ['qst', $question_id]
            )->__toString()
        );
    }

    public function buildQuestionTitleAsText(
        ?int $question_id
    ): string {
        if ($question_id === null) {
            return '';
        }
        $question_title = $this->properties_repository->getForQuestionId($question_id)?->getTitle();

        if ($question_title === null) {
            return "{$this->lng->txt('deleted')} ({$this->lng->txt('id')}: {$question_id})";
        }

        return $question_title;
    }

    public function buildTestTitleAsLink(int $test_ref_id): StandardLink
    {
        $test_obj_id = \ilObject::_lookupObjId($test_ref_id);
        if ($test_obj_id === 0) {
            return $this->ui_factory->link()->standard(
                "{$this->lng->txt('deleted')} ({$this->lng->txt('id')}: {$test_ref_id})",
                ''
            )->withDisabled();
        }

        if (\ilObject::_isInTrash($test_ref_id)) {
            return $this->ui_factory->link()->standard(
                "{$this->lng->txt('in_trash')} ({$this->lng->txt('title')}: "
                    . \ilObject::_lookupTitle($test_obj_id) . ", {$this->lng->txt('id')}: {$test_ref_id})",
                ''
            );
        }

        return $this->ui_factory->link()->standard(
            \ilObject::_lookupTitle($test_obj_id),
            $this->static_url->builder()->build('tst', new ReferenceId($test_ref_id))->__toString()
        );
    }

    public function buildTestTitleAsText(int $test_ref_id): string
    {
        $test_obj_id = \ilObject::_lookupObjId($test_ref_id);
        if ($test_obj_id === 0) {
            return "{$this->lng->txt('deleted')} ({$this->lng->txt('id')}: {$test_ref_id})";
        }

        return \ilObject::_lookupTitle($test_obj_id);
    }



    public function buildAccessCheckedTestTitleAsLinkForObjId(
        int $test_obj_id,
        string $title
    ): StandardLink {
        return $this->buildPossiblyLinkedTitle(
            $test_obj_id,
            $title,
            \ilObjTestGUI::class
        );
    }

    public function buildAccessCheckedQuestionpoolTitleAsLink(
        ?int $qpl_id,
        ?string $title = null,
        bool $reference = false
    ): StandardLink {
        if ($qpl_id === null) {
            return $this->ui_factory->link()->standard(
                $title ?? $this->lng->txt('tst_question_not_from_pool_info'),
                ''
            )->withDisabled();
        }

        if (\ilObject::_lookupType($qpl_id, $reference) !== 'qpl') {
            return $this->ui_factory->link()->standard(
                $this->lng->txt('tst_question_not_from_pool_info'),
                ''
            )->withDisabled();
        }

        $qpl_obj_id = $qpl_id;
        if ($reference) {
            $qpl_obj_id = \ilObject::_lookupObjId($qpl_id);
        }

        return $this->buildPossiblyLinkedTitle(
            $qpl_obj_id,
            $title ?? \ilObject::_lookupTitle($qpl_obj_id),
            \ilObjQuestionPoolGUI::class,
            $reference
        );
    }

    private function buildPossiblyLinkedTitle(
        int $obj_id,
        string $title,
        string $target_class_type,
        bool $reference = false
    ): StandardLink {
        $ref_id = $this->getFirstReferenceWithCurrentUserAccess(
            $reference,
            $obj_id,
            \ilObject::_getAllReferences($obj_id)
        );

        if ($ref_id === null) {
            return $this->ui_factory->link()->standard(
                "{$title} ({$this->lng->txt('status_no_permission')})",
                ''
            )->withDisabled();
        }

        return $this->getLinkedTitle($ref_id, $title, $target_class_type);
    }

    private function getLinkedTitle(
        int $ref_id,
        string $title,
        string $target_class_type
    ): StandardLink {
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
