<?php

// namespace ILIAS\Modules\OrgUnit\Positions;

// use ILIAS\Modules\OrgUnit\Positions\Authorities\Authority;

/**
 * Class ilOrgUnitPosition
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPosition extends \ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return "il_orgu_positions";
	}


	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $id = 0;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     512
	 */
	protected $title = "";
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     4000
	 */
	protected $description = "";
	/**
	 * @var \ilOrgUnitAuthority[]
	 */
	protected $authorities = array();


	public function afterObjectLoad() {
		$this->authorities = ilOrgUnitAuthority::where(array( ilOrgUnitAuthority::POSITION_ID => $this->getId() ))->get();
	}


	/**
	 * @return array
	 */
	public function getAuthoritiesAsArray() {
		$return = array();
		foreach ($this->authorities as $authority) {
			$return[] = $authority->__toArray();
		}

		return $return;
	}


	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getTitle();
	}


	/**
	 * @return array  it's own authorities and also all which use this position
	 */
	public function getDependentAuthorities() {
		$dependent = ilOrgUnitAuthority::where(array( ilOrgUnitAuthority::FIELD_OVER => $this->getId() ))
		                               ->get();

		$arr = $dependent + $this->authorities;

		return (array)$arr;
	}


	/**
	 * This deletes the Position, it's Authorities, dependent Authorities and all User-Assignements!
	 */
	public function deleteWithAllDependencies() {
		foreach ($this->getDependentAuthorities() as $authority) {
			$authority->delete();
		}
		parent::delete();
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return \ilOrgUnitAuthority[]
	 */
	public function getAuthorities() {
		return $this->authorities;
	}


	/**
	 * @param \ilOrgUnitAuthority[] $authorities
	 */
	public function setAuthorities($authorities) {
		$this->authorities = $authorities;
	}
}
