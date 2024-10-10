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

/**
 * @noinspection AutoloadingIssuesInspection
 */
class ilDclFileFieldModel extends ilDclBaseFieldModel
{
    public function allowFilterInListView(): bool
    {
        return false;
    }

    public function getValidFieldProperties(): array
    {
        return [ilDclBaseFieldModel::PROP_SUPPORTED_FILE_TYPES];
    }

    public function getSupportedExtensions(): array
    {
        if (!$this->hasProperty(ilDclBaseFieldModel::PROP_SUPPORTED_FILE_TYPES)) {
            return [];
        }

        $file_types = $this->getProperty(ilDclBaseFieldModel::PROP_SUPPORTED_FILE_TYPES);

        return $this->parseSupportedExtensions($file_types);
    }

    protected function parseSupportedExtensions(string $input_value): array
    {
        $supported_extensions = explode(",", $input_value);

        $trim_function = function ($value) {
            return trim(trim(strtolower($value)), ".");
        };

        return array_map($trim_function, $supported_extensions);
    }
}
