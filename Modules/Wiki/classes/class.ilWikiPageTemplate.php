<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wiki page template
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesWiki
 */
class ilWikiPageTemplate
{
    /**
     * @var ilDB
     */
    protected $db;

    const TYPE_ALL = 0;
    const TYPE_NEW_PAGES = 1;
    const TYPE_ADD_TO_PAGE = 2;

    protected $wiki_id;
    protected $ilDB;

    /**
     * Constructor
     *
     * @param int $a_wiki_id wiki id
     */
    public function __construct($a_wiki_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->wiki_id = $a_wiki_id;
        $this->db = $ilDB;
    }

    /**
     * Get all info
     */
    public function getAllInfo($a_type = self::TYPE_ALL)
    {
        $and = "";
        if ($a_type == self::TYPE_NEW_PAGES) {
            $and = " AND t.new_pages = " . $this->db->quote(1, "integer");
        }
        if ($a_type == self::TYPE_ADD_TO_PAGE) {
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
     *
     * @param int $a_id wiki page id
     */
    public function save($a_id, $a_new_pages = 0, $a_add_to_page = 0)
    {
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
     *
     * @param int $a_id wiki page id
     */
    public function remove($a_id)
    {
        $this->db->manipulate(
            "DELETE FROM wiki_page_template WHERE " .
            " wiki_id = " . $this->db->quote($this->wiki_id, "integer") .
            " AND wpage_id = " . $this->db->quote($a_id, "integer")
        );
    }
    
    /**
     * Is page set as template?
     *
     * @param int $a_id wiki page id
     * @return type bool
     */
    public function isPageTemplate($a_id)
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
