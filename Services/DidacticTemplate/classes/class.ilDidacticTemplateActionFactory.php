<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateAction.php';

/**
 * Factory for didactic template actions
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateActionFactory
{

	/**
	 * Get action class by xml definition type string
	 * @param string $a_action_type
	 * @return ilDidacticTemplateAction
	 */
	public static function factoryByTypeString($a_action_type)
	{
		switch($a_action_type)
		{
			case 'localPolicy':
				include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateLocalPolicyAction.php';
				return new ilDidacticTemplateLocalPolicyAction();

			case 'localRole':
				return new ilDidacticTemplateLocalRoleAction();
		}
	}

	/**
	 * Get action class by type
	 * @param string $a_action_type
	 * @return ilDidacticTemplateAction
	 */
	public static function factoryByType($a_action_type)
	{
		switch($a_action_type)
		{
			case ilDidacticTemplateAction::TYPE_LOCAL_POLICY:
				include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateLocalPolicyAction.php';
				return new ilDidacticTemplateLocalPolicyAction();

			case ilDidacticTemplateAction::TYPE_LOCAL_ROLE:
				return new ilDidacticTemplateLocalRoleAction();

		}
	}
	

	/**
	 * Get instance by id and type
	 * @param int $a_action_id
	 * @param int $a_actions_type
	 * @return ilDidacticTemplateLocalPolicyAction 
	 */
	public static function factoryByTypeAndId($a_action_id,$a_action_type)
	{
		switch($a_action_type)
		{
			case ilDidacticTemplateAction::TYPE_LOCAL_POLICY:
				include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateLocalPolicyAction.php';
				return new ilDidacticTemplateLocalPolicyAction($a_action_id);

			case ilDidacticTemplateAction::TYPE_LOCAL_ROLE:
				include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateLocalRoleAction.php';
				return new ilDidacticTemplateLocalRoleAction($a_action_id);

		}
		
	}


	/**
	 * Get actions of one template
	 * @param int $a_tpl_id
	 */
	public static function getActionsByTemplateId($a_tpl_id)
	{
		global $ilDB;

		$query = 'SELECT id, type_id FROM didactic_tpl_a '.
			'WHERE tpl_id = '.$ilDB->quote($a_tpl_id,'integer');
		$res = $ilDB->query($query);

		$actions = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$actions[] = self::factoryByTypeAndId($row->id, $row->type_id);
		}
		return (array) $actions;
	}
	
}
?>