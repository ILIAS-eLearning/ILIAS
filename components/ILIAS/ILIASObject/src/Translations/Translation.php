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

namespace ILIAS\ILIASObject\Translations;

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
class Translation
{
    /**
     *
     * @param array $languages list<Language>
     */
    public function __construct(
        private readonly int $obj_id,
        private bool $copage_translation_activated,
        private array $languages,
        private string $default_language,
        private ?string $master_language
    ) {
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getDefaultLanguage(): string
    {
        return $this->default_language;
    }

    public function getMasterLanguage(): ?string
    {
        return $this->master_language;
    }

    /**
     * @return list<Language>
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function addLanguage(
        string $lang_code,
        string $title,
        string $description,
        bool $default,
        bool $force = false
    ): void {
        if ($lang_code === ''
            || isset($this->languages[$lang_code]) && !$force) {
            return;
        }

        if ($default) {
            $this->languages[$this->default_language] = $this->languages[$this->default_language]->withDefault(false);
            $this->default_language = $lang_code;
        }

        $this->languages[$lang_code] = new Language(
            $lang_code,
            $title,
            $description,
            $default
        );
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

    public function setDefaultTitle(string $title): void
    {
        if ($this->default_language() === ''
            || !isset($this->languages[$this->default_language])) {
            return;
        }

        $this->languages[$this->default_language] = $this->languages[$this->default_language]->withTitle($title);
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

    public function setDefaultDescription(string $description): void
    {
        if ($this->default_language() === ''
            || !isset($this->languages[$this->default_language])) {
            return;
        }

        $this->languages[$this->default_language] = $this->languages[$this->default_language]->withDescription($description);
    }

    public function removeLanguage(string $lang): void
    {
        if ($lang !== $this->master_language) {
            unset($this->languages[$lang]);
        }
    }

    public function getCOPageTranslationActivated(): bool
    {
        return $this->copage_translation_activated;
    }

    public function copy(int $obj_id): self
    {
        return new self(
            $obj_id,
            $this->copage_translation_activated,
            $this->languages,
            $this->default_language,
            $this->master_language
        );
    }

    public function getEffectiveCOPageLang(
        string $lang,
        string $parent_type
    ): string {
        if (!$this->copage_translation_activated) {
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
