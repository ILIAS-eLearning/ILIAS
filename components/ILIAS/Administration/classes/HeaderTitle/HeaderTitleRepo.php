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

namespace ILIAS\Administration;

use ilDBInterface;
use ilObjUser;
use ilStr;
use ilObject;

readonly class HeaderTitleRepo
{
    private ilDBInterface $db;
    private ilObjUser $user;
    private int $obj_id;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->obj_id = SYSTEM_FOLDER_ID;
    }

    public function getHeaderTitle(): string
    {
        $title = '';

        $q = "SELECT title FROM object_translation " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
            "AND lang_default = 1";
        $r = $this->db->query($q);
        $row = $this->db->fetchObject($r);
        if ($row !== null) {
            $title = (string) $row->title;
        }

        $q = "SELECT title FROM object_translation " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
            "AND lang_code = " .
            $this->db->quote($this->user->getCurrentLanguage(), 'text') . " " .
            "AND NOT lang_default = 1";
        $r = $this->db->query($q);
        $row = $this->db->fetchObject($r);

        if ($row !== null) {
            $title = (string) $row->title;
        }

        return $title;
    }

    public function getHeaderTitleTranslations(): array
    {
        $q = "SELECT * FROM object_translation WHERE obj_id = " .
            $this->db->quote($this->obj_id, 'integer') . " ORDER BY lang_default DESC";
        $r = $this->db->query($q);

        $num = 0;
        while ($row = $this->db->fetchObject($r)) {
            $data["Fobject"][$num] = array("title" => $row->title,
                                           "desc" => ilStr::shortenTextExtended(
                                               (string) $row->description,
                                               ilObject::DESC_LENGTH,
                                               true
                                           ),
                                           "lang" => $row->lang_code
            );
            $num++;
        }

        // first entry is always the default language
        $data["default_language"] = 0;

        return $data ?? [];
    }

    public function removeHeaderTitleTranslations(): void
    {
        $query = "DELETE FROM object_translation WHERE obj_id= " .
            $this->db->quote($this->obj_id, 'integer');
        $this->db->manipulate($query);
    }

    public function addHeaderTitleTranslation(string $a_title, string $a_lang, bool $a_lang_default): void
    {
        $query = "INSERT INTO object_translation " .
            "(obj_id,title,description,lang_code,lang_default) " .
            "VALUES " .
            "(" . $this->db->quote($this->obj_id, 'integer') . "," .
            $this->db->quote($a_title, 'text') . "," .
            $this->db->quote('', 'text') . "," .
            $this->db->quote($a_lang, 'text') . "," .
            $this->db->quote($a_lang_default, 'integer') . ")";
        $res = $this->db->manipulate($query);
    }
}
