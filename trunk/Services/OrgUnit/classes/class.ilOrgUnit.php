<?php

require_once('Services/OrgUnit/exceptions/class.ilOrgUnitException.php');

class ilOrgUnit
{
	private static $instance_cache = array();
	private static $import_id_cache = array();

	private $id = 0;
	private $title = '';
	private $subtitle = '';
	private $import_id = 0;

	private $parent = 0;
	private $childs = array();

	private $assignment_list = null;

	private function __construct($ou_id = 0)
	{
		if( (int)$ou_id > 0 )
		{
			$this->id = (int)$ou_id;
			$this->read();
		}
	}

	public function initAssigns()
	{
		require_once('Services/OrgUnit/classes/class.ilOrgUnitAssignmentList.php');
		$this->assignment_list = new ilOrgUnitAssignmentList($this->id);
	}

	public function assignUser($a_user_id, $a_reporting_access,
			$a_cc_compl_invit, $a_cc_compl_not1, $a_cc_compl_not2)
	{
		if($this->assignment_list === null)
			throw new ilOrgUnitException('Error: Assignment object not initialised yet!');

		$this->assignment_list->addAssignment($a_user_id, $a_reporting_access,
			$a_cc_compl_invit, $a_cc_compl_not1, $a_cc_compl_not2);
	}

	public function deassignUser($a_user_id)
	{
		if($this->assignment_list === null)
			throw new ilOrgUnitException('Error: Assignment object not initialised yet!');

		$this->assignment_list->removeAssignment($a_user_id);
	}

	public function isUserAssigned($a_user_id)
	{
		if($this->assignment_list === null)
			throw new ilOrgUnitException('Error: Assignment object not initialised yet!');

		return $this->assignment_list->doesAssignmentExist($a_user_id);
	}

	public function getAssignedUsers()
	{
		if($this->assignment_list === null)
			throw new ilOrgUnitException('Error: Assignment object not initialised yet!');

		$assignments = array();
		foreach($this->assignment_list as $assignment)
		{
			$assignments[$assignment->getUserId()] = array(
				'reporting_access'	=> $assignment->hasReportingAccess(),
				'cc_coml_invit'		=> $assignment->hasCcComplianceInvitation(),
				'cc_coml_not1'		=> $assignment->hasCcComplianceNotify1(),
				'cc_coml_not2'		=> $assignment->hasCcComplianceNotify2()
			);
		}

		return $assignments;
	}

	public function read()
	{
		global $ilDB;

		$query = "SELECT * FROM org_unit_data WHERE ou_id = %s";

		$res = $ilDB->queryF($query, array('integer'), array($this->id));

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->id = $row->ou_id;
			$this->title = $row->ou_title;
			$this->subtitle = $row->ou_subtitle;
			$this->import_id = $row->ou_import_id;

			return true;
		}

		throw new ilOrgUnitDoesNotExistException(
			'organisation unit with id "'.$this->id.'" does not exist!'
		);
	}

	public function update()
	{
		global $ilDB;

		$ilDB->update(
			'org_unit_data',
			array(
				'ou_title'		=> array('text', $this->title),
				'ou_subtitle'	=> array('text', $this->subtitle),
				'ou_import_id'	=> array('text', $this->import_id)
			),
			array('ou_id' => array('integer', $this->id))
		);
	}

	public function create()
	{
		global $ilDB;

		$this->id = $ilDB->nextId('org_unit_data');

		$ilDB->insert(
			'org_unit_data',
			array(
				'ou_id'			=> array('integer', $this->id),
				'ou_title'		=> array('text', $this->title),
				'ou_subtitle'	=> array('text', $this->subtitle),
				'ou_import_id'	=> array('text', $this->import_id)
			),
			array('ou_id' => array('integer', $this->id))
		);
	}

	public function delete()
	{
		self::deleteInstance($this->id);
	}

	public static function deleteInstance($ou_id)
	{
		global $ilDB;

		$query = "DELETE FROM org_unit_data WHERE ou_id = %s";

		$ilDB->queryF($query, array('integer'), array($ou_id));

		if( isset(self::$instance_cache[$ou_id]) )
		{
			unset(self::$instance_cache[$ou_id]);
		}
	}

	public static function getInstance($ou_id)
	{
		if( !isset(self::$instance_cache[$ou_id]) )
		{
			self::$instance_cache[$ou_id] = new self($ou_id);
		}

		return self::$instance_cache[$ou_id];
	}

	public static function createInstance($ou_title, $ou_subtitle, $ou_import_id)
	{
		$unit = new self();

		$unit	->setTitle($ou_title)
				->setSubTitle($ou_subtitle)
				->setImportId($ou_import_id)
				->create();

		self::$instance_cache[$unit->getId()] = $unit;

		return $unit;
	}

	public static function lookupIdByImportId($ou_import_id)
	{
		if( isset(self::$import_id_cache[$ou_import_id]) )
		{
			return self::$import_id_cache[$ou_import_id];
		}

		global $ilDB;

		$query = "SELECT ou_id FROM org_unit_data WHERE ou_import_id = %s";

		$res = $ilDB->queryF($query, array('integer'), array($ou_import_id));

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			self::$import_id_cache[$ou_import_id] = $row->ou_id;
			return $row->ou_id;
		}

		return null;
	}

	public static function getInstancesByAssignedUser($a_user_id)
	{
		global $ilDB;

		$query = "SELECT ou_id FROM org_unit_data ".
					"LEFT JOIN org_unit_assignments ".
					"ON ou_id = oa_ou_id ".
					"WHERE oa_usr_id = %s";

		$res = $ilDB->queryF($query, array('integer'), array($a_user_id));

		$units = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$units[$row->ou_id] = self::getInstance($row->ou_id);
		}

		return $units;
	}
	
	public function hasUserReportingAccess($a_user_id)
	{
		if($this->assignment_list === null) $this->initAssigns();

		return $this->assignment_list->hasUserReportingAccess($a_user_id);
	}

	public function setId($ou_id)
	{
		$this->id = (int)$ou_id;
		return $this;
	}
	public function getId()
	{
		return $this->id;
	}
	public function setTitle($ou_title)
	{
		$this->title = $ou_title;
		return $this;
	}
	public function getTitle()
	{
		return $this->title;
	}
	public function setSubTitle($ou_subtitle)
	{
		$this->subtitle = $ou_subtitle;
		return $this;
	}
	public function getSubTitle()
	{
		return $this->subtitle;
	}
	public function setImportId($ou_import_id)
	{
		$this->import_id = (int)$ou_import_id;
		return $this;
	}
	public function getImportId()
	{
		return $this->import_id;
	}

	public function setParent($a_parent)
	{
		$this->parent = (int)$a_parent;
		return $this;
	}
	public function getParent()
	{
		return $this->parent;
	}
	public function addChild($a_child)
	{
		$this->childs[] = $a_child;
		return $this;
	}
	public function getChilds()
	{
		return $this->childs;
	}
	public function hasChilds()
	{
		return (bool)count($this->childs);
	}
	public function sortChilds()
	{
		usort($this->childs, array($this,'sortCallback'));

		foreach($this->childs as $child)
		{
			if( $child->hasChilds() ) $child->sortChilds();
		}
	}
	public function sortCallback($a_a, $a_b)
	{
		return strcmp($a_a->getTitle(), $a_b->getTitle());
	}
}


?>