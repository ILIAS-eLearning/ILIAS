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

/**
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjWorkspaceRootFolder extends ilObjWorkspaceFolder
{
    public function __construct(
        int $a_id = 0,
        bool $a_reference = true
    ) {
        global $DIC;
        parent::__construct($a_id, $a_reference);

        $this->db = $DIC->database();
    }

    protected function initType(): void
    {
        $this->type = "wsrt";
    }

    /**
     * get all translations from this category
     */
    public function getTranslations(): array
    {
        $ilDB = $this->db;

        $q = "SELECT * FROM object_translation WHERE obj_id = " .
            $ilDB->quote($this->getId(), 'integer') . " ORDER BY lang_default DESC";
        $r = $ilDB->query($q);

        $num = 0;

        $data["Fobject"] = array();
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data["Fobject"][$num] = array("title" => $row->title,
                                          "desc" => $row->description,
                                          "lang" => $row->lang_code
                                          );
            $num++;
        }

        // first entry is always the default language
        $data["default_language"] = 0;

        return $data ?: array();
    }

    // remove all Translations of current category
    public function removeTranslations(): void
    {
        $ilDB = $this->db;

        $query = "DELETE FROM object_translation WHERE obj_id= " .
            $ilDB->quote($this->getId(), 'integer');
        $ilDB->manipulate($query);
    }

    // add a new translation to current category
    public function addTranslation(
        string $a_title,
        string $a_desc,
        string $a_lang,
        string $a_lang_default
    ): void {
        $ilDB = $this->db;

        if (empty($a_title)) {
            $a_title = "NO TITLE";
        }

        $query = "INSERT INTO object_translation " .
             "(obj_id,title,description,lang_code,lang_default) " .
             "VALUES " .
             "(" . $ilDB->quote($this->getId(), 'integer') . "," .
             $ilDB->quote($a_title, 'text') . "," .
             $ilDB->quote($a_desc, 'text') . "," .
             $ilDB->quote($a_lang, 'text') . "," .
             $ilDB->quote($a_lang_default, 'integer') . ")";
        $ilDB->manipulate($query);
    }
}
