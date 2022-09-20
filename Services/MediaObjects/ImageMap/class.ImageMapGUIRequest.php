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

namespace ILIAS\MediaObjects\ImageMap;

use ILIAS\Repository\BaseGUIRequest;

class ImageMapGUIRequest
{
    use BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getObjId(): int
    {
        return $this->int("obj_id");
    }

    public function getHierId(): string
    {
        return $this->str("hier_id");
    }

    public function getPCId(): string
    {
        return $this->str("pc_id");
    }

    public function getItemId(): int
    {
        return $this->int("item_id");
    }

    public function getLinkType(): string
    {
        return $this->str("linktype");
    }

    public function getLinkTarget(): string
    {
        return $this->str("linktarget");
    }

    public function getLinkTargetFrame(): string
    {
        return $this->str("linktargetframe");
    }

    public function getLinkAnchor(): string
    {
        return $this->str("linkanchor");
    }

    public function getX(): string
    {
        return $this->str("editImagemapForward_x");
    }

    public function getY(): string
    {
        return $this->str("editImagemapForward_y");
    }

    public function getAreaTitle(int $nr): string
    {
        return $this->str("name_" . $nr);
    }

    public function getAreaName(): string
    {
        return $this->str("area_name");
    }

    public function getAreaHighlightMode(int $nr): string
    {
        return $this->str("hl_mode_" . $nr);
    }

    public function getAreaHighlightClass(int $nr): string
    {
        return $this->str("hl_class_" . $nr);
    }

    public function getHighlightMode(): string
    {
        return $this->str("highlight_mode");
    }

    public function getHighlightClass(): string
    {
        return $this->str("highlight_class");
    }

    public function getAreaShape(): string
    {
        return $this->str("shape");
    }

    public function getAreaLinkType(): string
    {
        return $this->str("area_link_type");
    }

    public function getExternalLink(): string
    {
        return $this->str("area_link_ext");
    }

    public function getArea(): array
    {
        return $this->strArray("area");
    }
}
