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

include_once "./Services/Xml/classes/class.ilXmlWriter.php";
include_once('./Modules/Group/classes/class.ilGroupParticipants.php');

/**
* XML writer class
*
* Class for writing xml export versions of courses
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilGroupXMLWriter.php 16108 2008-02-28 17:36:41Z rkuester $
*/
class ilGroupXMLWriter extends ilXmlWriter
{
    const MODE_SOAP = 1;
    const MODE_EXPORT = 2;
    
    private $mode = self::MODE_SOAP;


    private $ilias;

    private $xml;
    private $group_obj;
    private $attach_users = true;

    /**
    * constructor
    * @param	string	xml version
    * @param	string	output encoding
    * @param	string	input encoding
    * @access	public
    */
    public function __construct($group_obj)
    {
        global $DIC;

        $ilias = $DIC['ilias'];

        parent::__construct();

        $this->EXPORT_VERSION = "3";

        $this->ilias = $ilias;
        $this->group_obj = $group_obj;
        $this->participants = ilGroupParticipants::_getInstanceByObjId($this->group_obj->getId());
    }

    public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }
    
    public function getMode()
    {
        return $this->mode;
    }

    public function start()
    {
        if ($this->getMode() == self::MODE_SOAP) {
            $this->__buildHeader();
            $this->__buildGroup();
            $this->__buildMetaData();
            $this->__buildAdvancedMetaData();
            $this->__buildTitleDescription();
            $this->__buildRegistration();
            $this->__buildExtraSettings();
            if ($this->attach_users) {
                $this->__buildAdmin();
                $this->__buildMember();
            }
            include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
            ilContainerSortingSettings::_exportContainerSortingSettings($this, $this->group_obj->getId());
            ilContainer::_exportContainerSettings($this, $this->group_obj->getId());
            $this->__buildFooter();
        } elseif ($this->getMode() == self::MODE_EXPORT) {
            $this->__buildGroup();
            $this->__buildMetaData();
            $this->__buildAdvancedMetaData();
            $this->__buildTitleDescription();
            $this->__buildRegistration();
            $this->__buildExtraSettings();
            $this->__buildPeriod();
            include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
            ilContainerSortingSettings::_exportContainerSortingSettings($this, $this->group_obj->getId());
            ilContainer::_exportContainerSettings($this, $this->group_obj->getId());
            $this->__buildFooter();
        }
    }

    public function getXML()
    {
        return $this->xmlDumpMem(false);
    }

    // PRIVATE
    public function __buildHeader()
    {
        $this->xmlSetDtdDef("<!DOCTYPE group PUBLIC \"-//ILIAS//DTD Group//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_group_3_10.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS group " . $this->group_obj->getId() . " of installation " . $this->ilias->getSetting('inst_id') . ".");
        $this->xmlHeader();


        return true;
    }
    
    /**
     * Group start
     * @return
     */
    public function __buildGroup()
    {
        $attrs["exportVersion"] = $this->EXPORT_VERSION;
        $attrs["id"] = "il_" . $this->ilias->getSetting('inst_id') . '_grp_' . $this->group_obj->getId();
        
        switch ($this->group_obj->readGroupStatus()) {
            case GRP_TYPE_PUBLIC:
                $attrs['type'] = 'open';
                break;
                
            case GRP_TYPE_CLOSED:
            default:
                $attrs['type'] = 'closed';
                break;
        }
        
        $this->xmlStartTag("group", $attrs);
    }

    /**
     * write lom meta data
     * @return bool
     */
    protected function __buildMetaData()
    {
        $md2xml = new ilMD2XML($this->group_obj->getId(), $this->group_obj->getId(), 'grp');
        $md2xml->startExport();
        $this->appendXML($md2xml->getXML());

        return true;
    }

    /**
     * Build advanced meta data
     *
     * @access private
     *
     */
    private function __buildAdvancedMetaData()
    {
        ilAdvancedMDValues::_appendXMLByObjId($this, $this->group_obj->getId());
    }


    public function __buildTitleDescription()
    {
        $this->xmlElement('title', null, $this->group_obj->getTitle());
        
        if ($desc = $this->group_obj->getDescription()) {
            $this->xmlElement('description', null, $desc);
        }

        $attr['id'] = 'il_' . $this->ilias->getSetting('inst_id') . '_usr_' . $this->group_obj->getOwner();
        $this->xmlElement('owner', $attr);
        
        $this->xmlElement('information', null, $this->group_obj->getInformation());
    }
    
    /**
     * Build group period
     */
    protected function __buildPeriod()
    {
        if (
            $this->group_obj->getStart() instanceof ilDate &&
            $this->group_obj->getEnd() instanceof ilDate
        ) {
            $this->xmlStartTag('period');
            $this->xmlElement('start', null, $this->group_obj->getStart()->get(IL_CAL_UNIX));
            $this->xmlElement('end', null, $this->group_obj->getEnd()->get(IL_CAL_UNIX));
            $this->xmlEndTag('period');
        }
        return;
    }
    
    public function __buildRegistration()
    {
        
        // registration type
        switch ($this->group_obj->getRegistrationType()) {
            case GRP_REGISTRATION_DIRECT:
                $attrs['type'] = 'direct';
                break;
            case GRP_REGISTRATION_REQUEST:
                $attrs['type'] = 'confirmation';
                break;
            case GRP_REGISTRATION_PASSWORD:
                $attrs['type'] = 'password';
                break;
                
            default:
            case GRP_REGISTRATION_DEACTIVATED:
                $attrs['type'] = 'disabled';
                break;
        }
        $attrs['waitingList'] = $this->group_obj->isWaitingListEnabled() ? 'Yes' : 'No';
        
        $this->xmlStartTag('registration', $attrs);
        
        if (strlen($pwd = $this->group_obj->getPassword())) {
            $this->xmlElement('password', null, $pwd);
        }

        
        // limited registration period
        if (!$this->group_obj->isRegistrationUnlimited()) {
            $this->xmlStartTag('temporarilyAvailable');
            $this->xmlElement('start', null, $this->group_obj->getRegistrationStart()->get(IL_CAL_UNIX));
            $this->xmlElement('end', null, $this->group_obj->getRegistrationEnd()->get(IL_CAL_UNIX));
            $this->xmlEndTag('temporarilyAvailable');
        }

        // max members
        $attrs = array();
        $attrs['enabled'] = $this->group_obj->isMembershipLimited() ? 'Yes' : 'No';
        $this->xmlElement('maxMembers', $attrs, $this->group_obj->getMaxMembers());
        $this->xmlElement('minMembers', null, (int) $this->group_obj->getMinMembers());
        $this->xmlElement('WaitingListAutoFill', null, (int) $this->group_obj->hasWaitingListAutoFill());
        $this->xmlElement('CancellationEnd', null, ($this->group_obj->getCancellationEnd() && !$this->group_obj->getCancellationEnd()->isNull()) ? $this->group_obj->getCancellationEnd()->get(IL_CAL_UNIX) : null);
        
        $this->xmlElement('mailMembersType', null, (string) $this->group_obj->getMailToMembersType());

        $this->xmlEndTag('registration');
    }

    /**
     * Build extra settings, like "show member list"
     */
    public function __buildExtraSettings()
    {
        $this->xmlElement('showMembers', null, $this->group_obj->getShowMembers());
    }

    public function __buildAdmin()
    {
        $admins = $this->group_obj->getGroupAdminIds();
        $admins = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            ilOrgUnitOperation::OP_MANAGE_MEMBERS,
            $this->group_obj->getRefId(),
            $admins
        );
        
        foreach ($admins as $id) {
            $attr['id'] = 'il_' . $this->ilias->getSetting('inst_id') . '_usr_' . $id;
            $attr['notification'] = $this->participants->isNotificationEnabled($id) ? 'Yes' : 'No';

            $this->xmlElement('admin', $attr);
        }
        return true;
    }

    public function __buildMember()
    {
        $members = $this->group_obj->getGroupMemberIds();
        $members = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            ilOrgUnitOperation::OP_MANAGE_MEMBERS,
            $this->group_obj->getRefId(),
            $members
        );
        foreach ($members as $id) {
            if (!$this->group_obj->isAdmin($id)) {
                $attr['id'] = 'il_' . $this->ilias->getSetting('inst_id') . '_usr_' . $id;
                
                $this->xmlElement('member', $attr);
            }
        }
        return true;
    }

    public function __buildFooter()
    {
        $this->xmlEndTag('group');
    }

    public function setAttachUsers($value)
    {
        $this->attach_users = $value ? true : false;
    }
}
