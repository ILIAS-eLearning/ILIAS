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

use ILIAS\components\Authentication\Pages\AuthPageEditorContext;

class ilAuthPageEditorSettings
{
    /** @var array<value-of<AuthPageEditorContext>, self> */
    private static array $instances = [];

    /**
     * @var array<string, bool>
     */
    private array $languages = [];
    private ilSetting $storage;
    private ilLanguage $lng;


    private function __construct(AuthPageEditorContext $context)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->storage = new ilSetting($context->value);

        $this->read();
    }

    public static function getInstance(AuthPageEditorContext $context): self
    {
        return self::$instances[$context->value] ?? (self::$instances[$context->value] = new self($context));
    }

    private function getStorage(): ilSetting
    {
        return $this->storage;
    }

    public function getIliasEditorLanguage(string $a_langkey): string
    {
        if ($this->isIliasEditorEnabled($a_langkey)) {
            return $a_langkey;
        }

        if ($this->isIliasEditorEnabled($this->lng->getDefaultLanguage())) {
            return $this->lng->getDefaultLanguage();
        }

        return '';
    }

    public function enableIliasEditor(string $a_langkey, bool $a_status): void
    {
        $this->languages[$a_langkey] = $a_status;
    }

    public function isIliasEditorEnabled(string $a_langkey): bool
    {
        return $this->languages[$a_langkey] ?? false;
    }

    public function update(): void
    {
        foreach ($this->languages as $lngkey => $stat) {
            $this->storage->set($lngkey, (string) $stat);
        }
    }

    public function read(): void
    {
        $this->languages = [];
        foreach ($this->lng->getInstalledLanguages() as $lngkey) {
            $this->enableIliasEditor($lngkey, (bool) $this->getStorage()->get($lngkey, ''));
        }
    }
}
