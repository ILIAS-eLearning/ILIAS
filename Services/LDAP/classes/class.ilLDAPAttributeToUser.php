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
* Update/create ILIAS user account by given LDAP attributes according to user attribute mapping settings.
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesLDAP
*/
class ilLDAPAttributeToUser
{
    const MODE_INITIALIZE_ROLES = 1;

    private $modes = [];


    private $server_settings = null;
    private $role_assignment = null;
    private $db = null;
    
    private $user_data = array();
    private $setting = null;
    private $mapping = null;

    private $new_user_auth_mode = 'ldap';
    
    /**
     * Construct of ilLDAPAttribute2XML
     * Defines between LDAP and ILIAS user attributes
     *
     * @param object il
     */
    public function __construct(ilLDAPServer $a_server)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];
        $lng = $DIC['lng'];
        $ilLog = $DIC['ilLog'];
        
        // Initialise language object
        if (!is_object($lng)) {
            include_once './Services/Language/classes/class.ilLanguage.php';
            $lng = new ilLanguage('en');
        }
        
        $this->log = $ilLog;

        $this->server_settings = $a_server;
        $this->setting = $ilSetting;
        
        $this->initLDAPAttributeMapping();
    }
    
    // begin-patch ldap_multiple
    /**
     * Get server settings
     * @return ilLDAPServer
     */
    public function getServer()
    {
        return $this->server_settings;
    }
    // end-patch ldap_multiple
    
    /**
     * Set user data received from pear auth or by ldap_search
     *
     * @access public
     * @param array array of auth data. array('ilias_account1' => array(firstname => 'Stefan',...),...)
     *
     */
    public function setUserData($a_data)
    {
        $this->user_data = $a_data;
    }

    /**
     * Set auth mode for new users.
     * E.g. radius for radius authenticated user with ldap data source
     * @param string $a_authmode
     */
    public function setNewUserAuthMode($a_authmode)
    {
        $this->new_user_auth_mode = $a_authmode;
    }

    /**
     * Get auth mode for new users
     */
    public function getNewUserAuthMode()
    {
        return $this->new_user_auth_mode;
    }

    /**
     * Add import mode
     * @param $a_mode
     */
    public function addMode($a_mode)
    {
        if (is_array($this->modes) && !in_array($a_mode, $this->modes)) {
            $this->modes[] = $a_mode;
        }
    }

    /**
     * Check if mode is active
     * @param int $a_mode
     * @return bool
     */
    public function isModeActive($a_mode)
    {
        return is_array($this->modes) && in_array($a_mode, $this->modes);
    }
    
    
    /**
     * Create/Update non existing users
     *
     * @access public
     *
     */
    public function refresh()
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        
        $this->usersToXML();
        
        include_once './Services/User/classes/class.ilUserImportParser.php';
        include_once './Services/LDAP/classes/class.ilLDAPRoleAssignmentRules.php';
        
        $importParser = new ilUserImportParser();
        $importParser->setXMLContent($this->writer->xmlDumpMem(false));
        $importParser->setRoleAssignment(ilLDAPRoleAssignmentRules::getAllPossibleRoles($this->getServer()->getServerId()));
        $importParser->setFolderId(7);
        $importParser->startParsing();
        $debug = $importParser->getProtocol();
        #var_dump("<pre>",$this->writer->xmlDumpMem(),"</pre>");
        #print_r($this->writer->xmlDumpMem($format));
        
        return true;
    }

    /**
     * Parse role assignments for update of user account.
     * @param int $a_usr_id
     * @param string $a_external_account
     * @param array $user
     */
    protected function parseRoleAssignmentsForUpdate($a_usr_id, $a_external_account, $user)
    {
        $rules = $this->mapping->getRulesForUpdate();

        include_once './Services/LDAP/classes/class.ilLDAPRoleAssignmentRules.php';
        foreach (ilLDAPRoleAssignmentRules::getAssignmentsForUpdate(
            $this->getServer()->getServerId(),
            $a_usr_id,
            $a_external_account,
            $user
        ) as $role_data) {
            $this->writer->xmlElement(
                'Role',
                array('Id' => $role_data['id'],
                    'Type' => $role_data['type'],
                    'Action' => $role_data['action']),
                ''
            );
        }
    }

    /**
     * Parse role assignments for update of user account.
     * @param string $a_external_account
     * @param array $a_user
     */
    protected function parseRoleAssignmentsForCreation($a_external_account, $a_user)
    {
        include_once './Services/LDAP/classes/class.ilLDAPRoleAssignmentRules.php';
        foreach (ilLDAPRoleAssignmentRules::getAssignmentsForCreation(
            $this->getServer()->getServerId(),
            $a_external_account,
            $a_user
        ) as $role_data) {
            $this->writer->xmlElement(
                'Role',
                array('Id' => $role_data['id'],
                    'Type' => $role_data['type'],
                    'Action' => $role_data['action']),
                ''
            );
        }
    }
    
    /**
     * Create xml string of user according to mapping rules
     *
     * @access private
     *
     */
    private function usersToXML()
    {
        include_once('./Services/Xml/classes/class.ilXmlWriter.php');
        $this->writer = new ilXmlWriter();
        $this->writer->xmlStartTag('Users');
        
        $cnt_update = 0;
        $cnt_create = 0;
        
        // Single users
        foreach ($this->user_data as $external_account => $user) {
            $user['ilExternalAccount'] = $external_account;
            
            // Required fields
            if ($user['ilInternalAccount']) {
                $usr_id = ilObjUser::_lookupId($user['ilInternalAccount']);
                
                ++$cnt_update;
                // User exists
                $this->writer->xmlStartTag('User', array('Id' => $usr_id,'Action' => 'Update'));
                $this->writer->xmlElement('Login', array(), $user['ilInternalAccount']);
                $this->writer->xmlElement('ExternalAccount', array(), $external_account);
                $this->writer->xmlElement('AuthMode', array('type' => $this->getNewUserAuthMode()), null);

                if ($this->isModeActive(self::MODE_INITIALIZE_ROLES)) {
                    $this->parseRoleAssignmentsForCreation($external_account, $user);
                } else {
                    $this->parseRoleAssignmentsForUpdate($usr_id, $external_account, $user);
                }
                $rules = $this->mapping->getRulesForUpdate();
            } else {
                ++$cnt_create;
                // Create user
                $this->writer->xmlStartTag('User', array('Action' => 'Insert'));
                $this->writer->xmlElement('Login', array(), ilAuthUtils::_generateLogin($external_account));

                $this->parseRoleAssignmentsForCreation($external_account, $user);
                $rules = $this->mapping->getRules();
            }

            $this->writer->xmlElement('Active', array(), "true");
            $this->writer->xmlElement('TimeLimitOwner', array(), 7);
            $this->writer->xmlElement('TimeLimitUnlimited', array(), 1);
            $this->writer->xmlElement('TimeLimitFrom', array(), time());
            $this->writer->xmlElement('TimeLimitUntil', array(), time());
            
            // only for new users.
            // If auth_mode is 'default' (ldap) this status should remain.
            if (!$user['ilInternalAccount']) {
                $this->writer->xmlElement(
                    'AuthMode',
                    array('type' => $this->getNewUserAuthMode()),
                    $this->getNewUserAuthMode()
                );
                $this->writer->xmlElement('ExternalAccount', array(), $external_account);
            }
            foreach ($rules as $field => $data) {
                // Do Mapping: it is possible to assign multiple ldap attribute to one user data field
                if (!($value = $this->doMapping($user, $data))) {
                    continue;
                }
        
                switch ($field) {
                    case 'gender':
                        switch (strtolower($value)) {
                            case 'n':
                            case 'neutral':
                                $this->writer->xmlElement('Gender', array(), 'n');
                                break;

                            case 'm':
                            case 'male':
                                $this->writer->xmlElement('Gender', array(), 'm');
                                break;

                            case 'f':
                            case 'female':
                            default:
                                $this->writer->xmlElement('Gender', array(), 'f');
                                break;
                                
                        }
                        break;
                    
                    case 'firstname':
                        $this->writer->xmlElement('Firstname', array(), $value);
                        break;
                        
                    case 'lastname':
                        $this->writer->xmlElement('Lastname', array(), $value);
                        break;
                    
                    case 'hobby':
                        $this->writer->xmlElement('Hobby', array(), $value);
                        break;
                        
                    case 'title':
                        $this->writer->xmlElement('Title', array(), $value);
                        break;
                        
                    case 'institution':
                        $this->writer->xmlElement('Institution', array(), $value);
                        break;

                    case 'department':
                        $this->writer->xmlElement('Department', array(), $value);
                        break;

                    case 'street':
                        $this->writer->xmlElement('Street', array(), $value);
                        break;
                        
                    case 'city':
                        $this->writer->xmlElement('City', array(), $value);
                        break;

                    case 'zipcode':
                        $this->writer->xmlElement('PostalCode', array(), $value);
                        break;

                    case 'country':
                        $this->writer->xmlElement('Country', array(), $value);
                        break;
                        
                    case 'phone_office':
                        $this->writer->xmlElement('PhoneOffice', array(), $value);
                        break;

                    case 'phone_home':
                        $this->writer->xmlElement('PhoneHome', array(), $value);
                        break;

                    case 'phone_mobile':
                        $this->writer->xmlElement('PhoneMobile', array(), $value);
                        break;
                        
                    case 'fax':
                        $this->writer->xmlElement('Fax', array(), $value);
                        break;

                    case 'email':
                        $this->writer->xmlElement('Email', array(), $value);
                        break;
                        
                    case 'matriculation':
                        $this->writer->xmlElement('Matriculation', array(), $value);
                        break;
                        
                    /*
                    case 'photo':
                        $this->writer->xmlElement('PersonalPicture',array('encoding' => 'Base64','imagetype' => 'image/jpeg'),
                            base64_encode($this->convertInput($user[$value])));
                        break;
                    */
                    default:
                        // Handle user defined fields
                        if (substr($field, 0, 4) != 'udf_') {
                            continue 2;
                        }
                        $id_data = explode('_', $field);
                        if (!isset($id_data[1])) {
                            continue 2;
                        }
                        $this->initUserDefinedFields();
                        $definition = $this->udf->getDefinition($id_data[1]);
                        $this->writer->xmlElement(
                            'UserDefinedField',
                            array('Id' => $definition['il_id'],
                                                                            'Name' => $definition['field_name']),
                            $value
                        );
                        break;
                                                                            
                        
                }
            }
            $this->writer->xmlEndTag('User');
        }
        
        if ($cnt_create) {
            $this->log->write('LDAP: Started creation of ' . $cnt_create . ' users.');
        }
        if ($cnt_update) {
            $this->log->write('LDAP: Started update of ' . $cnt_update . ' users.');
        }
        $this->writer->xmlEndTag('Users');
    }
    
    /**
     * A value can be an array or a string
     * This function converts arrays to strings
     *
     * @access private
     * @param array or string value
     * @return string
     */
    private function convertInput($a_value)
    {
        if (is_array($a_value)) {
            return $a_value[0];
        } else {
            return $a_value;
        }
    }
    
    /**
     * doMapping
     *
     * @access private
     *
     */
    private function doMapping($user, $rule)
    {
        $mapping = trim(strtolower($rule['value']));
        
        if (strpos($mapping, ',') === false) {
            return $this->convertInput($user[$mapping]);
        }
        // Is multiple mapping
        
        $fields = explode(',', $mapping);
        $value = '';
        foreach ($fields as $field) {
            if (strlen($value)) {
                $value .= ' ';
            }
            $value .= ($this->convertInput($user[trim($field)]));
        }
        return $value ? $value : '';
    }
    
    
    
    private function initLDAPAttributeMapping()
    {
        include_once('Services/LDAP/classes/class.ilLDAPAttributeMapping.php');
        $this->mapping = ilLDAPAttributeMapping::_getInstanceByServerId($this->server_settings->getServerId());
    }
    
    private function initUserDefinedFields()
    {
        include_once('Services/User/classes/class.ilUserDefinedFields.php');
        $this->udf = ilUserDefinedFields::_getInstance();
    }
}
