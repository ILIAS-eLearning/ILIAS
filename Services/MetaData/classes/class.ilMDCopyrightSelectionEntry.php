<?php
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
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesMetaData
*/
class ilMDCopyrightSelectionEntry
{
    protected $logger = null;
    protected $db;
    
    private $entry_id;
    private $title;
    private $decription;
    private $copyright;
    private $costs;
    private $language;
    private $copyright_and_other_restrictions;

    /**
     * @var integer
     */
    protected $outdated;

    /**
     * order position in the MDCopyrightTableGUI
     * @var integer
     */
    protected $order_position;
    

    /**
     * Constructor
     *
     * @access public
     * @param int entry id
     *
     */
    public function __construct($a_entry_id)
    {
        global $DIC;
        
        
        $this->logger = $GLOBALS['DIC']->logger()->meta();
        $this->db = $GLOBALS['DIC']->database();
        $this->entry_id = $a_entry_id;
        $this->read();
    }
    
    /**
     * get entries
     *
     * @return ilMDCopyrightSelectionEntry[]
     * @access public
     * @static
     *
     */
    public static function _getEntries()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT entry_id FROM il_md_cpr_selections ORDER BY is_default DESC, position ASC";
        $res = $ilDB->query($query);

        $entries = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $entries[] = new ilMDCopyrightSelectionEntry($row->entry_id);
        }
        return $entries;
    }
    
    /**
     * Lookup copyright title.
     * Currently used for export of meta data
     * @param type $a_cp_string
     */
    public static function lookupCopyyrightTitle($a_cp_string)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$entry_id = self::_extractEntryId($a_cp_string)) {
            return $a_cp_string;
        }
                
        $query = "SELECT title FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $ilDB->quote($entry_id) . " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->title ? $row->title : '';
    }


    /**
     * lookup copyright by entry id
     *
     * @access public
     * @static
     *
     * @param string copyright string il_copyright_entry__IL_INST_ID__ENTRY_ID
     */
    public static function _lookupCopyright($a_cp_string)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$entry_id = self::_extractEntryId($a_cp_string)) {
            return $a_cp_string;
        }
                
        $query = "SELECT copyright FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $ilDB->quote($entry_id) . " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->copyright ? $row->copyright : '';
    }

    /**
     * @param $copyright_text
     * @return int
     * @throws ilDatabaseException
     */
    public static function lookupCopyrightByText($copyright_text)
    {
        global $DIC;

        $db = $DIC->database();

        $query = 'SELECT entry_id FROM il_md_cpr_selections ' .
            'WHERE copyright = ' . $db->quote($copyright_text, 'text');
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->entry_id;
        }
        return 0;
    }
    
    /**
     * extract entry id
     *
     * @access public
     * @static
     *
     * @param
     * @return integer
     */
    public static function _extractEntryId($a_cp_string)
    {
        if (!preg_match('/il_copyright_entry__([0-9]+)__([0-9]+)/', $a_cp_string, $matches)) {
            return 0;
        }
        if ($matches[1] != IL_INST_ID) {
            return 0;
        }
        return $matches[2] ? $matches[2] : 0;
    }
    
    /**
     * get usage
     *
     * @access public
     * @param
     *
     */
    public function getUsage()
    {
        return $this->usage;
    }
    
    /**
     * get entry id
     *
     * @access public
     * @param
     *
     */
    public function getEntryId()
    {
        return $this->entry_id;
    }

    /**
     * Get if the entry is default
     * No setter for this.
     */
    public function getIsDefault()
    {
        $query = "SELECT is_default FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $this->db->quote($this->entry_id, 'integer');

        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_DEFAULT);
        
        return $row['is_default'];
    }

    /**
     * Set copyright element as outdated and not usable anymore
     * @param $a_value
     */
    public function setOutdated($a_value)
    {
        $this->outdated = (int) $a_value;
    }

    /**
     * @return int
     */
    public function getOutdated()
    {
        return $this->outdated;
    }

    /**
     * Get default
     */
    public static function getDefault()
    {
        global $DIC;

        $db = $DIC->database();

        $query = "SELECT entry_id FROM il_md_cpr_selections " .
            "WHERE is_default = " . $db->quote(1, 'integer');

        $res = $db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_DEFAULT);

        return $row['entry_id'];
    }
    
    /**
     * set title
     *
     * @access public
     * @param string title
     *
     */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    
    /**
     * get title
     *
     * @access public
     *
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * set description
     *
     * @access public
     * @param string description
     *
     */
    public function setDescription($a_desc)
    {
        $this->description = $a_desc;
    }
    
    /**
     * get description
     *
     * @access public
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * set copyright
     *
     * @access public
     * @param string $copyright
     *
     */
    public function setCopyright($a_copyright)
    {
        $this->copyright = $a_copyright;
    }
    
    /**
     * get copyright
     *
     * @access publi
     */
    public function getCopyright()
    {
        return $this->copyright;
    }
    
    /**
     * set costs
     *
     * @access public
     * @param
     *
     */
    public function setCosts($a_costs)
    {
        $this->costs = $a_costs;
    }
    
    /**
     * get costs
     *
     * @access public
     */
    public function getCosts()
    {
        return $this->costs;
    }
    
    /**
     * set language
     *
     * @access public
     * @param string language key
     *
     */
    public function setLanguage($a_lang_key)
    {
        $this->language = $a_lang_key;
    }
    
    /**
     * get language
     *
     * @access public
     *
     */
    public function getLanguage()
    {
        return $this->language;
    }
    
    /**
     * set copyright and other restrictions
     *
     * @access public
     * @param bool copyright and other restrictions
     */
    public function setCopyrightAndOtherRestrictions($a_status)
    {
        $this->copyright_and_other_restrictions = $a_status;
    }
    
    /**
     * get copyright and other restrictions
     *
     * @access public
     * @param
     *
     */
    public function getCopyrightAndOtherRestrictions()
    {
        // Fixed
        return true;
    }

    /**
     * Set the order position in the table of copyrights.
     * @param $a_position integer
     */
    public function setOrderPosition($a_position)
    {
        $this->order_position = (int) $a_position;
    }

    /**
     * Get the order position in the table of copyrights.
     * @return int
     */
    public function getOrderPosition()
    {
        return $this->order_position;
    }

    protected function getNextOrderPosition()
    {
        $query = "SELECT count(entry_id) total FROM il_md_cpr_selections";
        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return $row['total'] + 1;
    }

    /**
     * Add entry
     *
     * @access public
     */
    public function add()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $next_id = $ilDB->nextId('il_md_cpr_selections');
        
        $ilDB->insert('il_md_cpr_selections', array(
            'entry_id'			=> array('integer',$next_id),
            'title'				=> array('text',$this->getTitle()),
            'description'		=> array('clob',$this->getDescription()),
            'copyright'			=> array('clob',$this->getCopyright()),
            'language'			=> array('text',$this->getLanguage()),
            'costs'				=> array('integer',$this->getCosts()),
            'cpr_restrictions'	=> array('integer',$this->getCopyrightAndOtherRestrictions()),
            'position'			=> array('integer', $this->getNextOrderPosition())
        ));
        $this->entry_id = $next_id;
        return true;
    }
    
    /**
     * update
     *
     * @access public
     *
     */
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->update('il_md_cpr_selections', array(
            'title'				=> array('text',$this->getTitle()),
            'description'		=> array('clob',$this->getDescription()),
            'copyright'			=> array('clob',$this->getCopyright()),
            'language'			=> array('text',$this->getLanguage()),
            'costs'				=> array('integer',$this->getCosts()),
            'cpr_restrictions'	=> array('integer',$this->getCopyrightAndOtherRestrictions()),
            'outdated'			=> array('integer',$this->getOutdated()),
            'position'			=> array('integer',$this->getOrderPosition())
            ), array(
                'entry_id'			=> array('integer',$this->getEntryId())
        ));
        return true;
    }
    
    /**
     * delete
     *
     * @access public
     *
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $this->db->quote($this->getEntryId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }
    
    /**
     * validate
     *
     * @access public
     * @param
     *
     */
    public function validate()
    {
        if (!strlen($this->getTitle())) {
            return false;
        }
        return true;
    }
    
    /**
     * Read entry
     *
     * @access private
     * @param
     *
     */
    private function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $this->db->quote($this->entry_id, 'integer') . " " .
            "ORDER BY is_default DESC, position ASC ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setTitle($row->title);
            $this->setDescription($row->description);
            $this->setCopyright($row->copyright);
            $this->setLanguage($row->language);
            $this->setCosts($row->costs);
            $this->setOutdated($row->outdated);
            $this->setOrderPosition($row->position);
            // Fixed
            $this->setCopyrightAndOtherRestrictions(true);
        }
        
        $query = "SELECT count(meta_rights_id) used FROM il_meta_rights " .
            "WHERE description = " . $ilDB->quote('il_copyright_entry__' . IL_INST_ID . '__' . $this->getEntryId(), 'text');
        
        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        $this->usage = $row->used;
    }

    /**
     * Create identifier for entry id
     * @param $a_entry_id
     * @return string
     */
    public static function createIdentifier($a_entry_id)
    {
        return 'il_copyright_entry__' . IL_INST_ID . '__' . $a_entry_id;
    }
}
