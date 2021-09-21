<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\User;

use ilLanguage;
use ilObjUserFolderGUI;

/**
 * Class InterestedUserFieldAttribute
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldAttribute
{
    public static ?ilLanguage $lng = null;
    private string $attributeName;

    private string $name;
    /**
     * @var InterestedUserFieldComponent[]
     */
    private array $components = [];

    public function __construct(string $attributeName, string $fieldName)
    {
        $this->attributeName = $attributeName;
        if (!self::$lng) {
            global $DIC;
            self::$lng = $DIC->language();
        }
        $this->name = $this->getNameTranslation($fieldName, $attributeName);
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getAttributeName() : string
    {
        return $this->attributeName;
    }

    /**
     * @return InterestedUserFieldComponent[]
     */
    public function getComponents() : array
    {
        return $this->components;
    }

    private function getNameTranslation(string $fieldName, string $attributeName) : string
    {
        $translationKey = str_replace("_$fieldName", "", $attributeName);
        if (isset(ilObjUserFolderGUI::USER_FIELD_TRANSLATION_MAPPING[$translationKey])) {
            return self::$lng->txt(ilObjUserFolderGUI::USER_FIELD_TRANSLATION_MAPPING[$translationKey]);
        }

        return "INVALID TRANSLATION KEY";
    }

    public function addComponent(string $componentName, string $description) : InterestedUserFieldComponent
    {
        foreach ($this->components as $component) {
            if ($component->getComponentName() === $componentName) {
                return $component;
            }
        }

        $component = new InterestedUserFieldComponent(
            $componentName,
            $description
        );
        $this->components[] = $component;

        return $component;
    }
}
