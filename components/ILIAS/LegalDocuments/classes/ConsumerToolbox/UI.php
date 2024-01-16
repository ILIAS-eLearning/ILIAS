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

namespace ILIAS\LegalDocuments\ConsumerToolbox;

use ILIAS\UI\Factory as UIFactory;
use ilGlobalTemplateInterface;
use ilLanguage;
use ilObjLegalDocumentsGUI;

class UI
{
    public function __construct(
        private readonly string $id,
        private readonly UIFactory $create,
        private readonly ilGlobalTemplateInterface $main_template,
        private readonly ilLanguage $language
    ) {
    }

    public function create(): UIFactory
    {
        return $this->create;
    }

    public function mainTemplate(): ilGlobalTemplateInterface
    {
        return $this->main_template;
    }

    public function loadLanguageModule(string $module): void
    {
        $this->language->loadLanguageModule($module);
    }

    public function txt(string $name): string
    {
        return $this->language->txt($this->firstExisting([
            $this->id . '_' . $name,
            ilObjLegalDocumentsGUI::TYPE . '_' . $name
        ], $name));
    }

    /**
     * @param list<string> $lang_vars
     */
    private function firstExisting(array $lang_vars, string $fallback): string
    {
        foreach ($lang_vars as $lang_var) {
            if ($this->language->exists($lang_var)) {
                return $lang_var;
            }
        }

        return $fallback;
    }
}
