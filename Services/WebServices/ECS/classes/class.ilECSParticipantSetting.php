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
* @ingroup ServicesWebServicesECS
*/
class ilECSParticipantSetting
{
    const AUTH_VERSION_4 = 1;
    const AUTH_VERSION_5 = 2;
    
    const PERSON_EPPN = 1;
    const PERSON_LUID = 2;
    const PERSON_LOGIN = 3;
    const PERSON_UID = 4;

    protected static $instances = array();


    // :TODO: what types are needed?
    const IMPORT_UNCHANGED = 0;
    const IMPORT_RCRS = 1;
    const IMPORT_CRS = 2;
    const IMPORT_CMS = 3;
    
    private $server_id = 0;
    private $mid = 0;
    private $export = false;
    private $import = false;
    private $import_type = 1;
    private $title = '';
    private $cname = '';
    private $token = true;
    private $dtoken = true;
    
    private $auth_version = self::AUTH_VERSION_4;
    private $person_type = self::PERSON_UID;
    

    private $export_types = array();
    private $import_types = array();

    private $exists = false;

    
    /**
     * Constructor
     *
     * @access private
     *
     */
    public function __construct($a_server_id, $mid)
    {
        $this->server_id = $a_server_id;
        $this->mid = $mid;
        $this->read();
    }
    
    /**
     * Get instance by server id and mid
     * @param type $a_server_id
     * @param type $mid
     * @return ilECSParticipantSetting
     */
    public static function getInstance($a_server_id, $mid)
    {
        if (self::$instances[$a_server_id . '_' . $mid]) {
            return self::$instances[$a_server_id . '_' . $mid];
        }
        return self::$instances[$a_server_id . '_' . $mid] = new self($a_server_id, $mid);
    }


    /**
     * Get server id
     * @return int
     */
    public function getServerId()
    {
        return $this->server_id;
    }

    public function setMid($a_mid)
    {
        $this->mid = $a_mid;
    }

    public function getMid()
    {
        return $this->mid;
    }

    public function enableExport($a_status)
    {
        $this->export = $a_status;
    }

    public function isExportEnabled()
    {
        return (bool) $this->export;
    }

    public function enableImport($a_status)
    {
        $this->import = $a_status;
    }

    public function isImportEnabled()
    {
        return $this->import;
    }

    public function setImportType($a_type)
    {
        if ($a_type != self::IMPORT_UNCHANGED) {
            $this->import_type = $a_type;
        }
    }

    public function getImportType()
    {
        return $this->import_type;
    }

    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getCommunityName()
    {
        return $this->cname;
    }

    public function setCommunityName($a_name)
    {
        $this->cname = $a_name;
    }
    
    public function isTokenEnabled()
    {
        return (bool) $this->token;
    }
    
    public function enableToken($a_stat)
    {
        $this->token = $a_stat;
    }
    
    public function setExportTypes($a_types)
    {
        $this->export_types = $a_types;
    }
    
    public function getExportTypes()
    {
        return $this->export_types;
    }
    
    public function setImportTypes($a_types)
    {
        $this->import_types = $a_types;
    }
    
    public function isDeprecatedTokenEnabled()
    {
        return (bool) $this->dtoken;
    }
    
    public function enableDeprecatedToken($a_stat)
    {
        $this->dtoken = $a_stat;
    }
    
    public function getImportTypes()
    {
        return $this->import_types;
    }

    private function exists()
    {
        return $this->exists;
    }
    
    /**
     * Update
     * Calls create automatically when no entry exists
     */
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!$this->exists()) {
            return $this->create();
        }
        $query = 'UPDATE ecs_part_settings ' .
            'SET ' .
            'sid = ' . $ilDB->quote((int) $this->getServerId(), 'integer') . ', ' .
            'mid = ' . $ilDB->quote((int) $this->getMid(), 'integer') . ', ' .
            'export = ' . $ilDB->quote((int) $this->isExportEnabled(), 'integer') . ', ' .
            'import = ' . $ilDB->quote((int) $this->isImportEnabled(), 'integer') . ', ' .
            'import_type = ' . $ilDB->quote((int) $this->getImportType(), 'integer') . ', ' .
            'title = ' . $ilDB->quote($this->getTitle(), 'text') . ', ' .
            'cname = ' . $ilDB->quote($this->getCommunityName(), 'text') . ', ' .
            'token = ' . $ilDB->quote($this->isTokenEnabled(), 'integer') . ', ' .
            'dtoken = ' . $ilDB->quote($this->isDeprecatedTokenEnabled(), 'integer') . ', ' .
            'export_types = ' . $ilDB->quote(serialize($this->getExportTypes()), 'text') . ', ' .
            'import_types = ' . $ilDB->quote(serialize($this->getImportTypes()), 'text') . ' ' .
            'WHERE sid = ' . $ilDB->quote((int) $this->getServerId(), 'integer') . ' ' .
            'AND mid  = ' . $ilDB->quote((int) $this->getMid(), 'integer');
        $aff = $ilDB->manipulate($query);
        return true;
    }

    private function create()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'INSERT INTO ecs_part_settings ' .
            '(sid,mid,export,import,import_type,title,cname,token,dtoken,export_types, import_types) ' .
            'VALUES( ' .
            $ilDB->quote($this->getServerId(), 'integer') . ', ' .
            $ilDB->quote($this->getMid(), 'integer') . ', ' .
            $ilDB->quote((int) $this->isExportEnabled(), 'integer') . ', ' .
            $ilDB->quote((int) $this->isImportEnabled(), 'integer') . ', ' .
            $ilDB->quote((int) $this->getImportType(), 'integer') . ', ' .
            $ilDB->quote($this->getTitle(), 'text') . ', ' .
            $ilDB->quote($this->getCommunityName(), 'text') . ', ' .
            $ilDB->quote($this->isTokenEnabled(), 'integer') . ', ' .
            $ilDB->quote($this->isDeprecatedTokenEnabled(), 'integer') . ', ' .
            $ilDB->quote(serialize($this->getExportTypes()), 'text') . ', ' .
            $ilDB->quote(serialize($this->getImportTypes()), 'text') . ' ' .
            ')';
        $aff = $ilDB->manipulate($query);
        return true;
    }

    /**
     * Delete one participant entry
     * @global <type> $ilDB
     * @return <type>
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_part_settings ' .
            'WHERE sid = ' . $ilDB->quote($this->getServerId(), 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($this->getMid(), 'integer');
        $ilDB->manipulate($query);
        return true;
    }

    /**
     * Read stored entry
     * @return <type>
     */
    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM ecs_part_settings ' .
            'WHERE sid = ' . $ilDB->quote($this->getServerId(), 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($this->getMid(), 'integer');

        $res = $ilDB->query($query);

        $this->exists = ($res->numRows() ? true : false);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->enableExport($row->export);
            $this->enableImport($row->import);
            $this->setImportType($row->import_type);
            $this->setTitle($row->title);
            $this->setCommunityName($row->cname);
            $this->enableToken($row->token);
            $this->enableDeprecatedToken($row->dtoken);
            
            $this->setExportTypes((array) unserialize($row->export_types));
            $this->setImportTypes((array) unserialize($row->import_types));
        }
        return true;
    }
    
    public static function deleteByServerId($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_events' .
            ' WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }
}
