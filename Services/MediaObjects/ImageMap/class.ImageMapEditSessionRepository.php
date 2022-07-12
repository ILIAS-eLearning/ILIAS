<?php declare(strict_types = 1);

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

/**
 * Stores repository clipboard data
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ImageMapEditSessionRepository
{
    public const KEY_BASE = "il_map_";

    public function __construct()
    {
    }

    /**
     * @return mixed|null
     */
    protected function get(string $key)
    {
        if (\ilSession::has(self::KEY_BASE . $key)) {
            return \ilSession::get(self::KEY_BASE . $key);
        }
        return null;
    }

    /**
     * @param mixed $val
     */
    protected function set(string $key, $val) : void
    {
        \ilSession::set(self::KEY_BASE . $key, $val);
    }

    public function setTargetScript(string $script) : void
    {
        $this->set("edit_target_script", $script);
    }

    public function getTargetScript() : string
    {
        return (string) $this->get("edit_target_script");
    }

    public function setRefId(int $ref_id) : void
    {
        $this->set("edit_ref_id", $ref_id);
    }

    public function getRefId() : int
    {
        return (int) $this->get("edit_ref_id");
    }

    public function setObjId(int $obj_id) : void
    {
        $this->set("edit_obj_id", $obj_id);
    }

    public function getObjId() : int
    {
        return (int) $this->get("edit_obj_id");
    }

    public function setHierId(string $hier_id) : void
    {
        $this->set("edit_hier_id", $hier_id);
    }

    public function getHierId() : string
    {
        return (string) $this->get("edit_hier_id");
    }

    public function setPCId(string $pc_id) : void
    {
        $this->set("edit_pc_id", $pc_id);
    }

    public function getPCId() : string
    {
        return (string) $this->get("edit_pc_id");
    }

    public function setAreaType(string $type) : void
    {
        $this->set("edit_area_type", $type);
    }

    public function getAreaType() : string
    {
        return (string) $this->get("edit_area_type");
    }

    public function setAreaNr(int $nr) : void
    {
        $this->set("area_nr", $nr);
    }

    public function getAreaNr() : int
    {
        return (int) $this->get("area_nr");
    }

    public function setCoords(string $coords) : void
    {
        $this->set("edit_coords", $coords);
    }

    public function getCoords() : string
    {
        return (string) $this->get("edit_coords");
    }

    public function setMode(string $mode) : void
    {
        $this->set("edit_mode", $mode);
    }

    public function getMode() : string
    {
        return (string) $this->get("edit_mode");
    }

    public function setLinkType(string $type) : void
    {
        $this->set("il_ltype", $type);
    }

    public function getLinkType() : string
    {
        return (string) $this->get("il_ltype");
    }

    public function setExternalLink(string $href) : void
    {
        $this->set("el_href", $href);
    }

    public function getExternalLink() : string
    {
        return (string) $this->get("el_href");
    }

    public function setInternalLink(
        string $type,
        string $target,
        string $target_frame,
        string $anchor
    ) : void {
        $this->set("il_type", $type);
        $this->set("il_target", $target);
        $this->set("il_targetframe", $target_frame);
        $this->set("il_anchor", $anchor);
    }
    
    /**
     * @return string[]
     */
    public function getInternalLink() : array
    {
        return [
            "type" => (string) $this->get("il_type"),
            "target" => (string) $this->get("il_target"),
            "target_frame" => (string) $this->get("il_targetframe"),
            "anchor" => (string) $this->get("il_anchor")
        ];
    }

    public function clear() : void
    {
        \ilSession::clear(self::KEY_BASE . "area_nr");
        \ilSession::clear(self::KEY_BASE . "edit_coords");
        \ilSession::clear(self::KEY_BASE . "edit_mode");
        \ilSession::clear(self::KEY_BASE . "el_href");
        \ilSession::clear(self::KEY_BASE . "il_type");
        \ilSession::clear(self::KEY_BASE . "il_ltype");
        \ilSession::clear(self::KEY_BASE . "il_target");
        \ilSession::clear(self::KEY_BASE . "il_targetframe");
        \ilSession::clear(self::KEY_BASE . "il_anchor");
        \ilSession::clear(self::KEY_BASE . "edit_area_type");
    }
}
