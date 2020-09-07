<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 *
 *
 * @author Fabian Wolf <wolf@leifos.de>
 * @version $Id: $
 * @ingroup
 */
class ilLDAPServerTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
    {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
        
        $this->setId('ldap_server_list');
        
        $this->setTitle($this->lng->txt('ldap_servers'));
        $this->setRowTemplate('tpl.ldap_server_row.html', 'Services/LDAP');

        $this->addColumn($this->lng->txt('active'), '', '1%');
        $this->addColumn($this->lng->txt('title'), '', '80%');
        $this->addColumn($this->lng->txt('user'), "", "4%");
        $this->addColumn($this->lng->txt('actions'), '', '15%');
        
        $this->importData();
    }
    
    private function importData()
    {
        include_once './Services/LDAP/classes/class.ilLDAPServer.php';
        
        $data = ilLDAPServer::_getAllServer();
        $this->setData($data);
    }
    
    protected function fillRow($a_set)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
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
        
        $ilCtrl->setParameter($this->getParentObject(), 'ldap_server_id', $a_set['server_id']);
        $this->tpl->setVariable('EDIT_LINK', $ilCtrl->getLinkTarget($this->getParentObject(), 'editServerSettings'));
        
        //actions
        
        include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('small');
        $list->setItemLinkClass('small');
        $list->setId('actl_' . $a_set['server_id']);
        $list->setListTitle($this->lng->txt('actions'));
        $list->addItem($this->lng->txt('edit'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'editServerSettings'));
        
        if ($a_set['active']) {
            $list->addItem(
                $this->lng->txt('deactivate'),
                '',
                $ilCtrl->getLinkTarget($this->getParentObject(), 'deactivateServer')
            );
        } else {
            $list->addItem(
                $this->lng->txt('activate'),
                '',
                $ilCtrl->getLinkTarget($this->getParentObject(), 'activateServer')
            );
        }
        
        $list->addItem(
            $this->lng->txt('delete'),
            '',
            $ilCtrl->getLinkTarget($this->getParentObject(), 'confirmDeleteServerSettings')
        );
        
        $this->tpl->setVariable('ACTIONS', $list->getHTML());
    }
}
