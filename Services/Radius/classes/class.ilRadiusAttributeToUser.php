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
* @ingroup ServicesRadius
*/
class ilRadiusAttributeToUser
{
    /**
     * Constructor
     *
     * @access public
     *
     */
    public function __construct()
    {
        global $ilLog;
        
        $this->log = $ilLog;
        
        include_once('Services/Radius/classes/class.ilRadiusSettings.php');
        $this->rad_settings = ilRadiusSettings::_getInstance();

        include_once('./Services/Xml/classes/class.ilXmlWriter.php');
        $this->writer = new ilXmlWriter();
    }
    
    /**
     * Create new ILIAS account
     *
     * @access public
     *
     * @param string external username
     */
    public function create($a_username)
    {
        $this->writer->xmlStartTag('Users');
        
        // Single users
        // Required fields
        // Create user
        $this->writer->xmlStartTag('User', array('Action' => 'Insert'));
        $this->writer->xmlElement('Login', array(), $new_name = ilAuthUtils::_generateLogin($a_username));
                
        // Assign to role only for new users
        $this->writer->xmlElement('Role', array('Id' => $this->rad_settings->getDefaultRole(),
            'Type' => 'Global',
            'Action' => 'Assign'), '');

        $this->writer->xmlElement('Active', array(), "true");
        $this->writer->xmlElement('TimeLimitOwner', array(), 7);
        $this->writer->xmlElement('TimeLimitUnlimited', array(), 1);
        $this->writer->xmlElement('TimeLimitFrom', array(), time());
        $this->writer->xmlElement('TimeLimitUntil', array(), time());
        $this->writer->xmlElement('AuthMode', array('type' => 'radius'), 'radius');
        $this->writer->xmlElement('ExternalAccount', array(), $a_username);
            
        $this->writer->xmlEndTag('User');
        $this->writer->xmlEndTag('Users');
        $this->log->write('Radius: Started creation of user: ' . $new_name);
        
        include_once './Services/User/classes/class.ilUserImportParser.php';
        $importParser = new ilUserImportParser();
        $importParser->setXMLContent($this->writer->xmlDumpMem(false));
        $importParser->setRoleAssignment(array($this->rad_settings->getDefaultRole() => $this->rad_settings->getDefaultRole()));
        $importParser->setFolderId(7);
        $importParser->startParsing();
        
        return $new_name;
    }
}
