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
 * @author Fabian Wolf <wolf@leifos.de>
 */
class ilLDAPServerTableGUI extends ilTable2GUI
{
    private ilRbacSystem $rbacSystem;
    private int $ref_id = 0 ;

    public function __construct(?object $a_parent_obj, string $a_parent_cmd = "", string $a_template_context = "")
    {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
        
        global $DIC;
        $this->rbacSystem = $DIC->rbac()->system();

        $http_wrapper = $DIC->http()->wrapper();
        $refinery = $DIC->refinery();
        if ($http_wrapper->query()->has('ref_id')) {
            $this->ref_id = (int) $http_wrapper->query()->retrieve(
                'ref_id',
                $refinery->kindlyTo()->int()
            );
        }
        $this->setId('ldap_server_list');
        
        $this->setTitle($this->lng->txt('ldap_servers'));
        $this->setRowTemplate('tpl.ldap_server_row.html', 'Services/LDAP');

        $this->addColumn($this->lng->txt('active'), '', '1%');
        $this->addColumn($this->lng->txt('title'), '', '80%');
        $this->addColumn($this->lng->txt('user'), "", "4%");
        $this->addColumn($this->lng->txt('actions'), '', '15%');
        
        $this->importData();
    }
    
    private function importData() : void
    {
        $data = ilLDAPServer::_getAllServer();
        $this->setData($data);
    }

    /**
     * @throws ilCtrlException
     */
    protected function fillRow(array $a_set) : void
    {
        if ($a_set['active']) {
            $this->tpl->setVariable('IMAGE_OK', ilUtil::getImagePath('icon_ok.svg'));
            $this->tpl->setVariable('TXT_OK', $this->lng->txt('active'));
        } else {
            $this->tpl->setVariable('IMAGE_OK', ilUtil::getImagePath('icon_not_ok.svg'));
            $this->tpl->setVariable('TXT_OK', $this->lng->txt('inactive'));
        }
        
        $this->tpl->setVariable('VAL_TITLE', $a_set["name"]);
        
        //user
        $user = count(ilObjUser::_getExternalAccountsByAuthMode("ldap_" . $a_set["server_id"]));
        $this->tpl->setVariable('VAL_USER', $user);
        
        $this->ctrl->setParameter($this->getParentObject(), 'ldap_server_id', $a_set['server_id']);
        $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->getParentObject(), 'editServerSettings'));
        
        //actions
        if ($this->rbacSystem->checkAccess("write", $this->ref_id)) {
            $list = new ilAdvancedSelectionListGUI();
            $list->setSelectionHeaderClass('small');
            $list->setItemLinkClass('small');
            $list->setId('actl_' . $a_set['server_id']);
            $list->setListTitle($this->lng->txt('actions'));
            $list->addItem($this->lng->txt('edit'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'editServerSettings'));

            if ($a_set['active']) {
                $list->addItem(
                    $this->lng->txt('deactivate'),
                    '',
                    $this->ctrl->getLinkTarget($this->getParentObject(), 'deactivateServer')
                );
            } else {
                $list->addItem(
                    $this->lng->txt('activate'),
                    '',
                    $this->ctrl->getLinkTarget($this->getParentObject(), 'activateServer')
                );
            }

            $list->addItem(
                $this->lng->txt('delete'),
                '',
                $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDeleteServerSettings')
            );

            $this->tpl->setVariable('ACTIONS', $list->getHTML());
        }
    }
}
