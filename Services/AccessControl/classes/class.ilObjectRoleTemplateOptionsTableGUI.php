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
 * Table for object role permissions
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesAccessControl
 */
class ilObjectRoleTemplateOptionsTableGUI extends ilTable2GUI
{
    private int $role_id;
    private int $obj_ref_id;

    private bool $show_admin_permissions = true;
    private bool $show_options = true;
    private ilRbacReview $rbacreview;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_obj_ref_id,
        int $a_role_id,
        bool $a_show_admin_permissions = false
    ) {
        global $DIC;

        $this->show_admin_permissions = $a_show_admin_permissions;
        $this->rbacreview = $DIC->rbac()->review();

        $this->setId('role_options_' . $a_obj_ref_id . '_' . $a_role_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->lng->loadLanguageModule('rbac');

        $this->role_id = $a_role_id;
        $this->obj_ref_id = $a_obj_ref_id;

        $this->setRowTemplate("tpl.obj_role_template_options_row.html", "Services/AccessControl");
        $this->setLimit(100);
        $this->setShowRowsSelector(false);
        $this->setDisableFilterHiding(true);

        $this->setEnableHeader(false);
        $this->disable('sort');
        $this->disable('numinfo');
        $this->disable('form');

        $this->addColumn('', '', '0');
        $this->addColumn('', '', '100%');

        $this->setTopCommands(false);
    }

    /**
     * Set show options
     */
    public function setShowOptions(bool $a_status) : void
    {
        $this->show_options = $a_status;
    }

    public function getShowOptions() : bool
    {
        return $this->show_options;
    }

    /**
     * Get role folder of current object
     */
    public function getObjectRefId() : int
    {
        return $this->obj_ref_id;
    }

    public function getRoleId() : int
    {
        return $this->role_id;
    }

    /**
     * Fill row template
     */
    protected function fillRow(array $a_set) : void
    {
        if (!$this->getShowOptions()) {
            return;
        }
        if (isset($a_set['recursive']) && !$this->show_admin_permissions) {
            $this->tpl->setCurrentBlock('recursive');
            $this->tpl->setVariable('TXT_RECURSIVE', $this->lng->txt('change_existing_objects'));
            $this->tpl->setVariable('DESC_RECURSIVE', $this->lng->txt('change_existing_objects_desc'));
        } elseif (isset($a_set['protected'])) {
            $this->tpl->setCurrentBlock('protected');

            if (!$this->rbacreview->isAssignable($this->getRoleId(), $this->getObjectRefId())) {
                $this->tpl->setVariable('DISABLED_PROTECTED', 'disabled="disabled"');
            }

            if ($this->rbacreview->isProtected($this->getObjectRefId(), $this->getRoleId())) {
                $this->tpl->setVariable('PROTECTED_CHECKED', 'checked="checked"');
            }

            $this->tpl->setVariable('TXT_PROTECTED', $this->lng->txt('role_protect_permissions'));
            $this->tpl->setVariable('DESC_PROTECTED', $this->lng->txt('role_protect_permissions_desc'));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * Parse permissions
     */
    public function parse() : void
    {
        $row = [];
        $row[0]['recursive'] = 1;
        $row[1]['protected'] = 1;
        $this->setData($row);
    }
}
