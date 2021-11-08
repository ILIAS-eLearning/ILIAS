<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */



/**
 * Abstract class for template actions
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
abstract class ilDidacticTemplateAction
{
    public const TYPE_LOCAL_POLICY = 1;
    public const TYPE_LOCAL_ROLE = 2;
    public const TYPE_BLOCK_ROLE = 3;

    public const FILTER_SOURCE_TITLE = 1;
    public const FILTER_SOURCE_OBJ_ID = 2;
    public const FILTER_PARENT_ROLES = 3;
    public const FILTER_LOCAL_ROLES = 4;

    public const PATTERN_PARENT_TYPE = 'action';
    
    protected ilLogger $logger;
    protected ilDBInterface $db;
    protected ilRbacReview $review;
    protected ilRbacAdmin $admin;



    private int $action_id = 0;
    private int $tpl_id = 0;
    private int $type = 0;


    private int $ref_id = 0;

    public function __construct(int $action_id = 0)
    {
        global $DIC;

        $this->logger = $DIC->logger()->otpl();
        $this->db = $DIC->database();
        $this->review = $DIC->rbac()->review();
        $this->admin = $DIC->rbac()->admin();
        
        $this->setActionId($action_id);
        $this->read();
    }
    
    /**
     * Get logger
     * @return ilLogger
     */
    public function getLogger() : \ilLogger
    {
        return $this->logger;
    }

    /**
     * Get action id
     * @return int
     */
    public function getActionId() : int
    {
        return $this->action_id;
    }

    public function setActionId(int $a_action_id) : void
    {
        $this->action_id = $a_action_id;
    }

    public function setType(int $a_type_id) : void
    {
        $this->type = $a_type_id;
    }

    /**
     * Set template id
     * @param int $a_id
     */
    public function setTemplateId(int $a_id) : void
    {
        $this->tpl_id = $a_id;
    }

    public function getTemplateId() : int
    {
        return $this->tpl_id;
    }

    /**
     * Set ref id of target object.
     * @param int ref id
     * @return void
     */
    public function setRefId(int $a_ref_id) : void
    {
        $this->ref_id = $a_ref_id;
    }

    /**
     * Get ref id of target object
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }

    /**
     * write action to db
     * overwrite for filling additional db fields
     * @return int
     */
    public function save() : int
    {
        if ($this->getActionId()) {
            return 0;
        }

        $this->setActionId((int) $this->db->nextId('didactic_tpl_a'));
        $query = 'INSERT INTO didactic_tpl_a (id, tpl_id, type_id) ' .
            'VALUES( ' .
            $this->db->quote($this->getActionId(), 'integer') . ', ' .
            $this->db->quote($this->getTemplateId(), 'integer') . ', ' .
            $this->db->quote($this->getType(), 'integer') .
            ')';
        $this->db->manipulate($query);
        return $this->getActionId();
    }

    /**
     * Delete didactic template action
     * overwrite for filling additional db fields
     */
    public function delete() : void
    {
        $query = 'DELETE FROM didactic_tpl_a ' .
            'WHERE id = ' . $this->db->quote($this->getActionId(), 'integer');
        $this->db->manipulate($query);
    }

    public function read() : void
    {
        $query = 'SELECT * FROM didactic_tpl_a ' .
            'WHERE id = ' . $this->db->quote($this->getActionId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setTemplateId((int) $row->tpl_id);
        }
    }

    /**
     * Get type of template
     * @return int $type
     */
    abstract public function getType() : int;

    /**
     * Apply action
     * @return bool
     */
    abstract public function apply():bool;

    /**
     * Implement everthing that is necessary to revert a didactic template
     *
     * return bool
     */
    abstract public function revert() : bool;


    /**
     * Clone method
     */
    public function __clone()
    {
        $this->setActionId(0);
    }


    /**
     * Write xml for export
     */
    abstract public function toXml(ilXmlWriter $writer) : void;


    /**
     * Init the source object
     * @return ilObject $obj
     */
    protected function initSourceObject():ilObject
    {
        $s = ilObjectFactory::getInstanceByRefId($this->getRefId(), false);
        return $s;
    }

    /**
     * Filter roles
     * @param ilObject $object
     */
    protected function filterRoles(ilObject $source) : array
    {
        $patterns = ilDidacticTemplateFilterPatternFactory::lookupPatternsByParentId(
            $this->getActionId(),
            self::PATTERN_PARENT_TYPE
        );

        $filtered = array();
        foreach ($this->rbacreview->getParentRoleIds($source->getRefId()) as $role_id => $role) {
            switch ($this->getFilterType()) {
                case self::FILTER_PARENT_ROLES:
                    
                    $this->logger->dump($role);
                    if (
                        ($role['parent'] == $source->getRefId()) &&
                        ($role['assign'] == 'y')
                    ) {
                        $this->logger->debug('Excluding local role: ' . $role['title']);
                        break;
                    }
                    foreach ($patterns as $pattern) {
                        if ($pattern->valid(ilObject::_lookupTitle($role_id))) {
                            $this->logger->debug('Role is valid: ' . ilObject::_lookupTitle($role_id));
                            $filtered[$role_id] = $role;
                        }
                    }
                    break;

                case self::FILTER_LOCAL_ROLES:

                    if (
                        $role['parent'] != $source->getRefId() ||
                        $role['assign'] == 'n'
                    ) {
                        $this->logger->debug('Excluding non local role' . $role['title']);
                        break;
                    }
                    foreach ($patterns as $pattern) {
                        if ($pattern->valid(\ilObject::_lookupTitle($role_id))) {
                            $this->logger->debug('Role is valid ' . \ilObject::_lookupTitle($role_id));
                            $filtered[$role_id] = $role;
                        }
                    }
                    break;
                
                default:
                case self::FILTER_SOURCE_TITLE:
                    foreach ($patterns as $pattern) {
                        if ($pattern->valid(ilObject::_lookupTitle($role_id))) {
                            $this->logger->debug('Role is valid: ' . ilObject::_lookupTitle($role_id));
                            $filtered[$role_id] = $role;
                        }
                    }
                    break;
            }
        }
        return $filtered;
    }
}
