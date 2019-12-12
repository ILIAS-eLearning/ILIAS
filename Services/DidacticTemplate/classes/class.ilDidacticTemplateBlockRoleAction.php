<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateAction.php';

/**
 * Description of ilDidacticTemplateBlockRoleAction
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateBlockRoleAction extends ilDidacticTemplateAction
{
    const FILTER_SOURCE_TITLE = 1;
    const FILTER_SOURCE_OBJ_ID = 2;
    const FILTER_PARENT_ROLES = 3;

    const PATTERN_PARENT_TYPE = 'action';


    private $pattern = array();
    private $filter_type = self::FILTER_SOURCE_TITLE;

    /**
     * Constructor
     * @param int $action_id
     */
    public function __construct($action_id = 0)
    {
        parent::__construct($action_id);
    }

    /**
     * Add filter
     * @param ilDidacticTemplateFilterPatter $pattern
     */
    public function addFilterPattern(ilDidacticTemplateFilterPattern $pattern)
    {
        $this->pattern[] = $pattern;
    }

    /**
     * Set filter patterns
     * @param array $patterns
     */
    public function setFilterPatterns(array $patterns)
    {
        $this->pattern = $patterns;
    }

    /**
     * Get filter pattern
     * @return array
     */
    public function getFilterPattern()
    {
        return $this->pattern;
    }

    /**
     * Set filter type
     * @param int $a_type
     */
    public function setFilterType($a_type)
    {
        $this->filter_type = $a_type;
    }

    /**
     * Get filter type
     * @return int
     */
    public function getFilterType()
    {
        return $this->filter_type;
    }

    /**
     * Save action
     */
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        parent::save();

        $query = 'INSERT INTO didactic_tpl_abr (action_id,filter_type) ' .
            'VALUES( ' .
            $ilDB->quote($this->getActionId(), 'integer') . ', ' .
            $ilDB->quote($this->getFilterType(), 'integer') . ' ' .
            ')';
        $ilDB->manipulate($query);

        foreach ($this->getFilterPattern() as $pattern) {
            /* @var ilDidacticTemplateFilterPattern $pattern */
            $pattern->setParentId($this->getActionId());
            $pattern->setParentType(self::PATTERN_PARENT_TYPE);
            $pattern->save();
        }
    }

    /**
     * delete action filter
     * @global ilDB $ilDB
     * @return bool
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        parent::delete();

        $query = 'DELETE FROM didactic_tpl_abr ' .
            'WHERE action_id  = ' . $ilDB->quote($this->getActionId(), 'integer');
        $ilDB->manipulate($query);

        foreach ($this->getFilterPattern() as $pattern) {
            $pattern->delete();
        }
        return true;
    }




    /**
     * Apply action
     */
    public function apply()
    {
        $source = $this->initSourceObject();
        $roles = $this->filterRoles($source);

        // Create local policy for filtered roles
        foreach ($roles as $role_id => $role) {
            $this->blockRole($role_id, $source);
        }
        return true;
    }

    /**
     * Blo k role
     * @param int $a_role_id
     * @param ilObject $source
     */
    protected function blockRole($a_role_id, $source)
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        
        // Set assign to 'y' only if it is a local role
        $assign = $rbacreview->isAssignable($a_role_id, $source->getRefId()) ? 'y' : 'n';

        // Delete permissions
        $rbacadmin->revokeSubtreePermissions($source->getRefId(), $a_role_id);

        // Delete template permissions
        $rbacadmin->deleteSubtreeTemplates($source->getRefId(), $a_role_id);

        $rbacadmin->assignRoleToFolder(
            $a_role_id,
            $source->getRefId(),
            $assign
        );
        return true;
    }

    /**
     * Revert action
     */
    public function revert()
    {
        $source = $this->initSourceObject();
        $roles = $this->filterRoles($source);

        // Create local policy for filtered roles
        foreach ($roles as $role_id => $role) {
            $this->deleteLocalPolicy($role_id, $source);
        }
        return true;
    }

    /**
     * Delete local policy
     *
     * @param int $a_role_id
     * @param ilObject $source
     */
    protected function deleteLocalPolicy($a_role_id, $source)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $rbacadmin = $DIC['rbacadmin'];

        // Create role folder if it does not exist
        //$rolf = $rbacreview->getRoleFolderIdOfObject($source->getRefId());

        if ($rbacreview->getRoleFolderOfRole($a_role_id) == $source->getRefId()) {
            ilLoggerFactory::getLogger('otpl')->debug('Ignoring local role: ' . ilObject::_lookupTitle($a_role_id));
            return false;
        }

        $rbacadmin->deleteLocalRole($a_role_id, $source->getRefId());

        // Change existing object
        include_once './Services/AccessControl/classes/class.ilObjRole.php';
        $role = new ilObjRole($a_role_id);
        $role->changeExistingObjects(
            $source->getRefId(),
            ilObjRole::MODE_UNPROTECTED_DELETE_LOCAL_POLICIES,
            array('all')
        );
        
        return true;
    }


    /**
     * Get action type
     * @return int
     */
    public function getType()
    {
        return self::TYPE_BLOCK_ROLE;
    }

    /**
     * Export to xml
     * @param ilXmlWriter $writer
     * @return void
     */
    public function toXml(ilXmlWriter $writer)
    {
        $writer->xmlStartTag('blockRoleAction');

        switch ($this->getFilterType()) {
            case self::FILTER_SOURCE_OBJ_ID:
                $writer->xmlStartTag('roleFilter', array('source' => 'objId'));
                break;

            default:
            case self::FILTER_SOURCE_TITLE:
                $writer->xmlStartTag('roleFilter', array('source' => 'title'));
                break;
        }

        foreach ($this->getFilterPattern() as $pattern) {
            $pattern->toXml($writer);
        }
        $writer->xmlEndTag('roleFilter');
        $writer->xmlEndTag('blockRoleAction');
        return;
    }

    /**
     *  clone method
     */
    public function __clone()
    {
        parent::__clone();

        // Clone patterns
        $cloned = array();
        foreach ($this->getFilterPattern() as $pattern) {
            $clones[] = clone $pattern;
        }
        $this->setFilterPatterns($clones);
    }

    /**
     * read action data
     * @global ilDB $ilDB
     * @return bool
     */
    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!parent::read()) {
            return false;
        }

        $query = 'SELECT * FROM didactic_tpl_abr ' .
            'WHERE action_id = ' . $ilDB->quote($this->getActionId());
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setFilterType($row->filter_type);
        }

        // Read filter
        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateFilterPatternFactory.php';
        foreach (ilDidacticTemplateFilterPatternFactory::lookupPatternsByParentId($this->getActionId(), self::PATTERN_PARENT_TYPE) as $pattern) {
            $this->addFilterPattern($pattern);
        }
    }
}
