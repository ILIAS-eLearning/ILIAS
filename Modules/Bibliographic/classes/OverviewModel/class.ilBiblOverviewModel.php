<?php
/**
 * Class ilBiblOverviewModel
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblOverviewModel extends ActiveRecord implements ilBiblOverviewModelInterface {
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 * @con_is_notnull true
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 */
	protected $ovm_id;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 */
	protected $file_type;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     32
	 */
	protected $literature_type;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     512
	 */
	protected $pattern;

	/**
	 * @return mixed
	 */
	public function getOvmId() {
		return $this->ovm_id;
	}


	/**
	 * @param mixed $ovm_id
	 */
	public function setOvmId($ovm_id) {
		$this->ovm_id = $ovm_id;
	}


	/**
	 * @return mixed
	 */
	public function getFileType() {
		return $this->file_type;
	}


	/**
	 * @param mixed $file_type
	 */
	public function setFileType($file_type) {
		$this->file_type = $file_type;
	}


	/**
	 * @return mixed
	 */
	public function getLiteratureType() {
		return $this->literature_type;
	}


	/**
	 * @param mixed $literature_type
	 */
	public function setLiteratureType($literature_type) {
		$this->literature_type = $literature_type;
	}


	/**
	 * @return mixed
	 */
	public function getPattern() {
		return $this->pattern;
	}


	/**
	 * @param mixed $pattern
	 */
	public function setPattern($pattern) {
		$this->pattern = $pattern;
	}

}