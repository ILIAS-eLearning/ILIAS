<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('Services/OrgUnit/classes/class.ilOrgUnitAssignment.php');

/**
* Organisation Unit Assignment List
*
* @author	Bjoern Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup ServicesOrgUnit
*/
class ilOrgUnitAssignmentList implements Iterator
{
	private $org_unit_id = 0;

	private $assignments = array();

	public function __construct($org_unit_id)
	{
		$this->org_unit_id = (int)$org_unit_id;

		$this->read();
	}

	private function read()
	{
		global $ilDB;

		$query = "SELECT * FROM org_unit_assignments WHERE oa_ou_id = %s";

		$res = $ilDB->queryF($query, array('integer'), array($this->org_unit_id));

		$this->assignments = array();

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$assignment = new ilOrgUnitAssignment($row->oa_ou_id, $row->oa_usr_id);

			$assignment->setReportingAccess($row->oa_reporting_access);
			$assignment->setCcComplianceInvitation($row->oa_cc_compl_invit);
			$assignment->setCcComplianceNotify1($row->oa_cc_compl_not1);
			$assignment->setCcComplianceNotify2($row->oa_cc_compl_not2);

			$this->assignments[$row->oa_usr_id] = $assignment;
		}

		#usort($this->assignments, array($this, 'sortCallback'));
	}

	public function sortCallback($a_assignment_a, $a_assignment_b)
	{
		$user_a = ilObjectFactory::getInstanceByObjId($a_assignment_a->getUserId());
		$user_b = ilObjectFactory::getInstanceByObjId($a_assignment_b->getUserId());

		$user_name_a = $user_a->getLastName().', '.$user_a->getFirstName();
		$user_name_b = $user_b->getLastName().', '.$user_b->getFirstName();

		return strcmp($user_name_a, $user_name_b);
	}

	public function addAssignment($a_user_id, $a_reporting_access,
				$a_cc_compl_invit, $a_cc_compl_not1, $a_cc_compl_not2)
	{
		if( isset($this->assignments[$user_id]) )
			throw new ilOrgUnitException('Error: User with id "'.$user_id.'" is allready assigned!');

		$assignment = new ilOrgUnitAssignment($this->org_unit_id, $a_user_id);

		$assignment	->setReportingAccess($a_reporting_access)
					->setCcComplianceInvitation($a_cc_compl_invit)
					->setCcComplianceNotify1($a_cc_compl_not1)
					->setCcComplianceNotify2($a_cc_compl_not2)
					->create();

		$this->assignments[$user_id] = $assignment;
	}

	public function removeAssignment($a_user_id)
	{
		if( !isset($this->assignments[$user_id]) )
			throw new ilOrgUnitException('Error: User with id "'.$user_id.'" is not assigned!');

		$this->assignments[$user_id]->delete();

		unset( $this->assignments[$user_id] );
	}

	public function doesAssignmentExist($a_user_id)
	{
		return isset($this->assignments[$a_user_id]);
	}

	public function hasUserReportingAccess($a_user_id)
	{		
		return $this->assignments[$a_user_id]->hasReportingAccess();
	}
	
	public function current()
	{
		return current($this->assignments);
	}

	public function next()
	{
		return next($this->assignments);
	}

	public function key()
	{
		return key($this->assignments);
	}

	public function valid()
	{
		return key($this->assignments) !== null;
	}

	public function rewind()
	{
		return reset($this->assignments);
	}
}

?>
