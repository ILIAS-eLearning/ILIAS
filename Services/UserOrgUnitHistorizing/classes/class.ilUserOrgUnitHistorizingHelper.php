<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserHistorizingHelper
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 */

require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");


class ilUserOrgUnitHistorizingHelper 
{
	/** @var int $variant Used to control predictable nonsense hash. Change to get alternative data for historizing */
	protected static $variant = 1;
	protected $role_utils;
	#region Singleton


	private function __construct() {
		$this->role_utils = gevRoleUtils::getInstance();
	}

	/** @var ilUserOrgUnitHistorizingHelper $instance */
	private static $instance;

	/**
	 * Singleton accessor
	 * 
	 * @static
	 * 
	 * @return ilUserOrgUnitHistorizingHelper
	 */
	public static function getInstance()
	{
		if(!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function usersHavingRole ($a_id) {
		return $this->role_utils->usersHavingRoleId($a_id);
	}

	public function getOrguOfRole($a_role_id) {
		require_once "Services/AccessControl/classes/class.ilRbacReview.php";
		require_once "Services/Object/classes/class.ilObject.php";
		require_once "Services/Object/classes/class.ilObjectFactory.php";
		global $ilRbacReview;
		$obj_fac = new ilObjectFactory;
		$obj = $obj_fac->getInstanceByObjId($ilRbacReview->getObjectOfRole($a_role_id));
		if($obj->getType() == 'orgu') {
			return $obj;		
		}
		return false;
	}

	public function getOrgUnitsAboveOf($orgu_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");

		$tree = ilObjOrgUnitTree::_getInstance();

		if(! $orgu_id){
			return array(null, null);
		}		
		$root_ref_id = ilObjOrgUnit::getRootOrgRefId();
		$orgu_refid = gevObjectUtils::getRefId($orgu_id);
		$orgu_1_refid = $tree->getParent($orgu_refid);

		$orgu_2_refid = ($orgu_1_refid == $root_ref_id) ? $orgu_1_refid : $tree->getParent($orgu_1_refid);

		$titles = $tree->getTitles(array($orgu_1_refid, $orgu_2_refid));

		$orgu_1_title = $titles[$orgu_1_refid];
		$orgu_2_title = $titles[$orgu_2_refid];
		
		//better check for level?
		$invalid =  array(
			'System Settings', 
			'__OrgUnitAdministration'
		);

		if(in_array($orgu_1_title, $invalid)){
			$orgu_1_title = null;
		}

		if(in_array($orgu_2_title, $invalid)){
			$orgu_2_title = null;
		}

		return array($orgu_1_title, $orgu_2_title);
	}
}