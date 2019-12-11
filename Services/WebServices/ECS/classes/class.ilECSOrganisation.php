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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesWebServicesECS
*/

class ilECSOrganisation
{
    protected $json_obj;
    protected $name;
    protected $abbr;


    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct()
    {
    }

    /**
     * load from json
     *
     * @access public
     * @param object json representation
     * @throws ilException
     */
    public function loadFromJson($a_json)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];

        if (!is_object($a_json)) {
            $ilLog->write(__METHOD__ . ': Cannot load from JSON. No object given.');
            throw new ilException('Cannot parse ECSParticipant.');
        }
        $this->name = $a_json->name;
        $this->abbr = $a_json->abbr;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get abbreviation
     * @return string
     */
    public function getAbbreviation()
    {
        return $this->abbr;
    }
}
