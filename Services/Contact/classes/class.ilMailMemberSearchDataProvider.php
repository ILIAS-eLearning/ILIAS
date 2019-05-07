<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMemberSearchDataProvider
 *
 * @author Nadia Matuschek <nmatuschek@databay.de>
 *
 **/
class ilMailMemberSearchDataProvider
{
	/** @var string */
	protected $type = 'crs';

	/** @var array */
	protected $data = array();

	/** @var null */
	protected $objParticipants = null;

	/** @var ilLanguage */
	protected $lng;

	/** @var ilObjectDataCache */
	protected $dataCache;

	/**
	 * @var array 
	 */
	protected $roleSortWeightMap = [
		'il_crs_a' => 10,
		'il_grp_a' => 10,
		'il_crs_t' => 9,
		'il_crs_m' => 8,
		'il_grp_m' => 8,
	];

	/**
	 * @param \ilParticipants $objParticipants
	 */
	public function __construct($objParticipants)
	{
		global $DIC;

		$this->dataCache = $DIC['ilObjDataCache'];
		$this->objParticipants = $objParticipants;
		$this->type            = $this->objParticipants->getType();
		$this->lng             = $DIC['lng'];

		$this->collectTableData();
	}

	/**
	 * 
	 */
	private function collectTableData()
	{
		$participants = $this->objParticipants->getParticipants();
		foreach ($participants as $user_id) {
			$name = ilObjUser::_lookupName($user_id);
			$login = ilObjUser::_lookupLogin($user_id);

			$publicName = '';
			if (in_array(ilObjUser::_lookupPref($user_id, 'public_profile'), array('g', 'y'))) {
				$publicName = $name['lastname'] . ', ' . $name['firstname'];
			}

			$this->data[$user_id]['user_id'] = $user_id;
			$this->data[$user_id]['login'] = $login;
			$this->data[$user_id]['name'] = $publicName;

			$assignedRoles = $this->objParticipants->getAssignedRoles($user_id);
			shuffle($assignedRoles);
			$this->dataCache->preloadObjectCache($assignedRoles);
			$roleTitles = [];
			foreach ($assignedRoles as $roleId) {
				$title = $this->dataCache->lookupTitle($roleId);
				$roleTitles[] = $title;
			}

			$roleTitles = $this->sortRoles($roleTitles);

			$that = $this;
			$roleTitles = array_map(function($roleTitle) use ($that) {
				return $that->buildRoleTitle($roleTitle);
			}, $roleTitles);

			$this->data[$user_id]['role'] = implode(', ', $roleTitles);
		}
	}

	/**
	 * @param string[] $roleTitles
	 * @return string[]
	 */
	private function sortRoles(array $roleTitles)
	{
		$that = $this;
		usort($roleTitles, function($a, $b) use ($that) {
			$leftPrefixTitle = substr($a, 0, 8); 
			$rightPrefixTitle = substr($b, 0, 8); 

			$leftRating = 0;
			if (isset($that->roleSortWeightMap[$leftPrefixTitle])) {
				$leftRating = $that->roleSortWeightMap[$leftPrefixTitle];
			}

			$rightRating = 0;
			if (isset($that->roleSortWeightMap[$rightPrefixTitle])) {
				$rightRating = $that->roleSortWeightMap[$rightPrefixTitle];
			}

			if ($leftRating > 0 || $rightRating > 0) {
				if ($leftRating !== $rightRating) {
					return $rightRating - $leftRating > 0 ? 1 : -1;
				} else {
					return 0;
				}
			}

			return strcmp($a, $b);
		});

		return $roleTitles;
	}

	/**
	 * @param string $role
	 * @return string
	 */
	private function buildRoleTitle($role)
	{
		require_once 'Services/AccessControl/classes/class.ilObjRole.php';
		return \ilObjRole::_getTranslation($role);
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
}