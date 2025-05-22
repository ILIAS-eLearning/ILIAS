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

namespace ILIAS\MediaObjects\Metadata;

use ILIAS\MetaData\Services\ServicesInterface as LOMServices;

class MetadataManager
{
    protected LOMServices $lom_services;

    public function __construct(LOMServices $lom_services)
    {
        $this->lom_services = $lom_services;
    }

    public function learningObjectMetadata(): LOMServices
    {
        return $this->lom_services;
    }

    public function getLOMLanguagesForSelectInputs(): array
    {
        $languages = [];
        foreach ($this->lom_services->dataHelper()->getAllLanguages() as $language) {
            $languages[$language->value()] = $language->presentableLabel();
        }
        return $languages;
    }

    public function getLOMLanguageCodes(): array
    {
        $languages = [];
        foreach ($this->lom_services->dataHelper()->getAllLanguages() as $language) {
            $languages[] = $language->value();
        }
        return $languages;
    }
}
