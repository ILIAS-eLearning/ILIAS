<?php

declare(strict_types=1);

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

class ilObjectTranslationLanguage
{
    protected string $language_code;
    protected string $title;
    protected string $description;
    protected bool $default = false;

    public function __construct(string $language_code, string $title, string $description, bool $default)
    {
        $this->language_code = $language_code;
        $this->title = $title;
        $this->description = $description;
        $this->default = $default;
    }

    public function getLanguageCode(): string
    {
        return $this->language_code;
    }

    public function setLanguageCode(string $language_code): void
    {
        $this->language_code = $language_code;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }
}
