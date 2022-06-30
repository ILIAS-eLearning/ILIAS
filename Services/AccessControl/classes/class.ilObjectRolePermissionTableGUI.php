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
class ilObjectRolePermissionTableGUI extends ilTable2GUI
{
    public const ROLE_FILTER_ALL = 1;
    public const ROLE_FILTER_GLOBAL = 2;
    public const ROLE_FILTER_LOCAL = 3;
    public const ROLE_FILTER_LOCAL_POLICY = 4;
    public const ROLE_FILTER_LOCAL_OBJECT = 5;

    private int $ref_id;
    private array $tree_path_ids = [];
    private array $activeOperations = [];
    private array $visible_roles = [];

    protected ilTree $tree;
    protected ilRbacReview $review;
    protected ilObjectDefinition $objDefinition;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $a_ref_id)
    {
        global $DIC;

        $this->objDefinition = $DIC['objDefinition'];
        $this->review = $DIC->rbac()->review();

        $this->ref_id = $a_ref_id;
        $this->setId('objroleperm_' . $this->ref_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->lng->loadLanguageModule('rbac');

        $this->tree = $DIC->repositoryTree();
        $this->tree_path_ids = $this->tree->getPathId($this->ref_id);

        $tpl = $DIC->ui()->mainTemplate();
        $tpl->addJavaScript('./Services/AccessControl/js/ilPermSelect.js');

        $this->setTitle($this->lng->txt('permission_settings'));
        $this->setEnableHeader(true);
        $this->disable('sort');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->disable('numinfo');
        $this->setRowTemplate("tpl.obj_role_perm_row.html", "Services/AccessControl");
        $this->setLimit(100);
        $this->setShowRowsSelector(false);
        $this->setDisableFilterHiding(true);
        $this->setNoEntriesText($this->lng->txt('msg_no_roles_of_type'));
        $this->addCommandButton('savePermissions', $this->lng->txt('save'));
        $this->initFilter();
    }

    /**
     * Get tree path ids
     */
    public function getPathIds() : array
    {
        return $this->tree_path_ids;
    }

    /**
     * Get ref id of current object
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }

    /**
     * Get obj id
     */
    public function getObjId() : int
    {
        return ilObject::_lookupObjId($this->getRefId());
    }

    /**
     * get obj type
     */
    public function getObjType() : string
    {
        return ilObject::_lookupType($this->getObjId());
    }

    /**
     * Add active operation
     */
    public function addActiveOperation(int $a_ops_id) : void
    {
        $this->activeOperations[] = $a_ops_id;
    }

    /**
     * get active operations
     * @return int[]
     */
    public function getActiveOperations() : array
    {
        return $this->activeOperations;
    }

    public function setVisibleRoles(array $a_ar) : void
    {
        $this->visible_roles = $a_ar;
    }

    /**
     * get visible roles
     */
    public function getVisibleRoles() : array
    {
        return $this->visible_roles;
    }

    /**
     * Init role filter
     */
    public function initFilter() : void
    {
        global $DIC;

        $tree = $DIC['tree'];

        $roles = $this->addFilterItemByMetaType(
            'role',
            ilTable2GUI::FILTER_SELECT
        );

        // Limit filter to local roles only for objects with group or course in path
        if (!$roles->getValue()) {
            if ($tree->checkForParentType($this->getRefId(), 'crs') || $tree->checkForParentType($this->getRefId(), 'grp')) {
                $roles->setValue(self::ROLE_FILTER_LOCAL);
            } else {
                $roles->setValue(self::ROLE_FILTER_ALL);
            }
        }

        $roles->setOptions(
            array(
                self::ROLE_FILTER_ALL => $this->lng->txt('filter_all_roles'),
                self::ROLE_FILTER_GLOBAL => $this->lng->txt('filter_global_roles'),
                self::ROLE_FILTER_LOCAL => $this->lng->txt('filter_local_roles'),
                self::ROLE_FILTER_LOCAL_POLICY => $this->lng->txt('filter_roles_local_policy'),
                self::ROLE_FILTER_LOCAL_OBJECT => $this->lng->txt('filter_local_roles_object')
            )
        );
    }

    /**
     */
    protected function fillRow(array $a_set) : void
    {
        // local policy
        if (isset($a_set['show_local_policy_row'])) {
            foreach ($a_set['roles'] as $role_id => $role_info) {
                $this->tpl->setCurrentBlock('role_option');
                $this->tpl->setVariable('INHERIT_ROLE_ID', $role_id);
                $this->tpl->setVariable('INHERIT_CHECKED', $role_info['local_policy'] ? 'checked=checked' : '');
                $this->tpl->setVariable(
                    'INHERIT_DISABLED',
                    ($role_info['protected'] || $role_info['isLocal'] || $role_info['blocked']) ? 'disabled="disabled"' : ''
                );
                $this->tpl->setVariable('TXT_INHERIT', $this->lng->txt('rbac_local_policy'));
                $this->tpl->setVariable('INHERIT_LONG', $this->lng->txt('perm_use_local_policy_desc'));
                $this->tpl->parseCurrentBlock();
            }
            return;
        }
        // protected
        if (isset($a_set['show_protected_row'])) {
            foreach ($a_set['roles'] as $role_id => $role_info) {
                $this->tpl->setCurrentBlock('role_protect');
                $this->tpl->setVariable('PROTECT_ROLE_ID', $role_id);
                $this->tpl->setVariable('PROTECT_CHECKED', $role_info['protected_status'] ? 'checked=checked' : '');
                $this->tpl->setVariable(
                    'PROTECT_DISABLED',
                    $role_info['protected_allowed'] ? '' : 'disabled="disabled"'
                );
                $this->tpl->setVariable('TXT_PROTECT', $this->lng->txt('role_protect_permissions'));
                $this->tpl->setVariable('PROTECT_LONG', $this->lng->txt('role_protect_permissions_desc'));
                $this->tpl->parseCurrentBlock();
            }
            return;
        }

        // block role
        if (isset($a_set['show_block_row'])) {
            foreach ($this->getVisibleRoles() as $role_info) {
                $this->tpl->setCurrentBlock('role_block');
                $this->tpl->setVariable('BLOCK_ROLE_ID', $role_info['obj_id']);
                $this->tpl->setVariable('TXT_BLOCK', $this->lng->txt('role_block_role'));
                $this->tpl->setVariable('BLOCK_LONG', $this->lng->txt('role_block_role_desc'));
                if ($role_info['blocked']) {
                    $this->tpl->setVariable('BLOCK_CHECKED', 'checked="checked"');
                }
                if (
                    ($role_info['protected'] == 'y') ||
                    ($role_info['assign'] == 'y' && $role_info['parent'] == $this->getRefId())
                ) {
                    $this->tpl->setVariable('BLOCK_DISABLED', 'disabled="disabled');
                }

                $this->tpl->parseCurrentBlock();
            }
            return;
        }

        // Select all
        if (isset($a_set['show_select_all'])) {
            foreach ($this->getVisibleRoles() as $role) {
                $this->tpl->setCurrentBlock('role_select_all');
                $this->tpl->setVariable('JS_ROLE_ID', $role['obj_id']);
                $this->tpl->setVariable('JS_SUBID', $a_set['subtype']);
                $this->tpl->setVariable('JS_ALL_PERMS', "['" . implode("','", $a_set['ops']) . "']");
                $this->tpl->setVariable('JS_FORM_NAME', $this->getFormName());
                $this->tpl->setVariable('TXT_SEL_ALL', $this->lng->txt('select_all'));
                $this->tpl->parseCurrentBlock();
            }
            return;
        }

        // Object permissions
        if (isset($a_set['show_start_info'])) {
            $this->tpl->setCurrentBlock('section_info');
            $this->tpl->setVariable('SECTION_TITLE', $this->lng->txt('perm_class_object'));
            $this->tpl->setVariable('SECTION_DESC', $this->lng->txt('perm_class_object_desc'));
            $this->tpl->parseCurrentBlock();

            return;
        }

        if (isset($a_set['show_create_info'])) {
            $this->tpl->setCurrentBlock('section_info');
            $this->tpl->setVariable('SECTION_TITLE', $this->lng->txt('perm_class_create'));
            $this->tpl->setVariable('SECTION_DESC', $this->lng->txt('perm_class_create_desc'));
            $this->tpl->parseCurrentBlock();

            return;
        }

        foreach ((array) $a_set['roles'] as $role_id => $role_info) {
            $perm = "";
            $this->tpl->setCurrentBlock('role_td');
            $this->tpl->setVariable('PERM_ROLE_ID', $role_id);
            $this->tpl->setVariable('PERM_PERM_ID', $a_set['perm']['ops_id']);

            if (substr($a_set['perm']['operation'], 0, 6) == 'create') {
                if ($this->objDefinition->isPlugin(substr($a_set['perm']['operation'], 7))) {
                    $perm = ilObjectPlugin::lookupTxtById(
                        substr($a_set['perm']['operation'], 7),
                        "obj_" . substr($a_set['perm']['operation'], 7)
                    );
                } else {
                    $perm = $this->lng->txt('obj_' . substr($a_set['perm']['operation'], 7));
                }
            } else {
                if ($this->objDefinition->isPlugin($this->getObjType())) {
                    if (ilObjectPlugin::langExitsById($this->getObjType(), $a_set['perm']['operation'])) {
                        $perm = ilObjectPlugin::lookupTxtById($this->getObjType(), $a_set['perm']['operation']);
                    }
                }

                if (!$perm) {
                    if ($this->lng->exists($this->getObjType() . '_' . $a_set['perm']['operation'] . '_short')) {
                        $perm = $this->lng->txt($this->getObjType() . '_' . $a_set['perm']['operation'] . '_short');
                    } else {
                        $perm = $this->lng->txt($a_set['perm']['operation']);
                    }
                }
            }

            $this->tpl->setVariable('TXT_PERM', $perm);

            if ($this->objDefinition->isPlugin($this->getObjType())) {
                $this->tpl->setVariable('PERM_LONG', ilObjectPlugin::lookupTxtById(
                    $this->getObjType(),
                    $this->getObjType() . "_" . $a_set['perm']['operation']
                ));
            } elseif (substr($a_set['perm']['operation'], 0, 6) == 'create') {
                if ($this->objDefinition->isPlugin(substr($a_set['perm']['operation'], 7))) {
                    $this->tpl->setVariable('PERM_LONG', ilObjectPlugin::lookupTxtById(
                        substr($a_set['perm']['operation'], 7),
                        $this->getObjType() . "_" . $a_set['perm']['operation']
                    ));
                } else {
                    $this->tpl->setVariable('PERM_LONG', $this->lng->txt('rbac_' . $a_set['perm']['operation']));
                }
            } else {
                $this->tpl->setVariable(
                    'PERM_LONG',
                    $this->lng->txt($this->getObjType() . '_' . $a_set['perm']['operation'])
                );
            }

            if ($role_info['protected'] || $role_info['blocked']) {
                $this->tpl->setVariable('PERM_DISABLED', 'disabled="disabled"');
            }
            if ($role_info['permission_set']) {
                $this->tpl->setVariable('PERM_CHECKED', 'checked="checked"');
            }

            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * Parse
     */
    public function parse() : void
    {
        $this->initColumns();

        $perms = array();
        $roles = array();

        if ($this->getVisibleRoles() === []) {
            $this->setData(array());
            return;
        }

        // Read operations of role
        $operations = array();
        foreach ($this->getVisibleRoles() as $role_data) {
            $operations[$role_data['obj_id']] = $this->review->getActiveOperationsOfRole(
                $this->getRefId(),
                $role_data['obj_id']
            );
        }

        $counter = 0;

        // Local policy
        if (ilPermissionGUI::hasContainerCommands($this->getObjType())) {
            $roles = array();
            $local_roles = $this->review->getRolesOfObject($this->getRefId());
            foreach ($this->getVisibleRoles() as $role_data) {
                $roles[$role_data['obj_id']] = array(
                    'blocked' => $role_data['blocked'],
                    'protected' => $role_data['protected'],
                    'local_policy' => in_array($role_data['obj_id'], $local_roles),
                    'isLocal' => ($this->getRefId() == $role_data['parent']) && $role_data['assign'] == 'y'
                );
            }
            $perms[$counter]['roles'] = $roles;
            $perms[$counter]['show_local_policy_row'] = 1;

            $counter++;
        }

        // Protect permissions
        if (ilPermissionGUI::hasContainerCommands($this->getObjType())) {
            $roles = array();
            foreach ($this->getVisibleRoles() as $role_data) {
                $roles[$role_data['obj_id']] = array(
                    'blocked' => $role_data['blocked'],
                    'protected_allowed' => $this->review->isAssignable($role_data['obj_id'], $this->getRefId()),
                    'protected_status' => $this->review->isProtected($role_data['parent'], $role_data['obj_id']),
                    'isLocal' => ($this->getRefId() == $role_data['parent']) && $role_data['assign'] == 'y'
                );
            }
            $perms[$counter]['roles'] = $roles;
            $perms[$counter]['show_protected_row'] = 1;

            $counter++;
        }
        // Block role
        if (ilPermissionGUI::hasContainerCommands($this->getObjType())) {
            $perms[$counter++]['show_block_row'] = 1;
        }

        if (ilPermissionGUI::hasContainerCommands($this->getObjType())) {
            $perms[$counter++]['show_start_info'] = true;
        }

        // no creation permissions
        $no_creation_operations = array();
        foreach ($this->review->getOperationsByTypeAndClass($this->getObjType(), 'object') as $operation) {
            $this->addActiveOperation($operation);
            $no_creation_operations[] = $operation;

            $roles = array();
            foreach ($this->getVisibleRoles() as $role_data) {
                $roles[$role_data['obj_id']] =
                    array(
                        'blocked' => $role_data['blocked'],
                        'protected' => $role_data['protected'],
                        'permission_set' => in_array($operation, (array) $operations[$role_data['obj_id']]),
                        'isLocal' => ($this->getRefId() == $role_data['parent']) && $role_data['assign'] == 'y'
                    );
            }

            $op = $this->review->getOperation($operation);

            $perms[$counter]['roles'] = $roles;
            $perms[$counter]['perm'] = $op;
            $counter++;
        }

        /*
         * Select all
         */
        if ($no_creation_operations !== []) {
            $perms[$counter]['show_select_all'] = 1;
            $perms[$counter]['ops'] = $no_creation_operations;
            $perms[$counter]['subtype'] = 'nocreation';
            $counter++;
        }

        if ($this->objDefinition->isContainer($this->getObjType())) {
            $perms[$counter++]['show_create_info'] = true;
        }

        // Get creatable objects
        $objects = $this->objDefinition->getCreatableSubObjects($this->getObjType());
        $ops_ids = ilRbacReview::lookupCreateOperationIds(array_keys($objects));
        $creation_operations = array();
        foreach ($objects as $type => $info) {
            $ops_id = $ops_ids[$type];

            if (!$ops_id) {
                continue;
            }

            $this->addActiveOperation($ops_id);
            $creation_operations[] = $ops_id;

            $roles = array();
            foreach ($this->getVisibleRoles() as $role_data) {
                $roles[$role_data['obj_id']] =
                    array(
                        'blocked' => $role_data['blocked'],
                        'protected' => $role_data['protected'],
                        'permission_set' => in_array($ops_id, (array) $operations[$role_data['obj_id']]),
                        'isLocal' => ($this->getRefId() == $role_data['parent']) && $role_data['assign'] == 'y'

                    );
            }

            $op = $this->review->getOperation($ops_id);

            $perms[$counter]['roles'] = $roles;
            $perms[$counter]['perm'] = $op;
            $counter++;
        }

        // Select all
        if ($creation_operations !== []) {
            $perms[$counter]['show_select_all'] = 1;
            $perms[$counter]['ops'] = $creation_operations;
            $perms[$counter]['subtype'] = 'creation';
            $counter++;
        }

        $this->setData($perms);
    }

    protected function initColumns() : void
    {
        global $DIC;

        $roles = $this->review->getParentRoleIds($this->getRefId());
        $roles = $this->getParentObject()->applyRoleFilter(
            $roles,
            (int) $this->getFilterItemByPostVar('role')->getValue()
        );

        $possible_roles = array();
        foreach ($roles as $role) {
            if ($this->review->isBlockedInUpperContext((int) $role['obj_id'], $this->getRefId())) {
                ilLoggerFactory::getLogger('ac')->debug('Ignoring blocked role: ' . $role['obj_id']);
                continue;
            }
            $possible_roles[] = $role;
        }

        if ($possible_roles !== []) {
            $column_width = 100 / count($possible_roles);
            $column_width .= '%';
        } else {
        }

        $all_roles = array();
        foreach ($possible_roles as $role) {
            if ($role['obj_id'] == SYSTEM_ROLE_ID) {
                continue;
            }
            $role['obj_id'] = (int) $role['obj_id'];
            $role['blocked'] = (bool) $this->review->isBlockedAtPosition($role['obj_id'], $this->getRefId());
            $role['role_type'] = $this->review->isGlobalRole($role['obj_id']) ? 'global' : 'local';

            // TODO check filter
            $this->addColumn(
                $this->createTitle($role),
                (string) $role['obj_id'],
                '',
                false,
                '',
                $this->createTooltip($role)
            );
            $all_roles[] = $role;
        }

        $this->setVisibleRoles($all_roles);
    }

    /**
     * Create a linked title for roles with local policy
     */
    protected function createTooltip(array $role) : string
    {
        $protected_status = $this->review->isProtected($role['parent'], $role['obj_id']) ? 'protected_' : '';
        if ($role['role_type'] == 'global') {
            $tp = $this->lng->txt('perm_' . $protected_status . 'global_role');
        } else {
            $tp = $this->lng->txt('perm_' . $protected_status . 'local_role');
        }

        $inheritance_seperator = ': ';

        // Show create at info
        if (
            $role['assign'] == 'y' && $role['role_type'] != 'global' || $role['assign'] == 'n' && $role['role_type'] != 'global'
        ) {
            $tp .= ': ';

            $obj = $this->review->getObjectOfRole($role['obj_id']);
            if ($obj) {
                $type = ilObject::_lookupType($this->getRefId(), true);
                if ($this->objDefinition->isPlugin($type)) {
                    $type_text = ilObjectPlugin::lookupTxtById($type, 'obj_' . $type);
                } else {
                    $type_text = $this->lng->txt('obj_' . ilObject::_lookupType($obj));
                }

                $tp .= sprintf(
                    $this->lng->txt('perm_role_path_info_created'),
                    $type_text,
                    ilObject::_lookupTitle($obj)
                );
                $inheritance_seperator = ', ';
            }
        }

        $path_hierarchy = $this->review->getObjectsWithStopedInheritance(
            $role['obj_id'],
            $this->tree->getPathId($this->getRefId())
        );

        $reduced_path_hierarchy = array_diff(
            $path_hierarchy,
            array(
                $this->getRefId(),
                $this->review->getObjectReferenceOfRole($role['obj_id'])
            )
        );

        // Inheritance
        if ($role['assign'] == 'n' && count($reduced_path_hierarchy)) {
            $tp .= $inheritance_seperator;

            $parent = end($reduced_path_hierarchy);
            $p_type = ilObject::_lookupType(ilObject::_lookupObjId($parent));
            $p_title = ilObject::_lookupTitle(ilObject::_lookupObjId($parent));
            $tp .= sprintf(
                $this->lng->txt('perm_role_path_info_inheritance'),
                $this->lng->txt('obj_' . $p_type),
                $p_title
            );
        }

        return $tp;
    }

    /**
     * Create (linked) title
     */
    protected function createTitle(array $role) : string
    {
        $role_title = ilObjRole::_getTranslation($role['title']);

        // No local policies
        if ($role['parent'] != $this->getRefId()) {
            return $role_title;
        }

        $type = ilObject::_lookupType($this->getRefId(), true);
        if ($this->objDefinition->isPlugin($type)) {
            if (preg_match("/^il_./", $role["title"])) {
                $role_title = ilObjectPlugin::lookupTxtById($type, ilObjRole::_removeObjectId($role["title"]));
            }
        }

        if ($role['blocked']) {
            return $role_title;
        }
        $this->ctrl->setParameterByClass('ilobjrolegui', 'obj_id', $role['obj_id']);

        return '<a class="tblheader" href="' . $this->ctrl->getLinkTargetByClass(
            'ilobjrolegui',
            ''
        ) . '" >' . $role_title . '</a>';
    }
}
