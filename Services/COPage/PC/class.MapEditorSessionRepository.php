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

namespace ILIAS\COPage\PC;

/**
 * Editing session repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class MapEditorSessionRepository
{
    protected const BASE_SESSION_KEY = 'copg_map_';
    protected const MODE_KEY = self::BASE_SESSION_KEY . 'mode';
    protected const AREA_NR_KEY = self::BASE_SESSION_KEY . 'area_nr';
    protected const AREA_TYPE_KEY = self::BASE_SESSION_KEY . 'area_type';
    protected const LINK_TYPE_KEY = self::BASE_SESSION_KEY . 'link_type';
    protected const LINK_TARGET_KEY = self::BASE_SESSION_KEY . 'link_target';
    protected const LINK_FRAME_KEY = self::BASE_SESSION_KEY . 'link_frame';
    protected const COORDS_KEY = self::BASE_SESSION_KEY . 'coords';

    public function __construct()
    {
    }

    public function setMode(string $mode): void
    {
        \ilSession::set(self::MODE_KEY, $mode);
    }

    public function getMode(): string
    {
        return \ilSession::get(self::MODE_KEY) ?? "";
    }

    public function setAreaNr(string $area_nr): void
    {
        \ilSession::set(self::AREA_NR_KEY, $area_nr);
    }

    public function getAreaNr(): string
    {
        return \ilSession::get(self::AREA_NR_KEY) ?? "";
    }

    public function setAreaType(string $area_type): void
    {
        \ilSession::set(self::AREA_TYPE_KEY, $area_type);
    }

    public function getAreaType(): string
    {
        return \ilSession::get(self::AREA_TYPE_KEY) ?? "";
    }

    public function setCoords(string $coords): void
    {
        \ilSession::set(self::COORDS_KEY, $coords);
    }

    public function getCoords(): string
    {
        return \ilSession::get(self::COORDS_KEY) ?? "";
    }

    public function setLinkType(string $link_type): void
    {
        \ilSession::set(self::LINK_TYPE_KEY, $link_type);
    }

    public function getLinkType(): string
    {
        return \ilSession::get(self::LINK_TYPE_KEY) ?? "";
    }

    public function setLinkTarget(string $link_target): void
    {
        \ilSession::set(self::LINK_TARGET_KEY, $link_target);
    }

    public function getLinkTarget(): string
    {
        return \ilSession::get(self::LINK_TARGET_KEY) ?? "";
    }

    public function setLinkFrame(string $link_frame): void
    {
        \ilSession::set(self::LINK_FRAME_KEY, $link_frame);
    }

    public function getLinkFrame(): string
    {
        return \ilSession::get(self::LINK_FRAME_KEY) ?? "";
    }
}
