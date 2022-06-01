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
 * Wiki page template
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiPageTemplate
{
    public const TYPE_ALL = 0;
    public const TYPE_NEW_PAGES = 1;
    public const TYPE_ADD_TO_PAGE = 2;

    protected ilDBInterface $db;
    protected int $wiki_id;

    public function __construct(
        int $a_wiki_id
    ) {
        global $DIC;

        $this->wiki_id = $a_wiki_id;
        $this->db = $DIC->database();
    }

    public function getAllInfo(
        int $a_type = self::TYPE_ALL
    ) : array {
        $and = "";
        if ($a_type === self::TYPE_NEW_PAGES) {
            $and = " AND t.new_pages = " . $this->db->quote(1, "integer");
        }
        if ($a_type === self::TYPE_ADD_TO_PAGE) {
            $and = " AND t.add_to_page = " . $this->db->quote(1, "integer");
        }

        $set = $this->db->query(
            $q = "SELECT t.wiki_id, t.wpage_id, p.title, t.new_pages, t.add_to_page FROM wiki_page_template t JOIN il_wiki_page p ON " .
            " (t.wpage_id = p.id) " .
            " WHERE t.wiki_id = " . $this->db->quote($this->wiki_id, "integer") .
            $and
        );
        $templates = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            $templates[] = $rec;
        }
        return $templates;
    }

    /**
     * Add wiki page template
     * @param int $a_id wiki page id
     */
    public function save(
        int $a_id,
        int $a_new_pages = 0,
        int $a_add_to_page = 0
    ) : void {
        if ($a_id <= 0) {
            return;
        }

        $set = $this->db->query(
            "SELECT * FROM wiki_page_template " .
            " WHERE wiki_id = " . $this->db->quote($this->wiki_id, "integer") .
            " AND wpage_id = " . $this->db->quote($a_id, "integer")
        );
        if (!$this->db->fetchAssoc($set)) {
            $this->db->manipulate("INSERT INTO wiki_page_template " .
                "(wiki_id, wpage_id, new_pages, add_to_page) VALUES (" .
                $this->db->quote($this->wiki_id, "integer") . "," .
                $this->db->quote($a_id, "integer") . "," .
                $this->db->quote($a_new_pages, "integer") . "," .
                $this->db->quote($a_add_to_page, "integer") .
                ")");
        } else {
            $this->db->manipulate(
                "UPDATE wiki_page_template SET " .
                " new_pages = " . $this->db->quote($a_new_pages, "integer") . "," .
                " add_to_page = " . $this->db->quote($a_add_to_page, "integer") .
                " WHERE wiki_id = " . $this->db->quote($this->wiki_id, "integer") .
                " AND wpage_id = " . $this->db->quote($a_id, "integer")
            );
        }
    }

    /**
     * Remove template status of a page
     * @param int $a_id wiki page id
     */
    public function remove(
        int $a_id
    ) : void {
        $this->db->manipulate(
            "DELETE FROM wiki_page_template WHERE " .
            " wiki_id = " . $this->db->quote($this->wiki_id, "integer") .
            " AND wpage_id = " . $this->db->quote($a_id, "integer")
        );
    }
    
    /**
     * Is page set as template?
     */
    public function isPageTemplate(int $a_id) : bool
    {
        $set = $this->db->query("SELECT t.wpage_id" .
            " FROM wiki_page_template t" .
            " JOIN il_wiki_page p ON " .
            " (t.wpage_id = p.id) " .
            " WHERE t.wiki_id = " . $this->db->quote($this->wiki_id, "integer") .
            " AND p.id = " . $this->db->quote($a_id, "integer"));
        return (bool) $this->db->numRows($set);
    }
}
