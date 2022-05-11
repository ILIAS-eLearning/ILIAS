<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for didactic template actions
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilDidacticTemplateActionFactory
{
    public static function factoryByType(int $a_action_type) : ilDidacticTemplateAction
    {
        switch ($a_action_type) {
            case ilDidacticTemplateAction::TYPE_LOCAL_POLICY:
                return new ilDidacticTemplateLocalPolicyAction();

            case ilDidacticTemplateAction::TYPE_LOCAL_ROLE:
                return new ilDidacticTemplateLocalRoleAction();

            case ilDidacticTemplateAction::TYPE_BLOCK_ROLE:
                return new ilDidacticTemplateBlockRoleAction();

            default:
                throw new InvalidArgumentException('Unknown action type given: ' . $a_action_type);
        }
    }

    public static function factoryByTypeAndId(int $a_action_id, int $a_action_type) : ilDidacticTemplateAction
    {
        switch ($a_action_type) {
            case ilDidacticTemplateAction::TYPE_LOCAL_POLICY:
                return new ilDidacticTemplateLocalPolicyAction($a_action_id);

            case ilDidacticTemplateAction::TYPE_LOCAL_ROLE:
                return new ilDidacticTemplateLocalRoleAction($a_action_id);

            case ilDidacticTemplateAction::TYPE_BLOCK_ROLE:
                return new ilDidacticTemplateBlockRoleAction($a_action_id);

            default:
                throw new InvalidArgumentException('Unknown action type given: ' . $a_action_type);
        }
    }

    /**
     * Get actions of one template
     * @param int $a_tpl_id
     * @return ilDidacticTemplateAction[]
     */
    public static function getActionsByTemplateId(int $a_tpl_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT id, type_id FROM didactic_tpl_a ' .
            'WHERE tpl_id = ' . $ilDB->quote($a_tpl_id, \ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);

        $actions = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $actions[] = self::factoryByTypeAndId((int) $row->id, (int) $row->type_id);
        }
        return $actions;
    }
}
