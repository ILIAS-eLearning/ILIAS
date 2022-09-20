<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * represents a creation of local roles action
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplates
 */
class ilDidacticTemplateLocalRoleAction extends ilDidacticTemplateAction
{
    private int $role_template_id = 0;

    public function __construct(int $a_action_id = 0)
    {
        parent::__construct($a_action_id);
    }

    public function getType(): int
    {
        return self::TYPE_LOCAL_ROLE;
    }

    public function setRoleTemplateId(int $a_role_template_id): void
    {
        $this->role_template_id = $a_role_template_id;
    }

    public function getRoleTemplateId(): int
    {
        return $this->role_template_id;
    }

    public function apply(): bool
    {
        $source = $this->initSourceObject();

        $role = new ilObjRole();
        $role->setTitle(ilObject::_lookupTitle($this->getRoleTemplateId()) . '_' . $this->getRefId());
        $role->setDescription(ilObject::_lookupDescription($this->getRoleTemplateId()));
        $role->create();
        $this->admin->assignRoleToFolder($role->getId(), $source->getRefId(), "y");
        $this->logger->info(
            'Using rolt: ' .
            $this->getRoleTemplateId() .
            ' with title "' .
            ilObject::_lookupTitle($this->getRoleTemplateId()) .
            '". '
        );

        // Copy template permissions

        $this->logger->debug(
            'Copy role template permissions ' .
            'tpl_id: ' . $this->getRoleTemplateId() . ' ' .
            'parent: ' . ROLE_FOLDER_ID . ' ' .
            'role_id: ' . $role->getId() . ' ' .
            'role_parent: ' . $source->getRefId()
        );

        $this->admin->copyRoleTemplatePermissions(
            $this->getRoleTemplateId(),
            ROLE_FOLDER_ID,
            $source->getRefId(),
            $role->getId(),
            true
        );
        // Set permissions
        $ops = $this->review->getOperationsOfRole($role->getId(), $source->getType(), $source->getRefId());
        $this->admin->grantPermission($role->getId(), $ops, $source->getRefId());

        return true;
    }

    public function revert(): bool
    {
        // @todo: revert could delete the generated local role. But on the other hand all users
        // assigned to this local role would be deassigned. E.g. if course or group membership
        // is handled by didactic templates, all members would get lost.
        return false;
    }

    public function save(): int
    {
        if (!parent::save()) {
            return 0;
        }

        $query = 'INSERT INTO didactic_tpl_alr (action_id,role_template_id) ' .
            'VALUES ( ' .
            $this->db->quote($this->getActionId(), 'integer') . ', ' .
            $this->db->quote($this->getRoleTemplateId(), 'integer') . ' ' .
            ') ';
        $res = $this->db->manipulate($query);

        return $this->getActionId();
    }

    public function delete(): void
    {
        parent::delete();

        $query = 'DELETE FROM didactic_tpl_alr ' .
            'WHERE action_id = ' . $this->db->quote($this->getActionId(), 'integer');
        $this->db->manipulate($query);
    }

    public function toXml(ilXmlWriter $writer): void
    {
        $writer->xmlStartTag('localRoleAction');

        $il_id = 'il_' . IL_INST_ID . '_' . ilObject::_lookupType($this->getRoleTemplateId()) . '_' . $this->getRoleTemplateId();

        $writer->xmlStartTag(
            'roleTemplate',
            [
                'id' => $il_id
            ]
        );

        $exp = new ilRoleXmlExport();
        $exp->setMode(ilRoleXmlExport::MODE_DTPL);
        $exp->addRole($this->getRoleTemplateId(), ROLE_FOLDER_ID);
        $exp->write();
        $writer->appendXML($exp->xmlDumpMem(false));
        $writer->xmlEndTag('roleTemplate');
        $writer->xmlEndTag('localRoleAction');
    }

    public function read(): void
    {
        parent::read();
        $query = 'SELECT * FROM didactic_tpl_alr ' .
            'WHERE action_id = ' . $this->db->quote($this->getActionId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setRoleTemplateId((int) $row->role_template_id);
        }
    }
}
