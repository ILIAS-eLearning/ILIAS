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

	const PATTERN_PARENT_TYPE = 'action';


	private $pattern = array();
	private $filter_type = self::FILTER_SOURCE_TITLE;

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
	 * Save action
	 */
	public function save()
	{
		global $ilDB;

		parent::save();

		$query = 'INSERT INTO didactic_tpl_abr (action_id,filter_type) '.
			'VALUES( '.
			$ilDB->quote($this->getActionId(),'integer').', '.
			$ilDB->quote($this->getFilterType(),'integer').' '.
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

		$query = 'DELETE FROM didactic_tpl_abr '.
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
		$source = $this->initSourceObject();
		return true;
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
		return self::TYPE_BLOCK_ROLE;
	}

	/**
	 * Export to xml
	 * @param ilXmlWriter $writer
	 * @return void
	 */
	public function  toXml(ilXmlWriter $writer)
	{
		$writer->xmlStartTag('blockRoleAction');

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
		$writer->xmlEndTag('blockRoleAction');
		return;
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

	/**
	 * read action data
	 * @global ilDB $ilDB
	 * @return bool
	 */
	public function read()
	{
		global $ilDB;

		if(!parent::read())
		{
			return false;
		}

		$query = 'SELECT * FROM didactic_tpl_abr '.
			'WHERE action_id = '.$ilDB->quote($this->getActionId());
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setFilterType($row->filter_type);
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
