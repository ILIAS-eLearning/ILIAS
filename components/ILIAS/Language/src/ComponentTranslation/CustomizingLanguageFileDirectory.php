<?php

declare(strict_types=1);

namespace ILIAS\Language\ComponentTranslation;

use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;

class CustomizingLanguageFileDirectory implements LanguageFileDirectory
{
    public function getPrefix(): string
    {
        return '';
    }

    public function getPath(): string
    {
        return 'lang/customizing/';
    }

    public function getSuffix(): string
    {
        return '.local';
    }

    public function isLocal(): bool
    {
        return true;
    }
}
