<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesMetaData
 */
class ilMDCopyrightSelectionEntry
{
    protected ilLogger $logger;
    protected ilDBInterface $db;

    private int $entry_id;
    private string $title = '';
    private string $description = '';
    private string $copyright = '';
    private bool $costs = false;
    private string $language = '';
    private bool $copyright_and_other_restrictions = true;
    private bool $usage = false;

    protected bool $outdated = false;

    protected int $order_position = 0;

    public function __construct(int $a_entry_id)
    {
        global $DIC;

        $this->logger = $DIC->logger()->meta();
        $this->db = $DIC->database();
        $this->entry_id = $a_entry_id;
        $this->read();
    }

    /**
     * @return ilMDCopyrightSelectionEntry[]
     */
    public static function _getEntries() : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT entry_id FROM il_md_cpr_selections ORDER BY is_default DESC, position ASC";
        $res = $ilDB->query($query);

        $entries = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $entries[] = new ilMDCopyrightSelectionEntry((int) $row->entry_id);
        }
        return $entries;
    }

    public static function lookupCopyyrightTitle(string $a_cp_string) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$entry_id = self::_extractEntryId($a_cp_string)) {
            return $a_cp_string;
        }

        $query = "SELECT title FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $ilDB->quote($entry_id, ilDBConstants::T_INTEGER) . " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->title ?: '';
    }

    public static function _lookupCopyright(string $a_cp_string) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$entry_id = self::_extractEntryId($a_cp_string)) {
            return $a_cp_string;
        }

        $query = "SELECT copyright FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $ilDB->quote($entry_id, ilDBConstants::T_INTEGER) . " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->copyright ?: '';
    }

    public static function lookupCopyrightByText(string $copyright_text) : int
    {
        global $DIC;

        $db = $DIC->database();

        $query = 'SELECT entry_id FROM il_md_cpr_selections ' .
            'WHERE copyright = ' . $db->quote($copyright_text, 'text');
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->entry_id;
        }
        return 0;
    }

    public static function _extractEntryId(string $a_cp_string) : int
    {
        if (!preg_match('/il_copyright_entry__([0-9]+)__([0-9]+)/', $a_cp_string, $matches)) {
            return 0;
        }
        if ($matches[1] != IL_INST_ID) {
            return 0;
        }
        return (int) ($matches[2] ?? 0);
    }

    public function getUsage() : bool
    {
        return $this->usage;
    }

    public function getEntryId() : int
    {
        return $this->entry_id;
    }

    /**
     * Get if the entry is default
     * No setter for this.
     */
    public function getIsDefault() : bool
    {
        $query = "SELECT is_default FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $this->db->quote($this->entry_id, 'integer');

        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_DEFAULT);

        return (bool) $row['is_default'];
    }

    public function setOutdated(bool $a_value) : void
    {
        $this->outdated = $a_value;
    }

    public function getOutdated() : bool
    {
        return $this->outdated;
    }

    public static function getDefault() : int
    {
        global $DIC;

        $db = $DIC->database();

        $query = "SELECT entry_id FROM il_md_cpr_selections " .
            "WHERE is_default = " . $db->quote(1, 'integer');

        $res = $db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_DEFAULT);

        return (int) $row['entry_id'];
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setDescription(string $a_desc) : void
    {
        $this->description = $a_desc;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setCopyright(string $a_copyright) : void
    {
        $this->copyright = $a_copyright;
    }

    public function getCopyright() : string
    {
        return $this->copyright;
    }

    public function setCosts(bool $a_costs) : void
    {
        $this->costs = $a_costs;
    }

    public function getCosts() : bool
    {
        return $this->costs;
    }

    public function setLanguage(string $a_lang_key) : void
    {
        $this->language = $a_lang_key;
    }

    public function getLanguage() : string
    {
        return $this->language;
    }

    public function setCopyrightAndOtherRestrictions(bool $a_status) : void
    {
        $this->copyright_and_other_restrictions = $a_status;
    }

    public function getCopyrightAndOtherRestrictions() : bool
    {
        return $this->copyright_and_other_restrictions;
    }

    public function setOrderPosition(int $a_position) : void
    {
        $this->order_position = $a_position;
    }

    public function getOrderPosition() : int
    {
        return $this->order_position;
    }

    protected function getNextOrderPosition() : int
    {
        $query = "SELECT count(entry_id) total FROM il_md_cpr_selections";
        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return $row['total'] + 1;
    }

    public function add() : bool
    {
        $next_id = $this->db->nextId('il_md_cpr_selections');

        $this->db->insert('il_md_cpr_selections', array(
            'entry_id' => array('integer', $next_id),
            'title' => array('text', $this->getTitle()),
            'description' => array('clob', $this->getDescription()),
            'copyright' => array('clob', $this->getCopyright()),
            'language' => array('text', $this->getLanguage()),
            'costs' => array('integer', $this->getCosts()),
            'cpr_restrictions' => array('integer', $this->getCopyrightAndOtherRestrictions()),
            'position' => array('integer', $this->getNextOrderPosition())
        ));
        $this->entry_id = $next_id;
        return true;
    }

    public function update() : bool
    {
        $this->db->update('il_md_cpr_selections', array(
            'title' => array('text', $this->getTitle()),
            'description' => array('clob', $this->getDescription()),
            'copyright' => array('clob', $this->getCopyright()),
            'language' => array('text', $this->getLanguage()),
            'costs' => array('integer', $this->getCosts()),
            'cpr_restrictions' => array('integer', $this->getCopyrightAndOtherRestrictions()),
            'outdated' => array('integer', $this->getOutdated()),
            'position' => array('integer', $this->getOrderPosition())
        ), array(
            'entry_id' => array('integer', $this->getEntryId())
        ));
        return true;
    }

    public function delete() : void
    {
        $query = "DELETE FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $this->db->quote($this->getEntryId(), 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function validate() : bool
    {
        return $this->getTitle() !== '';
    }

    private function read() : void
    {
        $query = "SELECT * FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $this->db->quote($this->entry_id, 'integer') . " " .
            "ORDER BY is_default DESC, position ASC ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setTitle($row->title);
            $this->setDescription($row->description);
            $this->setCopyright($row->copyright);
            $this->setLanguage($row->language);
            $this->setCosts((bool) $row->costs);
            $this->setOutdated((bool) $row->outdated);
            $this->setOrderPosition((int) $row->position);
            // Fixed
            $this->setCopyrightAndOtherRestrictions(true);
        }

        $query = "SELECT count(meta_rights_id) used FROM il_meta_rights " .
            "WHERE description = " . $this->db->quote(
                'il_copyright_entry__' . IL_INST_ID . '__' . $this->getEntryId(),
                'text'
            );

        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        $this->usage = (bool) $row->used;
    }

    public static function createIdentifier(int $a_entry_id) : string
    {
        return 'il_copyright_entry__' . IL_INST_ID . '__' . $a_entry_id;
    }
}
