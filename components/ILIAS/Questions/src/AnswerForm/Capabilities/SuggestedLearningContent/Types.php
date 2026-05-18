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

namespace ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent;

use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Language\Language;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Link\Factory as LinkFactory;
use ILIAS\UI\Component\Link\Standard as StandardLink;
use ILIAS\UI\Factory as UIFactory;

enum Types: string
{
    case None = 'none';
    case LearningModule = 'lm';
    case LearningModuleChapter = 'st';
    case LearningModulePage = 'pg';
    case GlossaryTerm = 'git';
    case File = 'file';

    public function getTranslatedOptionName(
        Language $lng
    ): string {
        return match($this) {
            self::GlossaryTerm => $lng->txt('glossary_term'),
            default => $lng->txt($this->value)
        };
    }

    public static function buildOptionsList(
        Language $lng
    ): array {
        return array_reduce(
            self::cases(),
            function (array $c, Types $v) use ($lng): array {
                if ($v === Types::None) {
                    return $c;
                }
                $c[$v->value] = $v->getTranslatedOptionName($lng);
                return $c;
            },
            []
        );
    }

    public function present(
        Language $lng,
        \ilCtrl $ctrl,
        StaticURLServices $static_url,
        IRSS $irss,
        UIFactory $ui_factory,
        string $file_title,
        ?ResourceIdentification $rid,
        ?int $target_ref_id,
        ?int $sub_object_id
    ): ?StandardLink {
        return match($this) {
            self::File => $ui_factory->link()->standard(
                $file_title === ''
                    ? $irss->manage()->getResource($rid)->getCurrentRevision()->getTitle()
                : $file_title,
                $irss->consume()->src($rid)->getSrc(true)
            ),
            self::LearningModule => $this->buildLinkToLearningModule(
                $ctrl,
                $lng,
                $ui_factory->link(),
                $target_ref_id
            ),
            self::LearningModulePage,
            self::LearningModuleChapter,
            self::GlossaryTerm => $this->buildLinkToSubObject(
                $lng,
                $ui_factory->link(),
                $static_url,
                $target_ref_id,
                $sub_object_id
            ),
            self::None => null
        };
    }

    public function getReferencedObjectType(): string
    {
        return match ($this) {
            self::LearningModule,
            self::LearningModuleChapter,
            self::LearningModulePage => 'lm',
            self::GlossaryTerm => 'glo',
            default => ''
        };
    }

    public function hasSelectContentSubForm(): bool
    {
        return $this !== self::LearningModule && $this !== self::File;
    }

    public function buildContentInput(
        Repository $repository,
        \ilRbacSystem $rbac_system,
        \ilTree $tree,
        Environment $environment,
        int $current_user_id
    ): Section {
        return match($this) {
            self::File => $this->buildUploadFileInput(
                $repository,
                $environment,
                $current_user_id
            ),
            default => $this->buildSelectObjectInput(
                $repository,
                $rbac_system,
                $tree,
                $environment,
                $this->getReferencedObjectType()
            )
        };
    }

    public function buildSubContentInput(
        Repository $repository,
        \ilRbacSystem $rbac_system,
        Environment $environment,
        int $target_ref_id
    ): Section {
        if (!$this->hasSelectContentSubForm()) {
            throw new InvalidArgumentException(
                'This type of SuggestedLearningContent does not provide a SubContentInput.'
            );
        }

        $ff = $environment->getUIFactory()->input()->field();
        $lng = $environment->getLanguage();

        return $ff->section(
            [
                'sub_object' => $ff->select(
                    $this->getTranslatedOptionName($lng),
                    $this->buildSubContentOptions(
                        $rbac_system,
                        $target_ref_id
                    )
                )->withRequired(true)
            ],
            $lng->txt('select_target')
        )->withAdditionalTransformation(
            $environment->getRefinery()->custom()->transformation(
                fn(array $vs): Content => $repository->getNew(
                    $environment->getAnswerFormId(),
                    $this
                )->withTargetRefId($target_ref_id)
                ->withSubObjectId((int) $vs['sub_object'])
            )
        );
    }

    private function buildLinkToLearningModule(
        \ilCtrl $ctrl,
        Language $lng,
        LinkFactory $link_factory,
        int $target_ref_id
    ): StandardLink {
        $ctrl->setParameterByClass(
            \ilLMPresentationGUI::class,
            'ref_id',
            $target_ref_id
        );

        $link = $link_factory->standard(
            "{$this->getTranslatedOptionName($lng)}: {$this->lookupObjectTitle($target_ref_id)}",
            $ctrl->getLinkTargetByClass(
                [
                    \ilLMPresentationGUI::class
                ],
            )
        );
        $ctrl->clearParameterByClass(
            \ilLMPresentationGUI::class,
            'ref_id'
        );
        return $link;
    }

    private function buildLinkToSubObject(
        Language $lng,
        LinkFactory $link_factory,
        StaticURLServices $static_url,
        int $target_ref_id,
        int $sub_object_id
    ): StandardLink {
        $sub_object_title = match($this) {
            self::GlossaryTerm => (
                new \ilGlossaryTerm($sub_object_id)
            )->getTerm(),
            default => \ilLMObject::_lookupTitle(
                $sub_object_id
            )
        };

        return $link_factory->standard(
            "{$this->getTranslatedOptionName($lng)}: {$this->lookupObjectTitle($target_ref_id)} - {$sub_object_title}",
            $static_url->builder()->build(
                $this->value,
                null,
                [$sub_object_id]
            )->__toString()
        );
    }

    private function lookupObjectTitle(
        int $target_ref_id
    ): string {
        return \ilObject::_lookupTitle(
            \ilObject::_lookupObjId($target_ref_id)
        );
    }

    private function buildUploadFileInput(
        Repository $repository,
        Environment $environment,
        int $current_user_id
    ): Section {
        $ff = $environment->getUIFactory()->input()->field();
        $lng = $environment->getLanguage();

        return $ff->section(
            [
                Content::KEY_FILE_TITLE => $ff->text(
                    $lng->txt('title'),
                    $lng->txt('if_no_title_then_filename')
                ),
                Content::KEY_RID => $ff->file(
                    $environment->getPresentationFactory()->getUploadHandler(
                        $environment,
                        new Stakeholder($current_user_id)
                    ),
                    $lng->txt('file')
                )->withRequired(true)
            ],
            $lng->txt('upload_file')
        )->withAdditionalTransformation(
            $environment->getRefinery()->custom()->transformation(
                fn(array $vs): Content => $repository->getNew(
                    $environment->getAnswerFormId(),
                    $this
                )->withFileInfo($vs)
            )
        );
    }

    private function buildSelectObjectInput(
        Repository $repository,
        \ilRbacSystem $rbac_system,
        \ilTree $tree,
        Environment $environment,
        string $type
    ): Section {
        $ff = $environment->getUIFactory()->input()->field();
        $lng = $environment->getLanguage();

        $node_retrieval = new NodeRetrieval(
            $rbac_system,
            $tree,
            $environment,
            $type
        );

        return $ff->section(
            [
                'object' => $ff->treeSelect(
                    $node_retrieval,
                    $lng->txt('select')
                )->withAdditionalTransformation(
                    $node_retrieval->buildValidNodeConstraint()
                )
            ],
            $this->getTranslatedOptionName($lng)
        )->withAdditionalTransformation(
            $environment->getRefinery()->custom()->transformation(
                fn(array $vs): Content => $repository->getNew(
                    $environment->getAnswerFormId(),
                    $this
                )->withTargetRefId((int) $vs['object'][0])
            )
        );
    }

    private function buildSubContentOptions(
        \ilRbacSystem $rbac_system,
        int $target_ref_id
    ): array {
        if (!$rbac_system->checkAccess('read', $target_ref_id)) {
            return [];
        }

        return match ($this) {
            self::GlossaryTerm => array_reduce(
                (new \ilObjGlossary($target_ref_id))->getTermList(),
                function (array $c, array $v): array {
                    $c[$v['id']] = $v['term'];
                    return $c;
                },
                []
            ),
            self::LearningModulePage,
            self::LearningModuleChapter => array_reduce(
                \ilLMObject::getObjectList(
                    \ilObject::_lookupObjectId($target_ref_id),
                    $this === self::LearningModuleChapter
                        ? 'st'
                        : 'pg'
                ),
                function (array $c, array $v): array {
                    $c[$v['obj_id']] = $v['title'];
                    return $c;
                },
                []
            )
        };

    }
}
