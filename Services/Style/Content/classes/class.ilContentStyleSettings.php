<?php declare(strict_types=1);

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

/**
 * Content style settings
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilContentStyleSettings
{
    protected ilDBInterface $db;
    public array $styles = array();

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->read();
    }

    /**
     * Add style to style folder
     */
    public function addStyle(int $a_style_id) : void
    {
        $this->styles[$a_style_id] =
            array("id" => $a_style_id,
                "title" => ilObject::_lookupTitle($a_style_id));
    }

    /**
     * remove Style from style list
     */
    public function removeStyle(int $a_id) : void
    {
        unset($this->styles[$a_id]);
    }

    public function update() : bool
    {
        $ilDB = $this->db;

        // save styles of style folder
        // note: there are no different style folders in ILIAS, only the one in the settings
        $q = "DELETE FROM style_folder_styles";
        $ilDB->manipulate($q);
        foreach ($this->styles as $style) {
            $q = "INSERT INTO style_folder_styles (folder_id, style_id) VALUES" .
                "(" . $ilDB->quote(0, "integer") . ", " .
                $ilDB->quote((int) $style["id"], "integer") . ")";
            $ilDB->manipulate($q);
        }

        return true;
    }

    public function read() : void
    {
        $ilDB = $this->db;

        // get styles of style folder
        $q = "SELECT * FROM style_folder_styles JOIN style_data ON (style_id = style_data.id)";

        $style_set = $ilDB->query($q);
        $this->styles = array();
        while ($style_rec = $ilDB->fetchAssoc($style_set)) {
            $this->styles[$style_rec["style_id"]] =
                array("id" => $style_rec["style_id"],
                    "title" => ilObject::_lookupTitle((int) $style_rec["style_id"]),
                    "category" => $style_rec["category"]);
        }

        $this->styles =
            ilArrayUtil::sortArray($this->styles, "title", "asc", false, true);
    }

    public function getStyles() : array
    {
        return $this->styles;
    }
}
