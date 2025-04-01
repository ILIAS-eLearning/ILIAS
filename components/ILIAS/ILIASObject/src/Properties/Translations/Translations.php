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

use ILIAS\ILIASObject\Properties\Property;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class handles translation mode for an object.
 *
 * Objects may not use any translations at all
 * - use translations for title/description only or
 * - use translation for (the page editing) content, too.
 *
 * Currently, supported by container objects and ILIAS learning modules.
 *
 * Content master lang vs. default language
 * - If no translation mode for the content is active no master lang will be
 *   set and no record in table obj_content_master_lng will be saved. For the
 *   title/descriptions the default will be marked by field lang_default in table
 *   object_translation.
 * - If translation for content is activated a master language must be set (since
 *   consent may already exist the language of this content is defined through
 *   setting the master language (in obj_content_master_lng). Modules that use
 *   this mode will not get informed about this, so they can not internally
 *   assign existing content to the master lang
 * - If translation for content is activated additionally a fallback language
 *   can be defined. Users will be presented their language, if content available
 *   otherwise the fallback language, if content is available, otherwise the
 *   master language
 */
class Translations
{
    /**
     *
     * @param array $languages list<Language>
     */
    public function __construct(
        private readonly int $obj_id,
        private array $languages,
        private string $default_language,
        private ?string $master_language,
        private readonly bool $migration_missing = false
    ) {
    }

    /**
     * @todo: Remove with ILIAS 12
     */
    public function migrationMissing(): bool
    {
        return $this->migration_missing;
    }
    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getDefaultLanguage(): string
    {
        return $this->default_language;
    }

    public function withDefaultLanguage(string $default_language): self
    {
        $clone = clone $this;
        $clone->languages[$clone->default_language] = $clone->languages[$clone->default_language]->withDefault(false);
        $clone->languages[$default_language] = $clone->languages[$default_language]->withDefault(true);
        $clone->default_language = $default_language;
        return $clone;
    }

    public function getMasterLanguage(): string
    {
        return $this->master_language ?? '';
    }

    public function withMasterLanguage(string $master_language): self
    {
        $clone = clone $this;
        if ($clone->master_language !== null) {
            $clone->languages[$clone->master_language] = $clone->languages[$clone->master_language]->withMaster(false);
        }
        $clone->languages[$master_language] = $clone->languages[$master_language]->withMaster(true);
        $clone->master_language = $master_language;
        return $clone;
    }

    /**
     * @return list<Language>
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function getLaguageForCode(string $lang_code): ?Language
    {
        return $this->languages[$lang_code] ?? null;
    }

    public function withLanguage(Language $lang): self
    {
        $clone = $this;
        $clone->languages[$lang->getLanguageCode()] = $lang;
        if ($lang->isMaster()) {
            $clone->languages[$clone->master_language] = $clone->languages[$clone->master_language]->withMaster(false);
            $clone->master_language = $lang->getLanguageCode();
        }

        if ($lang->isDefault()) {
            $clone->languages[$clone->default_language] = $this->languages[$clone->default_language]->withDefault(false);
            $clone->default_language = $lang->getLanguageCode();
        }

        return $clone;
    }

    public function withoutLanguage(string $lang): self
    {
        if ($lang === $this->master_language) {
            return $this;
        }

        $clone = $this;
        unset($clone->languages[$lang]);
        return $clone;
    }

    public function withAdditionalLanguage(
        string $lang_code,
        string $title,
        string $description,
        bool $default,
        bool $force = false
    ): self {
        if ($lang_code === ''
            || isset($this->languages[$lang_code]) && !$force) {
            return $this;
        }

        $clone = clone $this;
        if ($default && isset($clone->languages[$clone->default_language])) {
            $clone->languages[$clone->default_language] = $clone->languages[$clone->default_language]->withDefault(false);
            $clone->default_language = $lang_code;
        }

        $clone->languages[$lang_code] = new Language(
            $lang_code,
            $title,
            $description,
            $default
        );
        return $clone;
    }

    public function withResetLanguages(): self
    {
        $clone = clone $this;
        $clone->languages = [];
        return $clone;
    }

    public function getDefaultTitle(): string
    {
        if ($this->default_language !== ''
            && array_key_exists($this->default_language, $this->languages)) {
            return $this->languages[$this->default_language]->getTitle();
        }

        if ($this->languages === []) {
            return \ilObject::_lookupTitle($this->getObjId());
        }
        return '';
    }

    public function withDefaultTitle(string $title): self
    {
        if ($this->default_language === ''
            || !isset($this->languages[$this->default_language])) {
            return $this;
        }

        $clone = clone $this;
        $clone->languages[$clone->default_language] = $clone->languages[$clone->default_language]->withTitle($title);
        return $clone;
    }

    public function getDefaultDescription(): string
    {
        if ($this->default_language !== ''
            && array_key_exists($this->default_language, $this->languages)) {
            return $this->languages[$this->default_language]->getDescription();
        }

        if ($this->languages === []) {
            return \ilObject::_lookupTitle($this->getObjId());
        }
        return '';
    }

    public function withDefaultDescription(string $description): self
    {
        if ($this->default_language === ''
            || !isset($this->languages[$this->default_language])) {
            return $this;
        }

        $clone = clone $this;
        $clone->languages[$clone->default_language] = $clone->languages[$clone->default_language]->withDescription($description);
        return $clone;
    }

    public function getContentTranslationActivated(): bool
    {
        return $this->master_language !== null;
    }

    public function withDeactivatedContentTranslation(): self
    {
        $clone = clone $this;
        $clone->languages[$clone->master_language] = $clone->languages[$this->master_language]->withMaster(false);
        $clone->master_language = null;
        return $clone;
    }

    public function copy(int $obj_id): self
    {
        return new self(
            $obj_id,
            $this->languages,
            $this->default_language,
            $this->master_language
        );
    }

    public function getEffectiveCOPageLang(
        string $lang,
        string $parent_type
    ): string {
        if ($this->master_language === null) {
            return '-';
        }

        $page_lang_key = $lang === $this->master_language
            ? '-'
            : $lang;

        if (isset($this->languages[$lang])
            && \ilPageObject::_exists($parent_type, $this->getObjId(), $page_lang_key)) {
            return $page_lang_key;
        }

        if (isset($this->languages[$this->default_language])
            && \ilPageObject::_exists($parent_type, $this->getObjId(), $this->default_language)) {
            return $this->default_language;
        }
        return '-';
    }
}
