<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilEctsGradesEnabled
 * @author Michael Jansen <mjansen@databay.de>
 * @package ModulesTest
 */
interface ilEctsGradesEnabled
{
	/**
	 * @param array $grades
	 */
	public function setECTSGrades(array $grades);

	/**
	 * @return array
	 */
	public function getECTSGrades();

	/**
	 * @param float|null $ects_fx
	 */
	public function setECTSFX($ects_fx);

	/**
	 * @return float|null
	 */
	public function getECTSFX();

	/**
	 * @param int|bool $status
	 */
	public function setECTSOutput($status);

	/**
	 * @return int|bool
	 */
	public function getECTSOutput();

	/**
	 *
	 */
	public function saveECTSStatus();

	/**
	 * @return boolean
	 */
	public function canEditEctsGrades();

	/**
	 * Returns the ECTS grade for a number of reached points
	 * @param array  $passed_array   An array with the points of all users who passed the test
	 * @param double $reached_points The points reached in the test
	 * @param double $max_points     The maximum number of points for the test
	 * @return string The ECTS grade short description
	 */
	public function getECTSGrade($passed_array, $reached_points, $max_points);

	/**
	 * Returns the ECTS grade for a number of reached points
	 * @param double $reached_points The points reached in the test
	 * @param double $max_points     The maximum number of points for the test
	 * @return string The ECTS grade short description
	 */
	public static function _getECTSGrade($points_passed, $reached_points, $max_points, $a, $b, $c, $d, $e, $fx);
} 