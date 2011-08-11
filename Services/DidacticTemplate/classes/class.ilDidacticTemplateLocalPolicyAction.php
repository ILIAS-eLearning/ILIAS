<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateAction.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplates
 */
class ilDidacticTemplateLocalPolicyAction extends ilDidacticTemplateAction
{
	const TPL_ACTION_OVERWRITE = 1;
	const TPL_ACTION_INTERSECT = 2;
	const TPL_ACTION_ADD = 3;
	const TPL_ACTION_SUBTRACT = 4;
	const TPL_ACTION_UNION = 5;

	const FILTER_SOURCE_TITLE = 1;
	const FILTER_SOURCE_OBJ_ID = 2;

	const PATTERN_PARENT_TYPE = 'action';

	private $pattern = array();
	private $filter_type = self::FILTER_SOURCE_TITLE;
	private $role_template_type = self::TPL_ACTION_OVERWRITE;
	private $role_template_id = 0;


	/**
	 * Constructor
	 * @param int $action_id 
	 */
	public function  __construct($action_id = 0)
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
	public function setFilterPatterns(Array $patterns)
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
	 * Set Role template type
	 * @param int $a_tpl_type
	 */
	public function setRoleTemplateType($a_tpl_type)
	{
		$this->role_template_type = $a_tpl_type;
	}

	/**
	 * Get role template type
	 */
	public function getRoleTemplateType()
	{
		return $this->role_template_type;
	}

	/**
	 * Set role template id
	 * @param int $a_id
	 */
	public function setRoleTemplateId($a_id)
	{
		$this->role_template_id = $a_id;
	}

	/**
	 * Get role template id
	 * @return int
	 */
	public function getRoleTemplateId()
	{
		return $this->role_template_id;
	}

	/**
	 * Save action
	 */
	public function save()
	{
		global $ilDB;

		parent::save();

		$query = 'INSERT INTO didactic_tpl_alp (action_id,filter_type,template_type,template_id) '.
			'VALUES( '.
			$ilDB->quote($this->getActionId(),'integer').', '.
			$ilDB->quote($this->getFilterType(),'integer').', '.
			$ilDB->quote($this->getRoleTemplateType(),'integer').', '.
			$ilDB->quote($this->getRoleTemplateId(),'integer').' '.
			')';
		$ilDB->manipulate($query);

		foreach($this->getFilterPattern() as $pattern)
		{
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
		global $ilDB;

		parent::delete();

		$query = 'DELETE FROM didactic_tpl_alp '.
			'WHERE action_id  = '.$ilDB->quote($this->getActionId(),'integer');
		$ilDB->manipulate($query);

		foreach($this->getFilterPattern() as $pattern)
		{
			$pattern->delete();
		}
		return true;
	}




	/**
	 * Apply action
	 */
	public function  apply()
	{
		;
	}

	/**
	 * Revert action
	 */
	public function  revert()
	{
		;
	}

	/**
	 * Get action type
	 * @return int
	 */
	public function getType()
	{
		return self::TYPE_LOCAL_POLICY;
	}

	/**
	 * Export to xml
	 * @param ilXmlWriter $writer
	 * @return void
	 */
	public function  toXml(ilXmlWriter $writer)
	{
		$writer->xmlStartTag('localPolicyAction');

		switch($this->getFilterType())
		{
			case self::FILTER_SOURCE_TITLE:
				$writer->xmlStartTag('roleFilter',array('source' => 'title'));
				break;

			case self::FILTER_SOURCE_OBJ_ID:
				$writer->xmlStartTag('roleFilter',array('source' => 'objId'));
				break;

		}

		foreach($this->getFilterPattern() as $pattern)
		{
			$pattern->toXml($writer);
		}
		$writer->xmlEndTag('roleFilter');

		switch($this->getRoleTemplateType())
		{
			case self::TPL_ACTION_OVERWRITE:
				$writer->xmlElement(
					'localPolicyTemplate',
					array(
						'type'	=> 'overwrite',
						'id'	=> $this->getRoleTemplateId()
					)
				);
				break;

			case self::TPL_ACTION_INTERSECT:
				$writer->xmlElement(
					'localPolicyTemplate',
					array(
						'type'	=> 'intersect',
						'id'	=> $this->getRoleTemplateId()
					)
				);
				break;

			case self::TPL_ACTION_UNION:
				$writer->xmlElement(
					'localPolicyTemplate',
					array(
						'type'	=> 'union',
						'id'	=> $this->getRoleTemplateId()
					)
				);
				break;
		}


		$writer->xmlEndTag('localPolicyAction');
		return void;
	}

	/**
	 *  clone method
	 */
	public function  __clone()
	{
		parent::__clone();

		// Clone patterns
		$cloned = array();
		foreach($this->getFilterPattern() as $pattern)
		{
			$clones[] = clone $pattern;
		}
		$this->setFilterPatterns($clones);
	}

	public function read()
	{
		global $ilDB;

		if(!parent::read())
		{
			return false;
		}

		$query = 'SELECT * FROM didactic_tpl_alp '.
			'WHERE action_id = '.$ilDB->quote($this->getActionId());
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setFilterType($row->filter_type);
			$this->setRoleTemplateType($row->template_type);
			$this->setRoleTemplateId($row->template_id);
		}

		// Read filter
		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateFilterPatternFactory.php';
		foreach(ilDidacticTemplateFilterPatternFactory::lookupPatternsByParentId($this->getActionId(),self::PATTERN_PARENT_TYPE) as $pattern)
		{
			$this->addFilterPattern($pattern);
		}
	}
}
?>
