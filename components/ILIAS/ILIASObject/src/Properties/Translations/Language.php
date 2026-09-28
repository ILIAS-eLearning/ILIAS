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
    public const KEY_LANGUAGE = 'language';
    public const KEY_BASE = 'base';
    public const KEY_DEFAULT = 'default';
    public const KEY_TITLE = 'title';
    public const KEY_DESCRIPTION = 'description';

    public function __construct(
        private readonly string $language_code,
        private string $title,
        private string $description,
        private bool $default = false,
        private bool $base = false
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

    public function isBase(): bool
    {
        return $this->base;
    }

    public function withBase(bool $base): self
    {
        $clone = clone $this;
        $clone->base = $base;
        return $clone;
    }

    public function toForm(
        \ilLanguage $language,
        FieldFactory $field_factory,
        Refinery $refinery
    ): array {
        return [
            $field_factory->group([
                self::KEY_LANGUAGE => $field_factory->hidden()->withValue($this->language_code),
                self::KEY_TITLE => $field_factory->text($language->txt('title'))
                    ->withRequired(true)
                    ->withValue($this->title),
                self::KEY_DESCRIPTION => $field_factory->textarea($language->txt('description'))
                    ->withValue($this->description),
                self::KEY_DEFAULT => $field_factory->hidden()->withValue($this->isDefault()),
                self::KEY_BASE => $field_factory->hidden()->withValue($this->isBase()),
            ])->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    static fn(array $vs): self => new self(
                        $vs[self::KEY_LANGUAGE],
                        $vs[self::KEY_TITLE],
                        $vs[self::KEY_DESCRIPTION],
                        $vs[self::KEY_DEFAULT] === '1',
                        $vs[self::KEY_BASE] === '1'
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
                self::KEY_LANGUAGE => $this->getTranslatedLanguageName($lng, $this->language_code),
                self::KEY_BASE => $this->isBase(),
                self::KEY_DEFAULT => $this->isDefault(),
                self::KEY_TITLE => $this->getTitle(),
                self::KEY_DESCRIPTION => $this->getDescription()
            ]
        )->withDisabledAction(TranslationsTable::ACTION_DELETE, $this->isBase() || $this->isDefault())
        ->withDisabledAction(TranslationsTable::ACTION_MAKE_DEFAULT, $this->isDefault());
    }

    public function getDisplayValueForKey(
        SystemLanguage $lng,
        string $key
    ): string|bool {
        return match($key) {
            self::KEY_LANGUAGE => $this->getTranslatedLanguageName(
                $lng,
                $this->language_code
            ),
            self::KEY_BASE => $this->base,
            self::KEY_DEFAULT => $this->default,
            self::KEY_TITLE => $this->title,
            self::KEY_DESCRIPTION => $this->description
        };
    }

    private function getTranslatedLanguageName(
        SystemLanguage $lng,
        string $language_code
    ): string {
        return $lng->txt("meta_l_{$language_code}");
    }
}
