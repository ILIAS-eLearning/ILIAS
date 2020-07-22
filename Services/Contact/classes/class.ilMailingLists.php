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

require_once "Services/Contact/classes/class.ilMailingList.php";

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailingLists
{
    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var ilObjUser
     */
    private $user;

    /**
     * @var null|ilMailingList
     */
    private $ml = null;

    /**
     * ilMailingLists constructor.
     * @param ilObjUser $a_user
     */
    public function __construct(ilObjUser $a_user)
    {
        global $DIC;

        $this->db = $DIC['ilDB'];
        $this->user = $a_user;
    }

    public function get($id = 0)
    {
        return new ilMailingList($this->user, $id);
    }
    
    public function getSelected($a_ids = array())
    {
        $entries = array();
        
        if (is_array($a_ids) && !empty($a_ids)) {
            $counter = 0;
            while ($id = @array_pop($a_ids)) {
                $entries[$counter] = new ilMailingList($this->user, $id);
                
                ++$counter;
            }
        }
        
        return $entries;
    }
    
    public function getAll()
    {
        $res = $this->db->queryf(
            '
			SELECT * FROM addressbook_mlist
			WHERE user_id = %s',
            array('integer'),
            array($this->user->getId())
        );
        
        $entries = array();
        
        $counter = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $tmpObj = new ilMailingList($this->user, 0);
            $tmpObj->setId($row->ml_id);
            $tmpObj->setUserId($row->user_id);
            $tmpObj->setTitle($row->title);
            $tmpObj->setDescription($row->description);
            $tmpObj->setCreatedate($row->createdate);
            $tmpObj->setChangedate($row->changedae);
            $tmpObj->setMode($row->lmode);
            
            $entries[$counter] = $tmpObj;
            
            unset($tmpObj);
            
            ++$counter;
        }
        
        return $entries;
    }
    
    public function mailingListExists($a_list_name)
    {
        $ml_id = substr($a_list_name, strrpos($a_list_name, '_') + 1);

        if (!is_numeric($ml_id) || $ml_id <= 0) {
            return false;
        } else {
            $this->setCurrentMailingList($ml_id);
        }
        
        return true;
    }
    
    public function setCurrentMailingList($id = 0)
    {
        $this->ml = $this->get($id);
    }

    /**
     * @return ilMailingList
     */
    public function getCurrentMailingList()
    {
        return $this->ml;
    }
        
    public function deleteTemporaryLists()
    {
        foreach ($this->getAll() as $mlist) {
            if ($mlist->getMode() == ilMailingList::MODE_TEMPORARY) {
                $mlist->delete();
            }
        }
    }
}
