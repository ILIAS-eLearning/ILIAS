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

namespace ILIAS\User\Profile\ChangeListeners;

use ILIAS\Language\Language;

/**
 * Class InterestedUserFieldAttribute
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldAttribute
{
    private string $name;
    /**
     * @var array<InterestedUserFieldComponent>
     */
    private array $components = [];

    public function __construct(
        private readonly Language $lng,
        private readonly string $attribute_name,
        string $field_name
    ) {
        $this->name = $this->getNameTranslation($field_name, $attribute_name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributeName(): string
    {
        return $this->attribute_name;
    }

    /**
     * @return array<InterestedUserFieldComponent>
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    private function getNameTranslation(string $field_name, string $attribute_name): string
    {
        $translation_key = str_replace("_{$field_name}", '', $attribute_name);
        if (isset(\ilObjUserFolderGUI::USER_FIELD_TRANSLATION_MAPPING[$translation_key])) {
            return $this->lng->txt(ilObjUserFolderGUI::USER_FIELD_TRANSLATION_MAPPING[$translation_key]);
        }

        return 'INVALID TRANSLATION KEY';
    }

    public function addComponent(string $component_name, string $description): void
    {
        $this->components[$component_name] = new InterestedUserFieldComponent(
            $component_name,
            $description
        );
    }
}
