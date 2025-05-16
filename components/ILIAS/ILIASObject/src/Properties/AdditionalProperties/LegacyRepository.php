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

namespace ILIAS\ILIASObject\Properties\AdditionalProperties;

use ILIAS\ILIASObject\Properties\AdditionalProperties\Icon\Factory as CustomIconFactory;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Icon\Icon;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\TitleAndIconVisibility;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\HeaderActionVisibility;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\InfoTabVisibility;
use ILIAS\ILIASObject\Properties\ObjectTypeSpecificProperties\Factory as ObjectTypeSpecificPropertiesFactory;

/**
 *
 * @author Stephan Kergomard
 */
class LegacyRepository implements Repository
{
    public function __construct(
        private CustomIconFactory $custom_icon_factory,
        private ObjectTypeSpecificPropertiesFactory $object_type_specific_properties_factory
    ) {
    }

    public function getFor(int $object_id): AdditionalProperties
    {
        if ($object_id === 0) {
            return $this->getDefaultAdditionalProperties();
        }

        $type = \ilObject::_lookupType($object_id);
        $object_type_specific_properties = $this->object_type_specific_properties_factory->getForObjectTypeString($type);
        $providers = null;
        if ($object_type_specific_properties !== null) {
            $providers = $object_type_specific_properties->getProviders();
        }

        return new AdditionalProperties(
            new TitleAndIconVisibility($this->getTitleAndIconVisibility($object_id)),
            new HeaderActionVisibility($this->getHeaderActionVisibility($object_id)),
            new InfoTabVisibility($this->getInfoTabVisibility($object_id)),
            new Icon(
                $this->areCustomIconsEnabled(),
                $this->custom_icon_factory->getByObjId($object_id),
                $providers
            ),
            $object_id
        );
    }

    public function store(AdditionalProperties $properties): AdditionalProperties
    {
        if ($properties->getObjectId() === null) {
            throw new \Exception('The current configuration cannot be saved.');
        }

        $object_id = $properties->getObjectId();

        if ($properties->wasPropertyTitleAndIconVisibilityUpdated()) {
            $this->storeTitleAndIconVisibility(
                $object_id,
                $properties->getPropertyTitleAndIconVisibility()->getVisibility()
            );
        }

        if ($properties->wasPropertyHeaderActionVisbilityUpdated()) {
            $this->storeHeaderActionVisibility(
                $object_id,
                $properties->getPropertyHeaderActionVisibility()->getVisibility()
            );
        }

        if ($properties->wasPropertyInfoTabVisbilityUpdated()) {
            $this->storeInfoTabVisibility(
                $object_id,
                $properties->getPropertyInfoTabVisibility()->getVisibility()
            );
        }

        if ($properties->wasPropertyIconUpdated()) {
            $this->storeIcon($properties->getPropertyIcon());
        }

        return $properties->withResetUpdatedFlags();
    }

    private function getDefaultAdditionalProperties(): AdditionalProperties
    {
        return new AdditionalProperties(
            new TitleAndIconVisibility(),
            new HeaderActionVisibility(),
            new InfoTabVisibility(),
            new Icon(
                $this->areCustomIconsEnabled()
            )
        );
    }

    private function getTitleAndIconVisibility(int $object_id): bool
    {
        return !((bool) \ilContainer::_lookupContainerSetting($object_id, 'hide_header_icon_and_title'));
    }

    private function storeTitleAndIconVisibility(int $object_id, bool $visibility): void
    {
        $hide_header_icon_and_title = $visibility ? '' : '1';
        \ilContainer::_writeContainerSetting(
            $object_id,
            'hide_header_icon_and_title',
            $hide_header_icon_and_title
        );
    }

    private function getHeaderActionVisibility(int $object_id): bool
    {
        return !((bool) \ilContainer::_lookupContainerSetting($object_id, 'hide_top_actions'));
    }

    private function storeHeaderActionVisibility(int $object_id, bool $visibility): void
    {
        $hide_top_actions = $visibility ? '' : '1';
        \ilContainer::_writeContainerSetting(
            $object_id,
            'hide_top_actions',
            $hide_top_actions
        );
    }

    private function getInfoTabVisibility(int $object_id): bool
    {
        return ((bool) \ilContainer::_lookupContainerSetting($object_id, 'cont_show_info_tab'));
    }

    private function storeInfoTabVisibility(int $object_id, bool $visibility): void
    {
        $show_info_tab = $visibility ? '1' : '';
        \ilContainer::_writeContainerSetting(
            $object_id,
            'cont_show_info_tab',
            $show_info_tab
        );
    }

    private function areCustomIconsEnabled(): bool
    {
        return (bool) \ilSetting::_lookupValue('common', 'custom_icons');
    }

    private function storeIcon(Icon $property_icon): void
    {
        if ($property_icon->getDeletedFlag()) {
            $property_icon->getCustomIcon()->remove();
        }

        if ($property_icon->getTempFileName()) {
            $property_icon->getCustomIcon()->saveFromTempFileName($property_icon->getTempFileName());
        }
    }
}
