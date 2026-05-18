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

use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\Component\Link\Standard as StandardLink;
use ILIAS\UI\Factory as UIFactory;

class Content
{
    public const string KEY_TARGET_REF_ID = 'target_ref_id';
    public const string KEY_SUB_OBJECT_ID = 'sub_object_id';
    public const string KEY_RID = 'rid';
    public const string KEY_FILE_TITLE = 'file_title';

    private ?int $target_ref_id;
    private ?int $sub_object_id;
    private ?ResourceIdentification $rid;
    private string $file_title;

    public function __construct(
        private readonly IRSS $irss,
        private readonly Uuid $answer_form_id,
        private Types $type,
        string $content
    ) {
        $decoded_content = json_decode($content, true);
        $this->target_ref_id = $decoded_content[self::KEY_TARGET_REF_ID] ?? null;
        $this->sub_object_id = $decoded_content[self::KEY_SUB_OBJECT_ID] ?? null;
        $this->rid = $this->irss->manage()->find(
            $decoded_content[self::KEY_RID] ?? ''
        );
        $this->file_title = $decoded_content[self::KEY_FILE_TITLE] ?? '';

    }

    public function getAnswerFormId(): Uuid
    {
        return $this->answer_form_id;
    }

    public function getType(): Types
    {
        return $this->type;
    }

    public function withType(
        Types $type
    ): self {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function getTargetRefId(): ?int
    {
        return $this->target_ref_id;
    }

    public function withTargetRefId(
        int $target_ref_id
    ): self {
        $clone = clone $this;
        $clone->target_ref_id = $target_ref_id;
        return $clone;
    }

    public function withSubObjectId(
        int $sub_object_id
    ): self {
        $clone = clone $this;
        $clone->sub_object_id = $sub_object_id;
        return $clone;
    }

    public function withFileInfo(
        array $file_info
    ): self {
        $clone = clone $this;
        $clone->rid = $this->irss->manage()->find($file_info[self::KEY_RID][0]);
        $clone->file_title = $file_info[self::KEY_FILE_TITLE];
        return $clone;
    }

    public function getContentForPresentation(
        Language $lng,
        \ilCtrl $ctrl,
        StaticURLServices $static_url,
        UIFactory $ui_factory
    ): ?StandardLink {
        return $this->type->present(
            $lng,
            $ctrl,
            $static_url,
            $this->irss,
            $ui_factory,
            $this->file_title,
            $this->rid,
            $this->target_ref_id,
            $this->sub_object_id
        );
    }

    public function getListing(
        \ilCtrl $ctrl,
        StaticURLServices $static_url,
        Environment $environment
    ): array {
        $lng = $environment->getLanguage();

        return [
            $lng->txt('type') => $this->type->getTranslatedOptionName($lng),
            $lng->txt('content') => $this
                ->getContentForPresentation(
                    $environment->getLanguage(),
                    $ctrl,
                    $static_url,
                    $environment->getUIFactory()
                ) ?? ''
        ];
    }

    public function toStorage(
        PersistenceFactory $persistence_factory
    ): array {
        if ($this->type === null) {
            throw new \UnexpectedValueException(
                'You cannot save a Suggested Learnign Content without a Type!'
            );
        }

        return [
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                $this->answer_form_id->toString()
            ),
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                $this->type->value
            ),
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                json_encode($this->buildContentArray())
            )
        ];
    }

    private function buildContentArray(): array
    {
        $array = [];
        if ($this->target_ref_id !== null) {
            $array[self::KEY_TARGET_REF_ID] = $this->target_ref_id;
        }

        if ($this->sub_object_id !== null) {
            $array[self::KEY_SUB_OBJECT_ID] = $this->sub_object_id;
        }

        if ($this->rid !== null) {
            $array[self::KEY_RID] = $this->rid->serialize();
        }

        if ($this->file_title !== '') {
            $array[self::KEY_FILE_TITLE] = $this->file_title;
        }

        return $array;
    }
}
