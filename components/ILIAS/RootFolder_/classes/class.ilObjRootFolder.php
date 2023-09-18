<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilObjRootFolder
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjRootFolder extends ilContainer
{
    public function __construct(
        int $a_id,
        bool $a_call_by_reference = true
    ) {
        $this->type = "root";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * @throws ilException
     */
    public function delete(): bool
    {
        $message = get_class($this) . "::delete(): Can't delete root folder!";
        throw new ilException($message);
    }

    public function getTranslations(): array
    {
        global $ilDB;

        $q = "SELECT * FROM object_translation WHERE obj_id = " .
            $ilDB->quote($this->getId(), 'integer') . " ORDER BY lang_default DESC";
        $r = $this->ilias->db->query($q);

        $num = 0;

        $data["Fobject"] = [];
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data["Fobject"][$num] = [
                "title" => $row->title,
                "desc" => $row->description,
                "lang" => $row->lang_code
            ];
            $num++;
        }

        // first entry is always the default language
        $data["default_language"] = 0;

        return $data ?: [];
    }

    // remove translations of current category
    public function deleteTranslation(string $a_lang): void
    {
        global $ilDB;

        $query = "DELETE FROM object_translation WHERE obj_id= " .
            $ilDB->quote($this->getId(), 'integer') . " AND lang_code = " .
            $ilDB->quote($a_lang, 'text');
        $res = $ilDB->manipulate($query);
    }

    // remove all Translations of current category
    public function removeTranslations(): void
    {
        global $ilDB;

        $query = "DELETE FROM object_translation WHERE obj_id= " .
            $ilDB->quote($this->getId(), 'integer');
        $res = $ilDB->manipulate($query);
    }

    // add a new translation to current category
    public function addTranslation(string $a_title, string $a_desc, string $a_lang, string $a_lang_default): void
    {
        global $ilDB;

        if ($a_title === '') {
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

    public function addAdditionalSubItemInformation(array &$object): void
    {
        ilObjectActivation::addAdditionalSubItemInformation($object);
    }
}
