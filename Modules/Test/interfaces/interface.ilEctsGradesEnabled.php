<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilEctsGradesEnabled
 * @author Michael Jansen <mjansen@databay.de>
 * @package ModulesTest
 */
interface ilEctsGradesEnabled
{
    public function setECTSGrades(array $grades) : void;

    public function getECTSGrades() : array;

    /**
     * @param float|null $ects_fx
     */
    public function setECTSFX($ects_fx) : void;

    public function getECTSFX() : ?float;

    /**
     * @param int|bool $status
     */
    public function setECTSOutput($status) : void;

    public function getECTSOutput() : int;

    public function saveECTSStatus() : void;

    public function canEditEctsGrades() : bool;

    public function canShowEctsGrades() : bool;

    /**
     * Returns the ECTS grade for a number of reached points
     * @param array  $passed_array   An array with the points of all users who passed the test
     * @param double $reached_points The points reached in the test
     * @param double $max_points     The maximum number of points for the test
     * @return string The ECTS grade short description
     */
    public function getECTSGrade($passed_array, $reached_points, $max_points) : string;

    /**
     * Returns the ECTS grade for a number of reached points
     * @param double $reached_points The points reached in the test
     * @param double $max_points     The maximum number of points for the test
     * @return string The ECTS grade short description
     */
    public static function _getECTSGrade($points_passed, $reached_points, $max_points, $a, $b, $c, $d, $e, $fx) : string;
}
