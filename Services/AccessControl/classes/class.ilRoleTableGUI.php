<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI for the presentation og roles and role templates
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesAccessControl
 */
class ilRoleTableGUI extends ilTable2GUI
{
    private const TYPE_GLOBAL_AU = 1;
    private const TYPE_GLOBAL_UD = 2;
    private const TYPE_LOCAL_AU = 3;
    private const TYPE_LOCAL_UD = 4;
    private const TYPE_ROLT_AU = 5;
    private const TYPE_ROLT_UD = 6;

    private const FILTER_ROLE_TYPE = 'role_type';
    private const FILTER_TITLE = 'title';

    private const TYPE_VIEW = 1;
    private const TYPE_SEARCH = 2;

    private ilPathGUI $path_gui;

    private int $type = self::TYPE_VIEW;
    private string $role_title_filter = '';
    private int $role_folder_id = 0;

    private array $filter = [];
    private ilTree $tree;
    private ilRbacReview $rbacreview;
    private ilRbacSystem $system;

    public function __construct(object $a_parent_gui, string $a_parent_cmd)
    {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();
        $this->tree = $DIC->repositoryTree();
        $this->system = $DIC->rbac()->system();

        $this->setId('rolf_role_tbl');
        parent::__construct($a_parent_gui, $a_parent_cmd);
        $this->lng->loadLanguageModule('rbac');
        $this->lng->loadLanguageModule('search');
    }

    public function setType(int $a_type) : void
    {
        $this->type = $a_type;
    }

    public function setRoleTitleFilter(string $a_filter) : void
    {
        $this->role_title_filter = $a_filter;
    }

    public function getRoleTitleFilter() : string
    {
        return $this->role_title_filter;
    }

    public function getType() : int
    {
        return $this->type;
    }

    protected function getPathGUI() : ilPathGUI
    {
        return $this->path_gui;
    }

    /**
     * Init table
     */
    public function init() : void
    {
        $this->addColumn('', 'f', '1px');

        switch ($this->getType()) {
            case self::TYPE_VIEW:
                $this->setShowRowsSelector(true);
                $this->setDefaultOrderField('title');
                $this->setDefaultOrderDirection('asc');
                //$this->setId('rolf_role_tbl');
                $this->addColumn($this->lng->txt('search_title_description'), 'title', '30%');
                $this->addColumn($this->lng->txt('type'), 'rtype', '20%');
                $this->addColumn($this->lng->txt('context'), '', '40%');
                $this->addColumn($this->lng->txt('actions'), '', '10%');
                $this->setTitle($this->lng->txt('objs_role'));

                if ($GLOBALS['DIC']['rbacsystem']->checkAccess('delete',
                    $this->getParentObject()->object->getRefId())) {
                    $this->addMultiCommand('confirmDelete', $this->lng->txt('delete'));
                }
                break;

            case self::TYPE_SEARCH:
                $this->setShowRowsSelector(true);
                $this->disable('sort');
                //$this->setId('rolf_role_search_tbl');
                $this->addColumn($this->lng->txt('search_title_description'), 'title', '30%');
                $this->addColumn($this->lng->txt('type'), 'rtype', '20%');
                $this->addColumn($this->lng->txt('context'), '', '50%');
                $this->setTitle($this->lng->txt('rbac_role_rights_copy'));
                $this->addMultiCommand('chooseCopyBehaviour', $this->lng->txt('btn_next'));
                $this->addCommandButton('roleSearch', $this->lng->txt('btn_previous'));
                break;
        }

        $this->setRowTemplate('tpl.role_row.html', 'Services/AccessControl');
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
        $this->setSelectAllCheckbox('roles');

        $this->path_gui = new ilPathGUI();
        $this->getPathGUI()->enableTextOnly(false);
        $this->getPathGUI()->enableHideLeaf(false);
        $this->initFilter();
    }

    /**
     * Init filter
     */
    public function initFilter() : void
    {
        $this->setDisableFilterHiding(true);

        $action = [];
        switch ($this->getType()) {
            case self::TYPE_VIEW:
                $action[ilRbacReview::FILTER_ALL] = $this->lng->txt('all_roles');
                $action[ilRbacReview::FILTER_ALL_GLOBAL] = $this->lng->txt('all_global_roles');
                $action[ilRbacReview::FILTER_ALL_LOCAL] = $this->lng->txt('all_local_roles');
                $action[ilRbacReview::FILTER_INTERNAL] = $this->lng->txt('internal_local_roles_only');
                $action[ilRbacReview::FILTER_NOT_INTERNAL] = $this->lng->txt('non_internal_local_roles_only');
                $action[ilRbacReview::FILTER_TEMPLATES] = $this->lng->txt('role_templates_only');
                break;

            case self::TYPE_SEARCH:
                $action[ilRbacReview::FILTER_ALL] = $this->lng->txt('all_roles');
                $action[ilRbacReview::FILTER_ALL_GLOBAL] = $this->lng->txt('all_global_roles');
                $action[ilRbacReview::FILTER_ALL_LOCAL] = $this->lng->txt('all_local_roles');
                $action[ilRbacReview::FILTER_INTERNAL] = $this->lng->txt('internal_local_roles_only');
                $action[ilRbacReview::FILTER_NOT_INTERNAL] = $this->lng->txt('non_internal_local_roles_only');
                break;
        }

        $roles = new ilSelectInputGUI($this->lng->txt('rbac_role_selection'), 'role_type');
        $roles->setOptions($action);
        $this->addFilterItem($roles);
        $roles->readFromSession();

        if (!$roles->getValue()) {
            $roles->setValue(ilRbacReview::FILTER_ALL_GLOBAL);
        }

        // title filter
        $title = new ilTextInputGUI($this->lng->txt('title'), self::FILTER_TITLE);
        $title->setSize(16);
        $title->setMaxLength(64);
        $this->addFilterItem($title);
        $title->readFromSession();

        $this->filter[self::FILTER_ROLE_TYPE] = (int) $roles->getValue();
        $this->filter[self::FILTER_TITLE] = (string) $title->getValue();
    }

    protected function fillRow(array $a_set) : void
    {
        if ($a_set['type'] == 'role') {
            if ($a_set['parent'] != ROLE_FOLDER_ID) {
                $this->ctrl->setParameterByClass(
                    "ilobjrolegui",
                    "rolf_ref_id",
                    $a_set['parent']
                );
            }

            $this->ctrl->setParameterByClass("ilobjrolegui", "obj_id", $a_set["obj_id"]);
            $link = $this->ctrl->getLinkTargetByClass("ilobjrolegui", "perm");
            $this->ctrl->setParameterByClass("ilobjrolegui", "rolf_ref_id", "");
        } else {
            $this->ctrl->setParameterByClass("ilobjroletemplategui", "obj_id", $a_set["obj_id"]);
            $link = $this->ctrl->getLinkTargetByClass("ilobjroletemplategui", "perm");
        }

        switch ($a_set['rtype']) {
            case self::TYPE_GLOBAL_AU:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_auto_global'));
                break;
            case self::TYPE_GLOBAL_UD:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_ud_global'));
                break;
            case self::TYPE_LOCAL_AU:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_auto_local'));
                break;
            case self::TYPE_LOCAL_UD:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_ud_local'));
                break;
            case self::TYPE_ROLT_AU:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_auto_rolt'));
                break;
            case self::TYPE_ROLT_UD:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_ud_rolt'));
                break;
        }

        if (
            ($a_set['obj_id'] != ANONYMOUS_ROLE_ID and
                $a_set['obj_id'] != SYSTEM_ROLE_ID and
                substr($a_set['title_orig'], 0, 3) != 'il_') or
            $this->getType() == self::TYPE_SEARCH) {
            $this->tpl->setVariable('VAL_ID', $a_set['obj_id']);
        }
        $this->tpl->setVariable('VAL_TITLE_LINKED', $a_set['title']);
        $this->tpl->setVariable('VAL_LINK', $link);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }

        $ref = $a_set['parent'];
        if ($ref == ROLE_FOLDER_ID) {
            $this->tpl->setVariable('CONTEXT', $this->lng->txt('rbac_context_global'));
        } else {
            $this->tpl->setVariable(
                'CONTEXT',
                (string) $this->getPathGUI()->getPath(ROOT_FOLDER_ID, (int) $ref)
            );
        }

        if ($this->getType() == self::TYPE_VIEW and $a_set['obj_id'] != SYSTEM_ROLE_ID) {
            if ($this->system->checkAccess('write', $this->role_folder_id)) {
                // Copy role
                $this->tpl->setVariable('COPY_TEXT', $this->lng->txt('rbac_role_rights_copy'));
                $this->ctrl->setParameter($this->getParentObject(), "csource", $a_set["obj_id"]);
                $link = $this->ctrl->getLinkTarget($this->getParentObject(), 'roleSearch');
                $this->tpl->setVariable(
                    'COPY_LINK',
                    $link
                );
            }
        }
    }

    /**
     * Parse role list
     * @param array $role_list
     */
    public function parse(int $role_folder_id) : void
    {
        $this->role_folder_id = $role_folder_id;

        $filter_orig = '';
        if ($this->getType() == self::TYPE_VIEW) {
            $filter_orig = $title_filter = $this->filter[self::FILTER_TITLE];
            $type_filter = $this->filter[self::FILTER_ROLE_TYPE];
        } else {
            $filter_orig = $title_filter = $this->getRoleTitleFilter();
            $type_filter = ilRbacReview::FILTER_ALL;
        }

        // the translation must be filtered
        if ($type_filter == ilRbacReview::FILTER_INTERNAL || $type_filter == ilRbacReview::FILTER_ALL) {
            // roles like il_crs_... are filtered manually
            $title_filter = '';
        }

        $role_list = $this->rbacreview->getRolesByFilter(
            $type_filter,
            0,
            (string) $title_filter
        );

        $counter = 0;
        $rows = array();
        foreach ($role_list as $role) {
            if (
                $role['parent'] and
                (
                    $this->tree->isDeleted($role['parent']) ||
                    !$this->tree->isInTree($role['parent'])
                )
            ) {
                continue;
            }

            $title = ilObjRole::_getTranslation($role['title']);
            if ($type_filter == ilRbacReview::FILTER_INTERNAL || $type_filter == ilRbacReview::FILTER_ALL) {
                if (strlen($filter_orig)) {
                    if (stristr($title, $filter_orig) == false) {
                        continue;
                    }
                }
            }

            $rows[$counter]['title_orig'] = $role['title'];
            $rows[$counter]['title'] = $title;
            $rows[$counter]['description'] = $role['description'];
            $rows[$counter]['obj_id'] = $role['obj_id'];
            $rows[$counter]['parent'] = $role['parent'];
            $rows[$counter]['type'] = $role['type'];

            $auto = substr($role['title'], 0, 3) == 'il_';

            // Role templates
            if ($role['type'] == 'rolt') {
                $rows[$counter]['rtype'] = $auto ? self::TYPE_ROLT_AU : self::TYPE_ROLT_UD;
            } elseif ($role['parent'] == ROLE_FOLDER_ID) {
                // Roles
                if ($role['obj_id'] == ANONYMOUS_ROLE_ID or $role['obj_id'] == SYSTEM_ROLE_ID) {
                    $rows[$counter]['rtype'] = self::TYPE_GLOBAL_AU;
                } else {
                    $rows[$counter]['rtype'] = self::TYPE_GLOBAL_UD;
                }
            } else {
                $rows[$counter]['rtype'] = $auto ? self::TYPE_LOCAL_AU : self::TYPE_LOCAL_UD;
            }

            ++$counter;
        }
        $this->setMaxCount(count($rows));
        $this->setData($rows);
    }
}
