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

enum Types: string
{
    case None = 'no_content';
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

    public function present(
        \ilCtrl $ctrl,
        StaticURLServices $static_url,
        IRSS $irss,
        Environment $environment,
        string $file_title,
        ?ResourceIdentification $rid,
        ?int $target_ref_id,
        ?int $sub_object_id
    ): ?StandardLink {
        $lf = $environment->getUIFactory()->link();
        $lng = $environment->getLanguage();
        return match($this) {
            self::File => $lf->standard(
                $file_title === ''
                    ? $irss->manage()->getResource($rid)->getCurrentRevision()->getTitle()
                : $file_title,
                $irss->consume()->src($rid)->getSrc(true)
            ),
            self::LearningModule => $this->buildLinkToLearningModule(
                $ctrl,
                $lng,
                $lf,
                $target_ref_id
            ),
            self::LearningModulePage,
            self::LearningModuleChapter,
            self::GlossaryTerm => $lf->standard(
                $lng->txt('show'),
                $static_url->builder()->build(
                    $this->value,
                    null,
                    [$sub_object_id]
                )->__toString()
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
            $lng->txt('show'),
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
            $environment->getLanguage()->txt('select_object')
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
