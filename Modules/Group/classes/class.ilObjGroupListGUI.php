<?php

declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Class ilObjGroupListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
*/
class ilObjGroupListGUI extends ilObjectListGUI
{
    protected ilRbacSystem $rbacsystem;

    public function __construct(int $a_context = self::CONTEXT_REPOSITORY)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        parent::__construct($a_context);
    }

    /**
     * @inheritDoc
    */
    public function init(): void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->type = "grp";
        $this->gui_class_name = "ilobjgroupgui";

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        $this->enableSubstitutions($this->substitutions->isActive());

        // general commands array
        $this->commands = ilObjGroupAccess::_getCommands();
    }

    /**
     * @inheritDoc
    */
    public function getCommandLink(string $cmd): string
    {
        switch ($cmd) {
            // BEGIN WebDAV: Mount Webfolder.
            case 'mount_webfolder':
                if (ilDAVActivationChecker::_isActive()) {
                    global $DIC;
                    $uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
                    $cmd_link = $uri_builder->getUriToMountInstructionModalByRef($this->ref_id);
                    break;
                } // fall through if plugin is not active
                // END Mount Webfolder.

                // no break
            case "edit":
            default:
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->requested_ref_id);
                break;
        }
        return $cmd_link;
    }


    /**
     * @inheritDoc
    */
    public function getProperties(): array
    {
        $props = parent::getProperties();
        $info = ilObjGroupAccess::lookupRegistrationInfo($this->obj_id);
        //var_dump($info);
        if (isset($info['reg_info_list_prop'])) {
            $props[] = array(
                'alert' => false,
                'newline' => true,
                'property' => $info['reg_info_list_prop']['property'],
                'value' => $info['reg_info_list_prop']['value']
            );
        }
        if (isset($info['reg_info_list_prop_limit'])) {
            $props[] = array(
                'alert' => false,
                'newline' => false,
                'property' => $info['reg_info_list_prop_limit']['property'],
                'propertyNameVisible' => strlen($info['reg_info_list_prop_limit']['property']) ? true : false,
                'value' => $info['reg_info_list_prop_limit']['value']
            );
        }



        // waiting list
        if (ilGroupWaitingList::_isOnList($this->user->getId(), $this->obj_id)) {
            $props[] = array(
                "alert" => true,
                "property" => $this->lng->txt('member_status'),
                "value" => $this->lng->txt('on_waiting_list')
            );
        }

        // course period
        $info = ilObjGroupAccess::lookupPeriodInfo($this->obj_id);
        if (is_array($info)) {
            $props[] = array(
                'alert' => false,
                'newline' => true,
                'property' => $info['property'],
                'value' => $info['value']
            );
        }
        return $props;
    }

    /**
     * @inheritDoc
     */
    public function getCommandFrame(string $cmd): string
    {
        // begin-patch fm
        return parent::getCommandFrame($cmd);
        // end-patch fm
    }


    /**
     * @inheritDoc
     */
    public function checkCommandAccess(
        string $permission,
        string $cmd,
        int $ref_id,
        string $type,
        ?int $obj_id = null
    ): bool {
        if ($permission == 'grp_linked') {
            return
                parent::checkCommandAccess('read', '', $ref_id, $type, $obj_id) ||
                parent::checkCommandAccess('join', 'join', $ref_id, $type, $obj_id);
        }
        return parent::checkCommandAccess($permission, $cmd, $ref_id, $type, $obj_id);
    }
} // END class.ilObjGroupListGUI
