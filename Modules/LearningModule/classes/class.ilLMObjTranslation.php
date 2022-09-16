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
 * Translation information on lm object
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMObjTranslation
{
    protected int $id = 0;
    protected ilDBInterface $db;
    protected string $lang = "";
    protected string $title = "";
    protected string $short_title = "";
    protected string $create_date = "";
    protected string $last_update = "";

    public function __construct(
        int $a_id = 0,
        string $a_lang = ""
    ) {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_id > 0 && $a_lang != "") {
            $this->setId($a_id);
            $this->setLang($a_lang);
            $this->read();
        }
    }

    public function setId(int $a_val): void
    {
        $this->id = $a_val;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setLang(string $a_val): void
    {
        $this->lang = $a_val;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setTitle(string $a_val): void
    {
        $this->title = $a_val;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setShortTitle(string $a_val): void
    {
        $this->short_title = $a_val;
    }

    public function getShortTitle(): string
    {
        return $this->short_title;
    }

    public function getCreateDate(): string
    {
        return $this->create_date;
    }

    public function getLastUpdate(): string
    {
        return $this->last_update;
    }

    public function read(): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM lm_data_transl " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer") .
            " AND lang = " . $ilDB->quote($this->getLang(), "text")
        );
        $rec = $ilDB->fetchAssoc($set);
        $this->setTitle($rec["title"] ?? "");
        $this->setShortTitle($rec["short_title"] ?? "");
        $this->create_date = ($rec["create_date"] ?? 0);
        $this->last_update = ($rec["last_update"] ?? 0);
    }

    public function save(): void
    {
        $ilDB = $this->db;

        if (!self::exists($this->getId(), $this->getLang())) {
            $ilDB->manipulate("INSERT INTO lm_data_transl " .
                "(id, lang, title, short_title, create_date, last_update) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($this->getLang(), "text") . "," .
                $ilDB->quote($this->getTitle(), "text") . "," .
                $ilDB->quote($this->getShortTitle(), "text") . "," .
                $ilDB->now() . "," .
                $ilDB->now() .
                ")");
        } else {
            $ilDB->manipulate(
                "UPDATE lm_data_transl SET " .
                " title = " . $ilDB->quote($this->getTitle(), "text") . "," .
                " short_title = " . $ilDB->quote($this->getShortTitle(), "text") . "," .
                " last_update = " . $ilDB->now() .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer") .
                " AND lang = " . $ilDB->quote($this->getLang(), "text")
            );
        }
    }

    /**
     * Check for existence
     */
    public static function exists(
        int $a_id,
        string $a_lang
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM lm_data_transl " .
            " WHERE id = " . $ilDB->quote($a_id, "integer") .
            " AND lang = " . $ilDB->quote($a_lang, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Copy all translations of an object
     */
    public static function copy(
        string $a_source_id,
        string $a_target_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM lm_data_transl " .
            " WHERE id = " . $ilDB->quote($a_source_id, "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $lmobjtrans = new ilLMObjTranslation($a_target_id, $rec["lang"]);
            $lmobjtrans->setTitle($rec["title"]);
            $lmobjtrans->setShortTitle($rec["short_title"]);
            $lmobjtrans->save();
        }
    }
}
