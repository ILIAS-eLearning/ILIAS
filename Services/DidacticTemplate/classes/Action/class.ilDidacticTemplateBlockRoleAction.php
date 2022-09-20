<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of ilDidacticTemplateBlockRoleAction
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateBlockRoleAction extends ilDidacticTemplateAction
{
    /** @var ilDidacticTemplateFilterPattern[] */
    private array $pattern = [];
    private int $filter_type = self::FILTER_SOURCE_TITLE;

    public function __construct(int $action_id = 0)
    {
        parent::__construct($action_id);
    }

    public function addFilterPattern(ilDidacticTemplateFilterPattern $pattern): void
    {
        $this->pattern[] = $pattern;
    }

    /**
     * Set filter patterns
     * @param ilDidacticTemplateFilterPattern[] $patterns
     */
    public function setFilterPatterns(array $patterns): void
    {
        $this->pattern = $patterns;
    }

    /**
     * Get filter patterns
     * @return ilDidacticTemplateFilterPattern[]
     */
    public function getFilterPattern(): array
    {
        return $this->pattern;
    }

    public function setFilterType(int $a_type): void
    {
        $this->filter_type = $a_type;
    }

    public function getFilterType(): int
    {
        return $this->filter_type;
    }

    public function save(): int
    {
        parent::save();
        $query = 'INSERT INTO didactic_tpl_abr (action_id,filter_type) ' .
            'VALUES( ' .
            $this->db->quote($this->getActionId(), \ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getFilterType(), \ilDBConstants::T_INTEGER) . ' ' .
            ')';
        $this->db->manipulate($query);

        foreach ($this->getFilterPattern() as $pattern) {
            /* @var ilDidacticTemplateFilterPattern $pattern */
            $pattern->setParentId($this->getActionId());
            $pattern->setParentType(self::PATTERN_PARENT_TYPE);
            $pattern->save();
        }

        return $this->getActionId();
    }

    public function delete(): void
    {
        parent::delete();
        $query = 'DELETE FROM didactic_tpl_abr ' .
            'WHERE action_id  = ' . $this->db->quote($this->getActionId(), 'integer');
        $this->db->manipulate($query);

        foreach ($this->getFilterPattern() as $pattern) {
            $pattern->delete();
        }
    }

    public function apply(): bool
    {
        $source = $this->initSourceObject();
        $roles = $this->filterRoles($source);

        // Create local policy for filtered roles
        foreach ($roles as $role_id => $role) {
            $this->blockRole($role_id, $source);
        }

        return true;
    }

    protected function blockRole(int $a_role_id, ilObject $source): bool
    {
        // Set assign to 'y' only if it is a local role
        $assign = $this->review->isAssignable($a_role_id, $source->getRefId()) ? 'y' : 'n';

        // Delete permissions
        $this->admin->revokeSubtreePermissions($source->getRefId(), $a_role_id);

        // Delete template permissions
        $this->admin->deleteSubtreeTemplates($source->getRefId(), $a_role_id);

        $this->admin->assignRoleToFolder(
            $a_role_id,
            $source->getRefId(),
            $assign
        );

        return true;
    }

    public function revert(): bool
    {
        $source = $this->initSourceObject();
        $roles = $this->filterRoles($source);

        // Create local policy for filtered roles
        foreach ($roles as $role_id => $role) {
            $this->deleteLocalPolicy($role_id, $source);
        }

        return true;
    }

    protected function deleteLocalPolicy(int $a_role_id, ilObject $source): bool
    {
        // Create role folder if it does not exist
        //$rolf = $rbacreview->getRoleFolderIdOfObject($source->getRefId());

        if ($this->review->getRoleFolderOfRole($a_role_id) === $source->getRefId()) {
            $this->logger->debug('Ignoring local role: ' . ilObject::_lookupTitle($a_role_id));
            return false;
        }

        $this->admin->deleteLocalRole($a_role_id, $source->getRefId());

        // Change existing object
        $role = new ilObjRole($a_role_id);
        $role->changeExistingObjects(
            $source->getRefId(),
            ilObjRole::MODE_UNPROTECTED_DELETE_LOCAL_POLICIES,
            ['all']
        );
        return true;
    }

    public function getType(): int
    {
        return self::TYPE_BLOCK_ROLE;
    }

    public function toXml(ilXmlWriter $writer): void
    {
        $writer->xmlStartTag('blockRoleAction');

        switch ($this->getFilterType()) {
            case self::FILTER_SOURCE_OBJ_ID:
                $writer->xmlStartTag('roleFilter', ['source' => 'objId']);
                break;

            case self::FILTER_PARENT_ROLES:
                $writer->xmlStartTag('roleFilter', ['source' => 'parentRoles']);
                break;

            case self::FILTER_SOURCE_TITLE:
            default:
                $writer->xmlStartTag('roleFilter', ['source' => 'title']);
                break;
        }

        foreach ($this->getFilterPattern() as $pattern) {
            $pattern->toXml($writer);
        }
        $writer->xmlEndTag('roleFilter');
        $writer->xmlEndTag('blockRoleAction');
    }

    public function __clone()
    {
        parent::__clone();

        $clones = [];
        foreach ($this->getFilterPattern() as $pattern) {
            $clones[] = clone $pattern;
        }
        $this->setFilterPatterns($clones);
    }

    public function read(): void
    {
        parent::read();
        $query = 'SELECT * FROM didactic_tpl_abr ' .
            'WHERE action_id = ' . $this->db->quote($this->getActionId(), ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setFilterType((int) $row->filter_type);
        }

        // Read filter
        foreach (ilDidacticTemplateFilterPatternFactory::lookupPatternsByParentId(
            $this->getActionId(),
            self::PATTERN_PARENT_TYPE
        ) as $pattern) {
            $this->addFilterPattern($pattern);
        }
    }
}
