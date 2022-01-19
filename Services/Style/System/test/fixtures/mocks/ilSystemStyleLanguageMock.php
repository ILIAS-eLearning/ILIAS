<?php

declare(strict_types=1);

class ilSystemStyleLanguageMock extends ilLanguage
{
    public array $requested = [];

    public function __construct()
    {
    }

    public function txt(string $a_topic, string $a_default_lang_fallback_mod = ''): string
    {
        $this->requested[] = $a_topic;
        return $a_topic;
    }
}
