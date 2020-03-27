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
* Represents an entry for the search command queue
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroupServicesSearch
*/
class ilSearchCommandQueueElement
{
    const UPDATE = 'update';
    const DELETE = 'delete';
    const CREATE = 'create';
    const RESET = 'reset';

    private $obj_id;
    private $obj_type;
    private $command;
    private $last_update;
    private $finished;
    
    /**
     * Constructor
     */
    public function __construct()
    {
    }
    
    /**
     * set obj_id
     */
    public function setObjId($a_id)
    {
        $this->obj_id = $a_id;
    }
    
    /**
     * get obj_id
     */
    public function getObjId()
    {
        return $this->obj_id;
    }
    
    /**
     * set obj_type
     */
    public function setObjType($a_type)
    {
        $this->obj_type = $a_type;
    }
    
    /**
     * get obj_type
     */
    public function getObjType()
    {
        return $this->obj_type;
    }
    
    /**
     * set command
     */
    public function setCommand($a_command)
    {
        $this->command = $a_command;
    }
    
    /**
     * get command
     */
    public function getCommand()
    {
        return $this->command;
    }
    
    /**
     * set last_update
     */
    public function setLastUpdate(ilDateTime $date_time)
    {
        $this->last_update = $date_time;
    }
    
    /**
     * get last update
     */
    public function getLastUpdate()
    {
        return is_object($this->last_update) ? $this->last_update : null;
    }
    
    /**
     * set finsihed
     */
    public function setFinished($a_finished)
    {
        $this->finished = $a_finished;
    }
    
    /**
     * get finished
     */
    public function getFinished()
    {
        return (bool) $this->finished;
    }
}
