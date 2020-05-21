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

include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once('Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php');

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSCommunityTableGUI extends ilTable2GUI
{
    protected $lng;
    protected $ctrl;
    

    protected $server = null;
    protected $cid = 0;
    
    /**
     * constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct(ilECSSetting $set, $a_parent_obj, $a_parent_cmd, $cid)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        // TODO: set id
        $this->setId($set->getServerId() . '_' . $cid . '_' . 'community_table');

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn($this->lng->txt('ecs_participants'), 'participants', "35%");
        $this->addColumn($this->lng->txt('ecs_participants_infos'), 'infos', "35%");
        $this->addColumn($this->lng->txt('ecs_tbl_export'), 'export', '5%');
        $this->addColumn($this->lng->txt('ecs_tbl_import'), 'import', '5%');
        $this->addColumn($this->lng->txt('ecs_tbl_import_type'), 'type', '10%');

        if ($ilAccess->checkAccess('write', '', $_REQUEST["ref_id"])) {
            $this->addColumn('', 'actions', '10%');
        }

        $this->disable('form');
        $this->setRowTemplate("tpl.participant_row.html", "Services/WebServices/ECS");
        $this->setDefaultOrderField('participants');
        $this->setDefaultOrderDirection("desc");

        $this->cid = $cid;
        $this->server = $set;
    }

    /**
     * Get current server
     * @return ilECSSetting
     */
    public function getServer()
    {
        return $this->server;
    }
    
    /**
     * Fill row
     *
     * @access public
     * @param array row data
     *
     */
    public function fillRow($a_set)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];

        $this->tpl->setVariable('S_ID', $this->getServer()->getServerId());
        $this->tpl->setVariable('M_ID', $a_set['mid']);
        $this->tpl->setVariable('VAL_ID', $this->getServer()->getServerId() . '_' . $a_set['mid']);
        $this->tpl->setVariable('VAL_ORG', (string) $a_set['org']);
        $this->tpl->setVariable('VAL_CHECKED', $a_set['checked'] ? 'checked="checked"' : '');
        $this->tpl->setVariable('VAL_TITLE', $a_set['participants']);
        $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        $this->tpl->setVariable('VAL_EMAIL', $a_set['email']);
        $this->tpl->setVariable('VAL_DNS', $a_set['dns']);
        $this->tpl->setVariable('VAL_ABR', $a_set['abr']);
        $this->tpl->setVariable('TXT_EMAIL', $this->lng->txt('ecs_email'));
        $this->tpl->setVariable('TXT_DNS', $this->lng->txt('ecs_dns'));
        $this->tpl->setVariable('TXT_ABR', $this->lng->txt('ecs_abr'));
        $this->tpl->setVariable('TXT_ID', $this->lng->txt('ecs_unique_id'));
        $this->tpl->setVariable('TXT_ORG', $this->lng->txt('organization'));

        include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
        $part = new ilECSParticipantSetting($this->getServer()->getServerId(), $a_set['mid']);
        
        
        if ($part->isExportEnabled()) {
            foreach ($part->getExportTypes() as $obj_type) {
                $this->tpl->setCurrentBlock('obj_erow');
                $this->tpl->setVariable('TXT_OBJ_EINFO', $this->lng->txt('objs_' . $obj_type));
                $this->tpl->parseCurrentBlock();
            }
        } else {
            $this->lng->loadLanguageModule('administration');
            $this->tpl->setVariable('TXT_OBJ_EINFO', $this->lng->txt('disabled'));
        }
        
        if ($part->isImportEnabled()) {
            foreach ($part->getImportTypes() as $obj_type) {
                $this->tpl->setCurrentBlock('obj_irow');
                $this->tpl->setVariable('TXT_OBJ_IINFO', $this->lng->txt('objs_' . $obj_type));
                $this->tpl->parseCurrentBlock();
            }
        } else {
            $this->lng->loadLanguageModule('administration');
            $this->tpl->setVariable('TXT_OBJ_IINFO', $this->lng->txt('disabled'));
        }
        // :TODO: what types are to be supported?
        $sel = ilUtil::formSelect(
            $part->getImportType(),
            'import_type[' . $this->getServer()->getServerId() . '][' . $a_set['mid'] . ']',
            array(
                ilECSParticipantSetting::IMPORT_RCRS => $this->lng->txt('obj_rcrs'),
                ilECSParticipantSetting::IMPORT_CRS => $this->lng->txt('obj_crs'),
                ilECSParticipantSetting::IMPORT_CMS => $this->lng->txt('ecs_import_cms')
            ),
            false,
            true
        );
        $this->tpl->setVariable('IMPORT_SEL', $sel);

        include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $list = new ilAdvancedSelectionListGUI();
        $list->setItemLinkClass('small');
        $list->setSelectionHeaderClass('small');
        $list->setId('actl_' . $a_set['server_id'] . '_' . $a_set['mid']);
        $list->setListTitle($this->lng->txt('actions'));
        
        $ilCtrl->setParameter($this->getParentObject(), 'server_id', $this->getServer()->getServerId());
        $ilCtrl->setParameter($this->getParentObject(), 'mid', $a_set['mid']);
        $list->addItem(
            $this->lng->txt('edit'),
            '',
            $ilCtrl->getLinkTargetByClass('ilecsparticipantsettingsgui', 'settings')
        );

        switch ($part->getImportType()) {
            case ilECSParticipantSetting::IMPORT_RCRS:
                // Do nothing
                break;

            case ilECSParticipantSetting::IMPORT_CRS:
                // Possible action => Edit course allocation
                $ilCtrl->setParameter($this->getParentObject(), 'server_id', $this->getServer()->getServerId());
                $ilCtrl->setParameter($this->getParentObject(), 'mid', $a_set['mid']);
                $list->addItem(
                    $this->lng->txt('ecs_crs_alloc_set'),
                    '',
                    $ilCtrl->getLinkTargetByClass('ilecsmappingsettingsgui', 'cStart')
                );
                break;

            case ilECSParticipantSetting::IMPORT_CMS:

                $ilCtrl->setParameter($this->getParentObject(), 'server_id', $this->getServer()->getServerId());
                $ilCtrl->setParameter($this->getParentObject(), 'mid', $a_set['mid']);
                // Possible action => Edit course allocation, edit node mapping
                $list->addItem(
                    $this->lng->txt('ecs_dir_alloc_set'),
                    '',
                    $ilCtrl->getLinkTargetByClass('ilecsmappingsettingsgui', 'dStart')
                );
                $list->addItem(
                    $this->lng->txt('ecs_crs_alloc_set'),
                    '',
                    $ilCtrl->getLinkTargetByClass('ilecsmappingsettingsgui', 'cStart')
                );
                break;
        }

        if ($ilAccess->checkAccess('write', '', $_REQUEST["ref_id"])) {
            $this->tpl->setCurrentBlock("actions");
            $this->tpl->setVariable('ACTIONS', $list->getHTML());
            $this->tpl->parseCurrentBlock();
        }
    }
    
    /**
     * Parse
     *
     * @access public
     * @param array array of LDAPRoleAssignmentRule
     *
     */
    public function parse($a_participants)
    {
        foreach ($a_participants as $participant) {
            $tmp_arr['mid'] = $participant->getMID();
            $tmp_arr['participants'] = $participant->getParticipantName();
            $tmp_arr['description'] = $participant->getDescription();
            $tmp_arr['email'] = $participant->getEmail();
            $tmp_arr['dns'] = $participant->getDNS();

            if ($participant->getOrganisation() instanceof ilECSOrganisation) {
                $tmp_arr['abr'] = $participant->getOrganisation()->getAbbreviation();
                $tmp_arr['org'] = $participant->getOrganisation()->getName();
            }
            $def[] = $tmp_arr;
        }
        
        $this->setData($def ? $def : array());
    }
}
