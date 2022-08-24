<?php

declare(strict_types=1);

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
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSCommunityTableGUI extends ilTable2GUI
{
    private ilAccessHandler $access;


    private ilECSSetting $server;

    public function __construct(ilECSSetting $set, ?object $a_parent_obj, string $a_parent_cmd, int $cid)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;

        $this->access = $DIC->access();

        // TODO: set id
        $this->setId($set->getServerId() . '_' . $cid . '_' . 'community_table');

        $this->addColumn($this->lng->txt('ecs_participants'), 'participants', "35%");
        $this->addColumn($this->lng->txt('ecs_participants_infos'), 'infos', "35%");
        $this->addColumn($this->lng->txt('ecs_tbl_export'), 'export', '5%');
        $this->addColumn($this->lng->txt('ecs_tbl_import'), 'import', '5%');
        $this->addColumn($this->lng->txt('ecs_tbl_import_type'), 'type', '10%');

        if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
            $this->addColumn('', 'actions', '10%');
        }

        $this->disable('form');
        $this->setRowTemplate("tpl.participant_row.html", "Services/WebServices/ECS");
        $this->setDefaultOrderField('participants');
        $this->setDefaultOrderDirection("desc");

        $this->server = $set;
    }

    /**
     * Get current server
     */
    public function getServer(): ilECSSetting
    {
        return $this->server;
    }

    /**
     * Fill row
     *
     * @param array row data
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('S_ID', $this->getServer()->getServerId());
        $this->tpl->setVariable('M_ID', $a_set['mid']);
        $this->tpl->setVariable('VAL_ID', $this->getServer()->getServerId() . '_' . $a_set['mid']);
        $this->tpl->setVariable('VAL_ORG', (string) $a_set['org']);
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
        $sel = ilLegacyFormElementsUtil::formSelect(
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

        $list = new ilAdvancedSelectionListGUI();
        $list->setItemLinkClass('small');
        $list->setSelectionHeaderClass('small');
        $list->setId('actl_' . $this->getServer()->getServerId() . '_' . $a_set['mid']);
        $list->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameter($this->getParentObject(), 'server_id', $this->getServer()->getServerId());
        $this->ctrl->setParameter($this->getParentObject(), 'mid', $a_set['mid']);
        $list->addItem(
            $this->lng->txt('edit'),
            '',
            $this->ctrl->getLinkTargetByClass('ilecsparticipantsettingsgui', 'settings')
        );

        switch ($part->getImportType()) {
            case ilECSParticipantSetting::IMPORT_RCRS:
                // Do nothing
                break;

            case ilECSParticipantSetting::IMPORT_CRS:
                // Possible action => Edit course allocation
                $this->ctrl->setParameter($this->getParentObject(), 'server_id', $this->getServer()->getServerId());
                $this->ctrl->setParameter($this->getParentObject(), 'mid', $a_set['mid']);
                $list->addItem(
                    $this->lng->txt('ecs_crs_alloc_set'),
                    '',
                    $this->ctrl->getLinkTargetByClass('ilecsmappingsettingsgui', 'cStart')
                );
                break;

            case ilECSParticipantSetting::IMPORT_CMS:

                $this->ctrl->setParameter($this->getParentObject(), 'server_id', $this->getServer()->getServerId());
                $this->ctrl->setParameter($this->getParentObject(), 'mid', $a_set['mid']);
                // Possible action => Edit course allocation, edit node mapping
                $list->addItem(
                    $this->lng->txt('ecs_dir_alloc_set'),
                    '',
                    $this->ctrl->getLinkTargetByClass('ilecsmappingsettingsgui', 'dStart')
                );
                $list->addItem(
                    $this->lng->txt('ecs_crs_alloc_set'),
                    '',
                    $this->ctrl->getLinkTargetByClass('ilecsmappingsettingsgui', 'cStart')
                );
                break;
        }

        if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
            $this->tpl->setCurrentBlock("actions");
            $this->tpl->setVariable('ACTIONS', $list->getHTML());
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * @param ilECSCommunity[] $a_participants list of participants
     */
    public function parse(array $participants): void
    {
        foreach ($participants as $participant) {
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

        $this->setData($def ?? []);
    }
}
