<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Content style settings
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesStyle
 */
class ilContentStyleSettings
{
    /**
     * @var ilDB
     */
    protected $db;

    public $styles = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->read();
    }

    /**
     * Add style to style folder
     *
     * @param	int		$a_style_id		style id
     */
    public function addStyle($a_style_id)
    {
        $this->styles[$a_style_id] =
            array("id" => $a_style_id,
                "title" => ilObject::_lookupTitle($a_style_id));
    }


    /**
     * remove Style from style list
     */
    public function removeStyle($a_id)
    {
        unset($this->styles[$a_id]);
    }


    /**
     * update object data
     *
     * @return	boolean
     */
    public function update()
    {
        $ilDB = $this->db;

        // save styles of style folder
        // note: there are no different style folders in ILIAS, only the one in the settings
        $q = "DELETE FROM style_folder_styles";
        $ilDB->manipulate($q);
        foreach ($this->styles as $style) {
            $q = "INSERT INTO style_folder_styles (folder_id, style_id) VALUES" .
                "(" . $ilDB->quote((int) 0, "integer") . ", " .
                $ilDB->quote((int) $style["id"], "integer") . ")";
            $ilDB->manipulate($q);
        }

        return true;
    }

    /**
     * read style folder data
     */
    public function read()
    {
        $ilDB = $this->db;

        // get styles of style folder
        $q = "SELECT * FROM style_folder_styles JOIN style_data ON (style_id = style_data.id)";

        $style_set = $ilDB->query($q);
        $this->styles = array();
        while ($style_rec = $ilDB->fetchAssoc($style_set)) {
            $this->styles[$style_rec["style_id"]] =
                array("id" => $style_rec["style_id"],
                    "title" => ilObject::_lookupTitle($style_rec["style_id"]),
                    "category" => $style_rec["category"]);
        }

        $this->styles =
            ilUtil::sortArray($this->styles, "title", "asc", false, true);
    }


    /**
     * get style ids
     *
     * @return		array		ids
     */
    public function getStyles()
    {
        return $this->styles;
    }
}
