<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

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
    private ilLogger $log;
    
    public function __construct()
    {
        global $DIC;
        
        $this->log = $DIC->logger()->auth();
        
        $this->rad_settings = ilRadiusSettings::_getInstance();

        $this->writer = new ilXmlWriter();
    }
    
    /**
     * Create new ILIAS account
     *
     * @access public
     *
     * @param string external username
     */
    public function create(string $a_username) : string
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
        
        $importParser = new ilUserImportParser();
        $importParser->setXMLContent($this->writer->xmlDumpMem(false));
        $importParser->setRoleAssignment(array($this->rad_settings->getDefaultRole() => $this->rad_settings->getDefaultRole()));
        $importParser->setFolderId(7);
        $importParser->startParsing();
        
        return $new_name;
    }
}
