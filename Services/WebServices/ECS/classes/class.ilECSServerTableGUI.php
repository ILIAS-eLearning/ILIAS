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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilECSServerTableGUI extends ilTable2GUI
{
    private ilAccessHandler $access;

    /**
     * Constructor
     * @param object $a_parent_obj
     * @param string $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setId('ecs_server_list');

        $this->access = $DIC->access();
    }

    /**
     * Init Table
     */
    public function initTable() : void
    {
        $this->setTitle($this->lng->txt('ecs_available_ecs'));
        $this->setRowTemplate('tpl.ecs_server_row.html', 'Services/WebServices/ECS');

        $this->addColumn($this->lng->txt('ecs_tbl_active'), '', '1%');
        $this->addColumn($this->lng->txt('title'), '', '80%');

        if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
            $this->addColumn($this->lng->txt('actions'), '', '19%');
        }
    }

    /**
     * Fill row
 * @param array $a_set
     */
    protected function fillRow(array $a_set) : void
    {
        $this->ctrl->setParameter($this->getParentObject(), 'server_id', $a_set['server_id']);
        $this->ctrl->setParameterByClass('ilecsmappingsettingsgui', 'server_id', $a_set['server_id']);

        if ($a_set['active']) {
            $this->tpl->setVariable('IMAGE_OK', ilUtil::getImagePath('icon_ok.svg'));
            $this->tpl->setVariable('TXT_OK', $this->lng->txt('ecs_activated'));
        } else {
            $this->tpl->setVariable('IMAGE_OK', ilUtil::getImagePath('icon_not_ok.svg'));
            $this->tpl->setVariable('TXT_OK', $this->lng->txt('ecs_inactivated'));
        }
        

        $this->tpl->setVariable('VAL_TITLE', ilECSSetting::getInstanceByServerId($a_set['server_id'])->getTitle());
        $this->tpl->setVariable('LINK_EDIT', $this->ctrl->getLinkTarget($this->getParentObject(), 'edit'));
        $this->tpl->setVariable('TXT_SRV_ADDR', $this->lng->txt('ecs_server_addr'));

        if (ilECSSetting::getInstanceByServerId($a_set['server_id'])->getServer()) {
            $this->tpl->setVariable('VAL_DESC', ilECSSetting::getInstanceByServerId($a_set['server_id'])->getServer());
        } else {
            $this->tpl->setVariable('VAL_DESC', $this->lng->txt('ecs_not_configured'));
        }

        $dt = ilECSSetting::getInstanceByServerId($a_set['server_id'])->fetchCertificateExpiration();
        if ($dt !== null) {
            $this->tpl->setVariable('TXT_CERT_VALID', $this->lng->txt('ecs_cert_valid_until'));
            
            $now = new ilDateTime(time(), IL_CAL_UNIX);
            $now->increment(IL_CAL_MONTH, 2);
            
            if (ilDateTime::_before($dt, $now)) {
                $this->tpl->setCurrentBlock('invalid');
                $this->tpl->setVariable('VAL_ICERT', ilDatePresentation::formatDate($dt));
            } else {
                $this->tpl->setCurrentBlock('valid');
                $this->tpl->setVariable('VAL_VCERT', ilDatePresentation::formatDate($dt));
            }
            $this->tpl->parseCurrentBlock();
        }

        if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
            // Actions
            $list = new ilAdvancedSelectionListGUI();
            $list->setSelectionHeaderClass('small');
            $list->setItemLinkClass('small');
            $list->setId('actl_' . $a_set['server_id']);
            $list->setListTitle($this->lng->txt('actions'));


            if (ilECSSetting::getInstanceByServerId($a_set['server_id'])->isEnabled()) {
                $list->addItem($this->lng->txt('ecs_deactivate'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'deactivate'));
            } else {
                $list->addItem($this->lng->txt('ecs_activate'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'activate'));
            }

            $list->addItem($this->lng->txt('edit'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'edit'));
            $list->addItem($this->lng->txt('copy'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'cp'));
            $list->addItem($this->lng->txt('delete'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'delete'));

            $this->tpl->setCurrentBlock("actions");
            $this->tpl->setVariable('ACTIONS', $list->getHTML());
            $this->tpl->parseCurrentBlock();
        }
        $this->ctrl->clearParameters($this->getParentObject());
    }

    /**
     * Parse available servers
     */
    public function parse(ilECSServerSettings $servers) : void
    {
        $rows = [];
        foreach ($servers->getServers(ilECSServerSettings::ALL_SERVER) as $server) {
            $tmp['server_id'] = $server->getServerId();
            $tmp['active'] = $server->isEnabled();

            $rows[] = $tmp;
        }
        $this->setData($rows);
    }
}
