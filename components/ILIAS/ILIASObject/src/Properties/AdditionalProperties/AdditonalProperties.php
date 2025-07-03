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

use ILIAS\ILIASObject\Properties\Property;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Icon\Icon;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\TitleAndIconVisibility;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\HeaderActionVisibility;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\InfoTabVisibility;

/**
 * @author Stephan Kergomard
 */
class AdditionalProperties
{
    private bool $property_title_and_icon_visibility_updated = false;
    private bool $property_header_action_visibility_updated = false;
    private bool $property_info_tab_visibility_updated = false;
    private bool $property_icon_updated = false;

    public function __construct(
        private TitleAndIconVisibility $property_title_and_icon_visibility,
        private HeaderActionVisibility $property_header_action_visibility,
        private InfoTabVisibility $property_info_tab_visibility,
        private Icon $property_icon,
        private ?int $object_id = null
    ) {
    }

    public function getObjectId(): ?int
    {
        return $this->object_id;
    }

    public function getPropertyTitleAndIconVisibility(): Property
    {
        return $this->property_title_and_icon_visibility;
    }

    public function wasPropertyTitleAndIconVisibilityUpdated(): bool
    {
        return $this->property_title_and_icon_visibility_updated;
    }

    public function withPropertyTitleAndIconVisibility(TitleAndIconVisibility $property_title_and_icon_visibility): self
    {
        $clone = clone $this;
        $clone->property_title_and_icon_visibility = $property_title_and_icon_visibility;
        $clone->property_title_and_icon_visibility_updated = true;
        return $clone;
    }

    public function getPropertyHeaderActionVisibility(): Property
    {
        return $this->property_header_action_visibility;
    }

    public function wasPropertyHeaderActionVisbilityUpdated(): bool
    {
        return $this->property_header_action_visibility_updated;
    }

    public function withPropertyHeaderActionVisibility(HeaderActionVisibility $property_header_action_visibility): self
    {
        $clone = clone $this;
        $clone->property_header_action_visibility = $property_header_action_visibility;
        $clone->property_header_action_visibility_updated = true;
        return $clone;
    }

    public function getPropertyInfoTabVisibility(): Property
    {
        return $this->property_info_tab_visibility;
    }

    public function wasPropertyInfoTabVisbilityUpdated(): bool
    {
        return $this->property_info_tab_visibility_updated;
    }

    public function withPropertyInfoTabVisibility(InfoTabVisibility $property_info_tab_visibility): self
    {
        $clone = clone $this;
        $clone->property_info_tab_visibility = $property_info_tab_visibility;
        $clone->property_info_tab_visibility_updated = true;
        return $clone;
    }

    public function getPropertyIcon(): Icon
    {
        return $this->property_icon;
    }

    public function wasPropertyIconUpdated(): bool
    {
        return $this->property_icon_updated;
    }

    public function withPropertyIcon(Icon $property_icon): self
    {
        $clone = clone $this;
        $clone->property_icon = $property_icon;
        $clone->property_icon_updated = true;
        return $clone;
    }

    public function withResetUpdatedFlags(): self
    {
        $clone = clone $this;
        $clone->property_title_and_icon_visibility_updated = false;
        $clone->property_header_action_visibility_updated = false;
        $clone->property_info_tab_visibility_updated = false;
        $clone->property_icon_updated = false;
        return $clone;
    }
}
