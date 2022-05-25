<?php declare(strict_types=1);

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class ilCmiXapiLrsTypeList
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 * @package     Module/CmiXapi
 */
class ilCmiXapiLrsTypeList
{
    /**
     * Get basic data array of all types (without field definitions)
     * @param boolean        get extended data ('usages')
     * @param mixed        required availability or null
     * @return    array        array of assoc data arrays
     */
    public static function getTypesData(bool $a_extended = false, ?int $a_availability = null) : array
    {
        global $ilDB;

        $query = "SELECT * FROM " . ilCmiXapiLrsType::getDbTableName();
        if (isset($a_availability)) {
            $query .= " WHERE availability=" . $ilDB->quote($a_availability, 'integer');
        }
        $query .= " ORDER BY title";
        $res = $ilDB->query($query);

        $data = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $row['lrs_type_id'] = $row['type_id']; // indeed it is an lrs-type-id

            if ($a_extended) {
                $row['usages'] = self::countUntrashedUsages((int) $row['lrs_type_id']);
            }
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Count the number of untrashed usages of a type
     */
    public static function countUntrashedUsages(int $a_type_id) : int
    {
        global $ilDB;

        $query = "
			SELECT COUNT(*) untrashed
			FROM cmix_settings s
			INNER JOIN object_reference r
			ON r.obj_id = s.obj_id
			AND r.deleted IS NULL
			WHERE s.lrs_type_id = %s
		";

        $res = $ilDB->queryF($query, ['integer'], [$a_type_id]);
        $row = $ilDB->fetchObject($res);
        return (int) $row->untrashed;
    }

    /**
     * Get array of options for selecting the type
     * @param     int|null  $a_availability required availability or null
     * @return    array     id => title
     */
    public static function getTypeOptions(?int $a_availability = null) : array
    {
        global $ilDB;

        $query = "SELECT * FROM " . ilCmiXapiLrsType::getDbTableName();
        if (isset($a_availability)) {
            $query .= " WHERE availability=" . $ilDB->quote($a_availability, 'integer');
        }
        $res = $ilDB->query($query);

        $options = array();
        while ($row = $ilDB->fetchObject($res)) {
            $options[$row->type_id] = $row->title;
        }
        return $options;
    }

    /**
     * @return array<string, mixed[]>
     */
    public static function getTypesStruct() : array
    {
        return array(
            'type_name' => array('type' => 'text', 'maxlength' => 32)
            ,
            'title' => array('type' => 'text', 'maxlength' => 255)
            ,
            'description' => array('type' => 'text', 'maxlength' => 4000)
            ,
            'availability' => array('type' => 'a_integer', 'maxlength' => 1, 'options' => array(2, 1, 0))
            //AVAILABILITY_CREATE,AVAILABILITY_EXISTING,AVAILABILITY_NONE
            // , 'lrs'				=> array('type'=>'headline')
            ,
            'lrs_type_id' => array('type' => 'a_integer', 'maxlength' => 1, 'options' => array(0))
            ,
            'lrs_endpoint' => array('type' => 'text', 'maxlength' => 64, 'required' => true)
            ,
            'lrs_key' => array('type' => 'text', 'maxlength' => 64, 'required' => true)
            ,
            'lrs_secret' => array('type' => 'text', 'maxlength' => 64, 'required' => true)
            ,
            'external_lrs' => array('type' => 'bool')
            ,
            'privacy_ident' => array('type' => 'a_integer', 'maxlength' => 1, 'options' => array(0, 1, 2, 3))
            ,
            'privacy_name' => array('type' => 'a_integer', 'maxlength' => 1, 'options' => array(0, 1, 2, 3))
            ,
            'privacy_comment_default' => array('type' => 'text', 'maxlength' => 2000)
            ,
            'remarks' => array('type' => 'text', 'maxlength' => 4000)
        );
    }

    public static function getCountTypesForCreate() : int
    {
        global $ilDB;
        $query = "SELECT COUNT(*) counter FROM " . ilCmiXapiLrsType::getDbTableName() . "
					WHERE availability = " . $ilDB->quote(ilCmiXapiLrsType::AVAILABILITY_CREATE, 'integer');
        $res = $ilDB->query($query);
        $row = $ilDB->fetchObject($res);
        return (int) $row->counter;
    }
}
