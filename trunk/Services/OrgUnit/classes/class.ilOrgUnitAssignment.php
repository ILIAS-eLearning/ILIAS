<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */



/**
* Organisation Unit Assignment
*
* @author	Bjoern Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup ServicesOrgUnit
*/
class ilOrgUnitAssignment
{
	private $org_unit_id = 0;
	private $user_id = 0;
	
	private $reporting_access	= false;
	private $cc_compl_invit		= false;
	private $cc_compl_not1		= false;
	private $cc_compl_not2		= false;

	public function __construct($org_unit_id, $user_id)
	{
		$this->org_unit_id = (int)$org_unit_id;
		$this->user_id = (int)$user_id;
	}

	public function create()
	{
		global $ilDB;

		$ilDB->insert('org_unit_assignments', array(
			'oa_ou_id'				=> array('integer', (int)$this->org_unit_id),
			'oa_usr_id'				=> array('integer', (int)$this->user_id),
			'oa_reporting_access'	=> array('integer', (int)$this->reporting_access),
			'oa_cc_compl_invit'		=> array('integer', (int)$this->cc_compl_invit),
			'oa_cc_compl_not1'		=> array('integer', (int)$this->cc_compl_not1),
			'oa_cc_compl_not2'		=> array('integer', (int)$this->cc_compl_not2)
		));
	}

	public function update()
	{
		global $ilDB;

		$ilDB->insert('org_unit_assignments',
			array(
				'oa_reporting_access'	=> array('integer', (int)$this->reporting_access),
				'oa_cc_compl_invit'		=> array('integer', (int)$this->cc_compl_invit),
				'oa_cc_compl_not1'		=> array('integer', (int)$this->cc_compl_not1),
				'oa_cc_compl_not2'		=> array('integer', (int)$this->cc_compl_not2)
			),
			array(
				'oa_ou_id'				=> array('integer', (int)$this->org_unit_id),
				'oa_usr_id'				=> array('integer', (int)$this->user_id)
			)
		);
	}

	public function delete()
	{
		global $ilDB;

		$query = "DELETE FROM org_unit_assignments ".
					"WHERE oa_ou_id = %s AND oa_usr_id = %s";

		$ilDB->queryF(
				$query,
				array('integer', 'integer'),
				array($this->org_unit_id, $this->user_id)
		);
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function setReportingAccess($a_reporting_access)
	{
		$this->reporting_access = (bool)$a_reporting_access;
		return $this;
	}

	public function hasReportingAccess()
	{
		return $this->reporting_access;
	}

	public function setCcComplianceInvitation($a_cc_compl_invit)
	{
		$this->cc_compl_invit = (bool)$a_cc_compl_invit;
		return $this;
	}

	public function hasCcComplianceInvitation()
	{
		$this->cc_compl_invit;
	}

	public function setCcComplianceNotify1($a_cc_compl_not1)
	{
		$this->cc_compl_not1 = (bool)$a_cc_compl_not1;
		return $this;
	}

	public function hasCcComplianceNotify1()
	{
		$this->cc_compl_not1;
	}

	public function setCcComplianceNotify2($a_cc_compl_not2)
	{
		$this->cc_compl_not2 = (bool)$a_cc_compl_not2;
		return $this;
	}

	public function hasCcComplianceNotify2()
	{
		$this->cc_compl_not2;
	}
}

?>
