<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author Fabian Wolf <wolf@leifos.com>
 */
class ilLDAPRoleMappingTableGUI extends ilTable2GUI
{
    private ilObjectDataCache $ilObjDataCache;
    private ilRbacReview $rbacreview;
    private int $server_id;

    /**
     * @throws ilCtrlException
     */
    public function __construct(object $a_parent_obj, int $a_server_id, string $a_parent_cmd = '')
    {
        $this->server_id = $a_server_id;
        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;
        
        $this->ilObjDataCache = $DIC['ilObjDataCache'];
        $this->rbacreview = $DIC->rbac()->review();
        
        $this->addColumn("");
        $this->addColumn($this->lng->txt('title'), "role");
        $this->addColumn($this->lng->txt('obj_role'), "role");
        $this->addColumn($this->lng->txt('ldap_group_dn'), "dn");
        $this->addColumn($this->lng->txt('ldap_server'), "url");
        $this->addColumn($this->lng->txt('ldap_group_member'), "member_attribute");
        $this->addColumn($this->lng->txt('ldap_info_text'), "info");
        $this->addColumn($this->lng->txt('action'));
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.ldap_role_mapping_row.html", "Services/LDAP");
        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection("desc");
        $this->addMultiCommand('confirmDeleteRoleMapping', $this->lng->txt("delete"));
        
        $this->getItems();
    }

    /**
     * @throws ilCtrlException
     */
    protected function fillRow(array $a_set) : void
    {
        $title = $this->ilObjDataCache->lookupTitle($this->rbacreview->getObjectOfRole((int) $a_set["role"]));
        $this->tpl->setVariable("VAL_ID", $a_set['mapping_id']);
        $this->tpl->setVariable("VAL_TITLE", ilStr::shortenTextExtended($title, 30, true));
        $this->tpl->setVariable("VAL_ROLE", $a_set["role_name"]);
        $this->tpl->setVariable("VAL_GROUP", $a_set["dn"]);
        $this->tpl->setVariable("VAL_URL", $a_set["url"]);
        $this->tpl->setVariable("VAL_MEMBER", $a_set["member_attribute"]);
        $this->tpl->setVariable("VAL_INFO", ilLegacyFormElementsUtil::prepareFormOutput($a_set['info']));
        $this->ctrl->setParameter($this->getParentObject(), 'mapping_id', $a_set['mapping_id']);
        $this->tpl->setVariable("EDIT_URL", $this->ctrl->getLinkTarget($this->getParentObject(), 'addRoleMapping'));
        $this->tpl->setVariable("EDIT_TXT", $this->lng->txt('copy'));
        $this->ctrl->setParameter($this->getParentObject(), 'mapping_id', $a_set['mapping_id']);
        $this->tpl->setVariable("COPY_URL", $this->ctrl->getLinkTarget($this->getParentObject(), 'editRoleMapping'));
        $this->tpl->setVariable("COPY_TXT", $this->lng->txt('edit'));
    }
    
    /**
     * get items from db
     */
    public function getItems() : void
    {
        $mapping_instance = ilLDAPRoleGroupMappingSettings::_getInstanceByServerId($this->server_id);
        $this->setData($mapping_instance->getMappings());
    }
}
