<?php

declare(strict_types=1);
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
class ilObjectRoleTemplatePermissionTableGUI extends ilTable2GUI
{
    private int $ref_id = 0;
    private int $role_id = 0;
    private int $role_folder_id = 0;

    private string $tpl_type = '';

    private bool $show_admin_permissions = false;
    private bool $show_change_existing_objects = true;

    private static ?array $template_permissions = null;
    protected ilObjectDefinition $objDefinition;
    protected ilRbacReview $review;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_ref_id,
        int $a_role_id,
        string $a_type,
        bool $a_show_admin_permissions = false
    ) {
        global $DIC;

        $this->review = $DIC->rbac()->review();
        $this->objDefinition = $DIC['objDefinition'];

        $this->tpl_type = $a_type;
        $this->show_admin_permissions = $a_show_admin_permissions;

        $this->setId('role_template_' . $a_ref_id . '_' . $a_type);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setFormName('role_template_permissions');
        $this->setSelectAllCheckbox('template_perm[' . $this->getTemplateType() . ']');

        $this->lng->loadLanguageModule('rbac');

        $this->ref_id = $a_ref_id;
        $this->role_id = $a_role_id;

        $this->setRowTemplate("tpl.obj_role_template_perm_row.html", "Services/AccessControl");
        $this->setLimit(100);
        $this->setShowRowsSelector(false);
        $this->setDisableFilterHiding(true);
        $this->setNoEntriesText($this->lng->txt('msg_no_roles_of_type'));

        $this->setEnableHeader(false);
        $this->disable('sort');
        $this->disable('numinfo');
        $this->disable('form');

        $this->addColumn('', '', '0');
        $this->addColumn('', '', '100%');

        $this->initTemplatePermissions();
    }

    public function setShowChangeExistingObjects(bool $a_status): void
    {
        $this->show_change_existing_objects = $a_status;
    }

    public function getShowChangeExistingObjects(): bool
    {
        return $this->show_change_existing_objects;
    }

    protected function initTemplatePermissions(): void
    {
        if (self::$template_permissions !== null) {
            return;
        }
        self::$template_permissions = $this->review->getAllOperationsOfRole(
            $this->getRoleId(),
            $this->getRefId()
        );
    }

    /**
     * Get permissions by type
     */
    protected function getPermissions(string $a_type): array
    {
        return !isset(self::$template_permissions[$a_type]) ? [] : self::$template_permissions[$a_type];
    }

    public function getTemplateType(): string
    {
        return $this->tpl_type;
    }

    public function getRoleId(): int
    {
        return $this->role_id;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getObjId(): int
    {
        return ilObject::_lookupObjId($this->getRefId());
    }

    public function getObjType(): string
    {
        return ilObject::_lookupType($this->getObjId());
    }

    protected function fillRow(array $a_set): void
    {
        if (isset($a_set['show_ce'])) {
            $this->tpl->setCurrentBlock('ce_td');
            $this->tpl->setVariable('CE_TYPE', $this->getTemplateType());
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('ce_desc_td');
            $this->tpl->setVariable('CE_DESC_TYPE', $this->getTemplateType());
            $this->tpl->setVariable('CE_LONG', $this->lng->txt('change_existing_object_type_desc'));

            if ($this->objDefinition->isSystemObject($this->getTemplateType())) {
                $this->tpl->setVariable(
                    "TXT_CE",
                    $this->lng->txt("change_existing_prefix_single") . " " .
                    $this->lng->txt("obj_" . $this->getTemplateType()) . " " .
                    $this->lng->txt("change_existing_suffix_single")
                );
            } else {
                $pl_txt = ($this->objDefinition->isPlugin($this->getTemplateType()))
                    ? ilObjectPlugin::lookupTxtById(
                        $this->getTemplateType(),
                        "objs_" . $this->getTemplateType()
                    )
                    : $this->lng->txt('objs_' . $this->getTemplateType());
                $this->tpl->setVariable(
                    'TXT_CE',
                    $this->lng->txt('change_existing_prefix') . ' ' .
                    $pl_txt . ' ' .
                    $this->lng->txt('change_existing_suffix')
                );
                $this->tpl->parseCurrentBlock();
            }
        } else {
            $this->tpl->setCurrentBlock('perm_td');
            $this->tpl->setVariable('OBJ_TYPE', $this->getTemplateType());
            $this->tpl->setVariable('PERM_PERM_ID', $a_set['ops_id']);
            $this->tpl->setVariable('PERM_CHECKED', $a_set['set'] ? 'checked="checked"' : '');

            if ($this->getRoleId() == SYSTEM_ROLE_ID) {
                $this->tpl->setVariable('PERM_DISABLED', 'disabled="disabled"');
            }

            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('perm_desc_td');
            $this->tpl->setVariable('DESC_TYPE', $this->getTemplateType());
            $this->tpl->setVariable('DESC_PERM_ID', $a_set['ops_id']);

            $create_type = $a_set["create_type"] ?? "";
            if ($create_type != "" && $this->objDefinition->isPlugin($a_set['create_type'])) {
                $this->tpl->setVariable(
                    'TXT_PERMISSION',
                    ilObjectPlugin::lookupTxtById(
                        $a_set['create_type'],
                        $this->getTemplateType() . "_" . $a_set['name']
                    )
                );
            } elseif ($create_type == "" && $this->objDefinition->isPlugin($this->getTemplateType())) {
                $this->tpl->setVariable(
                    'TXT_PERMISSION',
                    ilObjectPlugin::lookupTxtById(
                        $this->getTemplateType(),
                        $this->getTemplateType() . "_" . $a_set['name']
                    )
                );
            } else {
                if (substr($a_set['name'], 0, 6) == 'create') {
                    #$perm = $this->lng->txt($this->getTemplateType().'_'.$row['name']);
                    $perm = $this->lng->txt('rbac' . '_' . $a_set['name']);
                } elseif ($this->lng->exists($this->getTemplateType() . '_' . $a_set['name'] . '_short')) {
                    $perm = $this->lng->txt($this->getTemplateType() . '_' . $a_set['name'] . '_short') . ': ' .
                        $this->lng->txt($this->getTemplateType() . '_' . $a_set['name']);
                } else {
                    $perm = $this->lng->txt($a_set['name']) . ': ' . $this->lng->txt($this->getTemplateType() . '_' . $a_set['name']);
                }

                $this->tpl->setVariable('TXT_PERMISSION', $perm);
            }
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * Parse permissions
     * @return
     */
    public function parse(): void
    {
        $operations = $this->getPermissions($this->getTemplateType());

        // Object permissions
        $rows = array();
        foreach ($this->review->getOperationsByTypeAndClass($this->getTemplateType(), 'object') as $ops_id) {
            $operations = $this->getPermissions($this->getTemplateType());

            $operation = $this->review->getOperation($ops_id);

            $perm['ops_id'] = $ops_id;
            $perm['set'] = (in_array($ops_id, $operations) || $this->getRoleId() == SYSTEM_ROLE_ID);
            $perm['name'] = $operation['operation'];

            $rows[] = $perm;
        }

        // Get creatable objects
        $objects = $this->objDefinition->getCreatableSubObjects($this->getTemplateType());
        $ops_ids = ilRbacReview::lookupCreateOperationIds(array_keys($objects));

        foreach ($objects as $type => $info) {
            $ops_id = $ops_ids[$type] ?? null;

            if (!$ops_id) {
                continue;
            }

            $perm['ops_id'] = $ops_id;
            $perm['set'] = (in_array($ops_id, $operations) || $this->getRoleId() == SYSTEM_ROLE_ID);

            $perm['name'] = 'create_' . $info['name'];
            $perm['create_type'] = $info['name'];

            $rows[] = $perm;
        }

        if (
            !$this->show_admin_permissions &&
            $this->getShowChangeExistingObjects()
        ) {
            $rows[] = array('show_ce' => 1);
        }
        $this->setData($rows);
    }
}
