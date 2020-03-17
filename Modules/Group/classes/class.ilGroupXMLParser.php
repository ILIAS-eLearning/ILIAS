<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./Services/Xml/classes/class.ilSaxParser.php");
require_once('./Services/User/classes/class.ilObjUser.php');
include_once('./Services/Calendar/classes/class.ilDateTime.php');
include_once('./Modules/Group/classes/class.ilGroupParticipants.php');


/**
 * Group Import Parser
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.ilGroupXMLParser.php 15678 2008-01-06 20:40:55Z akill $
 *
 * @extends ilSaxParser

 */
class ilGroupXMLParser extends ilMDSaxParser implements ilSaxSubsetParser
{
    public static $CREATE = 1;
    public static $UPDATE = 2;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var ilObjGroup
     */
    private $group_obj;

    /**
     * @var bool
     */
    private $lom_parsing_active = false;


    /**
     * @var ilSaxController|null
     */
    protected $sax_controller = null;


    /**
     * @var ilAdvancedMDValueParser
     */
    protected $advanced_md_value_parser = null;


    private $participants = null;
    private $current_container_setting;
    private $sort = null;

    public $group_data;


    public $parent;
    public $counter;

    public $mode;
    public $grp;

    /**
     * Constructor
     *
     * @param	string		$a_xml_file		xml file
     *
     * @access	public
     */


    /**
     * ilGroupXMLParser constructor.
     * @param ilObjGroup $group
     * @param string $a_xml
     * @param int $a_parent_id
     */
    public function __construct(ilObjGroup $group, $a_xml, $a_parent_id)
    {
        define('EXPORT_VERSION', 2);

        parent::__construct(null);

        $this->sax_controller = new ilSaxController();

        $this->mode = ilGroupXMLParser::$CREATE;
        $this->group_obj = $group;
        $this->log = $GLOBALS['DIC']->logger()->grp();

        $this->setXMLContent($a_xml);

        // init md parsing
        $this->setMDObject(
            new ilMD(
                $this->group_obj->getId(),
                $this->group_obj->getId(),
                $this->group_obj->getType()
            )
        );

        // SET MEMBER VARIABLES
        $this->__pushParentId($a_parent_id);
    }

    public function __pushParentId($a_id)
    {
        $this->parent[] = $a_id;
    }
    public function __popParentId()
    {
        array_pop($this->parent);

        return true;
    }
    public function __getParentId()
    {
        return $this->parent[count($this->parent) - 1];
    }


    /**
     * set event handlers
     *
     * @param	resource	reference to the xml parser
     * @access	public
     */
    public function setHandlers($a_xml_parser)
    {
        $this->sax_controller->setHandlers($a_xml_parser);
        $this->sax_controller->setDefaultElementHandler($this);

        $this->advanced_md_value_parser = new ilAdvancedMDValueParser(
            $this->group_obj->getId()
        );

        $this->sax_controller->setElementHandler(
            $this->advanced_md_value_parser,
            'AdvancedMetaData'
        );
    }


    /**
     * start the parser
     */
    public function startParsing()
    {
        parent::startParsing();

        if ($this->mode == ilGroupXMLParser::$CREATE) {
            return is_object($this->group_obj) ? $this->group_obj->getRefId() : false;
        } else {
            return is_object($this->group_obj) ? $this->group_obj->update() : false;
        }
    }


    /**
     * handler for begin of element
     */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];

        if ($this->lom_parsing_active) {
            parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
            return;
        }

        switch ($a_name) {
            case "MetaData":
                $this->lom_parsing_active = true;
                parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;


            // GROUP DATA
            case "group":
                $this->group_data["admin"] = array();
                $this->group_data["member"] = array();

                $this->group_data["type"] = $a_attribs["type"];
                $this->group_data["id"] = $a_attribs["id"];

                break;

            case 'title':
                break;
                
            case "owner":
                $this->group_data["owner"] = $a_attribs["id"];
                break;

            case 'registration':
                $this->group_data['registration_type'] = $a_attribs['type'];
                $this->group_data['waiting_list_enabled'] = $a_attribs['waitingList'] == 'Yes' ? true : false;
                break;
            
            case 'period':
                $this->in_period = true;
                break;
                
            case 'maxMembers':
                $this->group_data['max_members_enabled'] = $a_attribs['enabled'] == 'Yes' ? true : false;
                break;

            case "admin":
                if (!isset($a_attribs['action']) || $a_attribs['action'] == "Attach") {
                    $this->group_data["admin"]["attach"][] = $a_attribs["id"];
                } elseif (isset($a_attribs['action']) || $a_attribs['action'] == "Detach") {
                    $this->group_data["admin"]["detach"][] = $a_attribs["id"];
                }
                
                if (isset($a_attribs['notification']) and $a_attribs['notification'] == 'Yes') {
                    $this->group_data['notifications'][] = $a_attribs['id'];
                }
                
                break;

            case "member":
                if (!isset($a_attribs['action']) || $a_attribs['action'] == "Attach") {
                    $GLOBALS['DIC']->logger()->grp()->debug('New member with id ' . $a_attribs['id']);
                    $this->group_data["member"]["attach"][] = $a_attribs["id"];
                } elseif (isset($a_attribs['action']) || $a_attribs['action'] == "Detach") {
                    $GLOBALS['DIC']->logger()->grp()->debug('Deprecated member with id ' . $a_attribs['id']);
                    $this->group_data["member"]["detach"][] = $a_attribs["id"];
                }

                break;

            case 'ContainerSetting':
                $this->current_container_setting = $a_attribs['id'];
                break;

            case 'Sort':

                if ($this->group_imported) {
                    $this->__initContainerSorting($a_attribs, $this->group_obj->getId());
                } else {
                    $this->sort = $a_attribs;
                }

                break;
            
            case 'WaitingListAutoFill':
            case 'CancellationEnd':
            case 'minMembers':
            case 'mailMembersType':
                break;
        }
    }


    public function handlerEndTag($a_xml_parser, $a_name)
    {
        if ($this->lom_parsing_active) {
            parent::handlerEndTag($a_xml_parser, $a_name);
        }

        switch ($a_name) {
            case 'MetaData':
                $this->lom_parsing_active = false;
                parent::handlerEndTag($a_xml_parser, $a_name);
                break;


            case "title":
                $this->group_data["title"] = trim($this->cdata);
                break;

            case "description":
                $this->group_data["description"] = trim($this->cdata);
                break;
                
            case 'information':
                $this->group_data['information'] = trim($this->cdata);
                break;

            case 'password':
                $this->group_data['password'] = trim($this->cdata);
                break;
                
            case 'maxMembers':
                $this->group_data['max_members'] = trim($this->cdata);
                break;

            case 'expiration':
                $this->group_data['expiration_end'] = trim($this->cdata);
                break;
                
            case 'start':
                if ($this->in_period) {
                    $this->group_data['period_start'] = trim($this->cdata);
                } else {
                    $this->group_data['expiration_start'] = trim($this->cdata);
                }
                break;
                
            case 'end':
                if ($this->in_period) {
                    $this->group_data['period_end'] = trim($this->cdata);
                } else {
                    $this->group_data['expiration_end'] = trim($this->cdata);
                }
                break;
            
            case 'period':
                $this->in_period = false;
                break;

            case "group":
                // NOW SAVE THE NEW OBJECT (if it hasn't been imported)
                $this->__save();
                break;
            
            case 'ContainerSetting':
                if ($this->current_container_setting) {
                    ilContainer::_writeContainerSetting(
                        $this->group_obj->getId(),
                        $this->current_container_setting,
                        $this->cdata
                    );
                }
                break;
                
            case 'WaitingListAutoFill':
                $this->group_data['auto_wait'] = trim($this->cdata);
                break;
            
            case 'CancellationEnd':
                if ((int) $this->cdata) {
                    $this->group_data['cancel_end'] = new ilDate((int) $this->cdata, IL_CAL_UNIX);
                }
                break;
                
            case 'minMembers':
                if ((int) $this->cdata) {
                    $this->group_data['min_members'] = (int) $this->cdata;
                }
                break;

            case 'showMembers':
                if ((int) $this->cdata) {
                    $this->group_data['show_members'] = (int) $this->cdata;
                }
                break;
            
            case 'mailMembersType':
                $this->group_data['mail_members_type'] = (int) $this->cdata;
                break;
                
        }
        $this->cdata = '';
    }


    /**
     * handler for character data
     */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        if ($this->lom_parsing_active) {
            parent::handlerCharacterData($a_xml_parser, $a_data);
        }

        $a_data = str_replace("<", "&lt;", $a_data);
        $a_data = str_replace(">", "&gt;", $a_data);

        if (!empty($a_data)) {
            $this->cdata .= $a_data;
        }
    }

    // PRIVATE
    public function __save()
    {
        if ($this->group_imported) {
            return true;
        }

        $this->group_obj->setImportId($this->group_data["id"]);
        $this->group_obj->setTitle($this->group_data["title"]);
        $this->group_obj->setDescription($this->group_data["description"]);
        $this->group_obj->setInformation((string) $this->group_data['information']);
        
        if (
            $this->group_data['period_start'] &&
            $this->group_data['period_end']) {
            $this->group_obj->setStart(new ilDate($this->group_data['period_start'], IL_CAL_UNIX));
            $this->group_obj->setEnd(new ilDate($this->group_data['period_end'], IL_CAL_UNIX));
        }
        
        $ownerChanged = false;
        if (isset($this->group_data["owner"])) {
            $owner = $this->group_data["owner"];
            if (!is_numeric($owner)) {
                $owner = ilUtil::__extractId($owner, IL_INST_ID);
            }
            if (is_numeric($owner) && $owner > 0) {
                $this->group_obj->setOwner($owner);
                $ownerChanged = true;
            }
        }

        /**
         * mode can be create or update
         */
        if ($this->mode == ilGroupXMLParser::$CREATE) {
            $this->group_obj->createReference();
            $this->group_obj->putInTree($this->__getParentId());
            $this->group_obj->setPermissions($this->__getParentId());
            if (
                array_key_exists('type', $this->group_data) &&
                $this->group_data['type'] == 'closed'
            ) {
                $this->group_obj->updateGroupType(GRP_TYPE_CLOSED);
            }
        } else {
            if (
                array_key_exists('type', $this->group_data) &&
                $this->group_data['type'] == 'closed'
            ) {
                $this->group_obj->updateGroupType(GRP_TYPE_CLOSED);
            } elseif (
                array_key_exists('type', $this->group_data) &&
                $this->group_data['type'] == 'open'
            ) {
                $this->group_obj->updateGroupType(GRP_TYPE_OPEN);
            }
        }
        // SET GROUP SPECIFIC DATA
        switch ($this->group_data['registration_type']) {
            case 'direct':
            case 'enabled':
                $flag = GRP_REGISTRATION_DIRECT;
                break;

            case 'disabled':
                $flag = GRP_REGISTRATION_DEACTIVATED;
                break;

            case 'confirmation':
                $flag = GRP_REGISTRATION_REQUEST;
                break;

            case 'password':
                $flag = GRP_REGISTRATION_PASSWORD;
                break;

            default:
                $flag = GRP_REGISTRATION_DIRECT;
        }
        $this->group_obj->setRegistrationType($flag);
        
        $end = new ilDateTime(time(), IL_CAL_UNIX);
        if ($this->group_data['expiration_end']) {
            $end = new ilDateTime($this->group_data['expiration_end'], IL_CAL_UNIX);
        }

        $start = clone $end;
        if ($this->group_data['expiration_start']) {
            $start = new ilDateTime($this->group_data['expiration_start'], IL_CAL_UNIX);
        }

        $this->group_obj->setRegistrationStart($start);
        $this->group_obj->setRegistrationEnd($end);
        $this->group_obj->setPassword($this->group_data['password']);
        $this->group_obj->enableUnlimitedRegistration(!isset($this->group_data['expiration_end']));
        $this->group_obj->enableMembershipLimitation($this->group_data['max_members_enabled']);
        $this->group_obj->setMaxMembers($this->group_data['max_members'] ? $this->group_data['max_members'] : 0);
        $this->group_obj->enableWaitingList($this->group_data['waiting_list_enabled']);
        
        $this->group_obj->setWaitingListAutoFill($this->group_data['auto_wait']);
        $this->group_obj->setCancellationEnd($this->group_data['cancel_end']);
        $this->group_obj->setMinMembers($this->group_data['min_members']);
        $this->group_obj->setShowMembers($this->group_data['show_members'] ? $this->group_data['show_members'] : 0);
        $this->group_obj->setMailToMembersType((int) $this->group_data['mail_members_type']);
        $this->group_obj->update();

        // ASSIGN ADMINS/MEMBERS
        $this->__assignMembers();

        $this->__pushParentId($this->group_obj->getRefId());

        if ($this->sort) {
            $this->__initContainerSorting($this->sort, $this->group_obj->getId());
        }

        $this->group_imported = true;

        return true;
    }

    public function __assignMembers()
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];

        $this->participants = new ilGroupParticipants($this->group_obj->getId());
        $this->participants->add($ilUser->getId(), IL_GRP_ADMIN);
        $this->participants->updateNotification($ilUser->getId(), $ilSetting->get('mail_grp_admin_notification', true));
        
        // attach ADMINs
        if (isset($this->group_data["admin"]["attach"]) && count($this->group_data["admin"]["attach"])) {
            foreach ($this->group_data["admin"]["attach"] as $user) {
                if ($id_data = $this->__parseId($user)) {
                    if ($id_data['local'] or $id_data['imported']) {
                        $this->participants->add($id_data['usr_id'], IL_GRP_ADMIN);
                        if (in_array($user, (array) $this->group_data['notifications'])) {
                            $this->participants->updateNotification($id_data['usr_id'], true);
                        }
                    }
                }
            }
        }
        // detach ADMINs
        if (isset($this->group_data["admin"]["detach"]) && count($this->group_data["admin"]["detach"])) {
            foreach ($this->group_data["admin"]["detach"] as $user) {
                if ($id_data = $this->__parseId($user)) {
                    if ($id_data['local'] or $id_data['imported']) {
                        if ($this->participants->isAssigned($id_data['usr_id'])) {
                            $this->participants->delete($id_data['usr_id']);
                        }
                    }
                }
            }
        }
        // MEMBER
        if (isset($this->group_data["member"]["attach"]) && count($this->group_data["member"]["attach"])) {
            foreach ($this->group_data["member"]["attach"] as $user) {
                if ($id_data = $this->__parseId($user)) {
                    if ($id_data['local'] or $id_data['imported']) {
                        $this->participants->add($id_data['usr_id'], IL_GRP_MEMBER);
                    }
                }
            }
        }

        if (isset($this->group_data["member"]["detach"]) && count($this->group_data["member"]["detach"])) {
            foreach ($this->group_data["member"]["detach"] as $user) {
                if ($id_data = $this->__parseId($user)) {
                    if ($id_data['local'] or $id_data['imported']) {
                        if ($this->participants->isAssigned($id_data['usr_id'])) {
                            $this->participants->delete($id_data['usr_id']);
                        }
                    }
                }
            }
        }
        return true;
    }

    public function __parseId($a_id)
    {
        global $DIC;

        $ilias = $DIC['ilias'];

        $fields = explode('_', $a_id);

        if (!is_array($fields) or
           $fields[0] != 'il' or
           !is_numeric($fields[1]) or
           $fields[2] != 'usr' or
           !is_numeric($fields[3])) {
            return false;
        }
        if ($id = ilObjUser::_getImportedUserId($a_id)) {
            return array('imported' => true,
                         'local' => false,
                         'usr_id' => $id);
        }
        if (($fields[1] == $ilias->getSetting('inst_id', 0)) and ($user = ilObjUser::_lookupName($fields[3]))) {
            if (strlen($user['login'])) {
                return array('imported' => false,
                             'local' => true,
                             'usr_id' => $fields[3]);
            }
        }
        $GLOBALS['DIC']->logger()->grp()->warning('Parsing id failed: ' . $a_id);
        return false;
    }


    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function __initContainerSorting($a_attribs, $a_group_id)
    {
        include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
        ilContainerSortingSettings::_importContainerSortingSettings($a_attribs, $a_group_id);
    }
}
