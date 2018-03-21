<?php
/**
 * Class ilBiblOverviewModelInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblOverviewModelInterface {

	/**
	 * @return int
	 */
	public function getOvmId();


	/**
	 * @param integer $ovm_id
	 */
	public function setOvmId($ovm_id);


	/**
	 * @return integer
	 */
	public function getFileType();

	/**
	 * @param integer $file_type
	 */
	public function setFileType($file_type);

	/**
	 * @return string
	 */
	public function getLiteratureType();

	/**
	 * @param string $literature_type
	 */
	public function setLiteratureType($literature_type);

	/**
	 * @return string
	 */
	public function getPattern();

	/**
	 * @param string $pattern
	 */
	public function setPattern($pattern);
}