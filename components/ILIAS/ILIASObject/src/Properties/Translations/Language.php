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

namespace ILIAS\ILIASObject\Properties\Translations;

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\Language\Language as SystemLanguage;
use ILIAS\Refinery\Factory as Refinery;

class Language
{
    public function __construct(
        private readonly string $language_code,
        private string $title,
        private string $description,
        private bool $default = false,
        private bool $master = false
    ) {
    }

    public function getLanguageCode(): string
    {
        return $this->language_code;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function withDescription(string $description): self
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function withDefault(bool $default): self
    {
        $clone = clone $this;
        $clone->default = $default;
        return $clone;
    }

    public function isMaster(): bool
    {
        return $this->master;
    }

    public function withMaster(bool $master): self
    {
        $clone = clone $this;
        $clone->master = $master;
        return $clone;
    }

    public function toForm(
        \ilLanguage $language,
        FieldFactory $field_factory,
        Refinery $refinery
    ): array {
        return [
            $field_factory->group([
                'language' => $field_factory->hidden()->withValue($this->language_code),
                'title' => $field_factory->text($language->txt('title'))
                    ->withRequired(true)
                    ->withValue($this->title),
                'description' => $field_factory->textarea($language->txt('description'))
                    ->withValue($this->description)
            ])->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    static fn(array $vs): self => new self(
                        $vs['language'],
                        $vs['title'],
                        $vs['description']
                    )
                )
            )
        ];
    }

    public function toRow(
        DataRowBuilder $row_builder,
        SystemLanguage $lng
    ): DataRow {
        return $row_builder->buildDataRow(
            $this->language_code,
            [
                'language' => $this->getTranslatedLanguageName($lng, $this->language_code),
                'master' => $this->isMaster(),
                'default' => $this->isDefault(),
                'title' => $this->getTitle(),
                'description' => $this->getDescription()
            ]
        )->withDisabledAction(TranslationsTable::ACTION_DELETE, $this->isMaster() || $this->isDefault())
        ->withDisabledAction(TranslationsTable::ACTION_MAKE_DEFAULT, $this->isDefault());
    }

    private function getTranslatedLanguageName(
        SystemLanguage $lng,
        string $language_code
    ): string {
        return $lng->txt("meta_l_{$language_code}");
    }
}
