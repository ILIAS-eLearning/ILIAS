<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
* Remote course app class
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup components\ILIASRemoteCourse
*/

class ilObjRemoteCourse extends ilRemoteObjectBase
{
    public const DB_TABLE_NAME = "remote_course_settings";

    public const ACTIVATION_OFFLINE = 0;
    public const ACTIVATION_UNLIMITED = 1;
    public const ACTIVATION_LIMITED = 2;

    private int $availability_type = 0;
    private int $end = 0;
    private int $start = 0;

    public function initType(): void
    {
        $this->type = "rcrs";
    }

    protected function getTableName(): string
    {
        return self::DB_TABLE_NAME;
    }

    protected function getECSObjectType(): string
    {
        return "/campusconnect/courselinks";
    }

    /**
     * Set Availability type
     *
     * @param $a_type availability type
     */
    public function setAvailabilityType(int $a_type): void
    {
        $this->availability_type = (int) $a_type;
    }

    /**
     * get availability type
     */
    public function getAvailabilityType(): int
    {
        return $this->availability_type;
    }

    /**
     * set starting time
     *
     * @param $a_time starting time
     */
    public function setStartingTime(int $a_time): void
    {
        $this->start = $a_time;
    }

    /**
     * get starting time
     */
    public function getStartingTime(): int
    {
        return $this->start;
    }

    /**
     * set ending time
     *
     * @param $a_time ending time
     */
    public function setEndingTime(int $a_time): void
    {
        $this->end = $a_time;
    }

    /**
     * get ending time
     */
    public function getEndingTime(): int
    {
        return $this->end;
    }

    /**
     * Lookup online
     */
    public static function _lookupOnline(int $a_obj_id): bool
    {
        global $ilDB;

        $query = "SELECT * FROM " . self::DB_TABLE_NAME .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        switch ($row->availability_type) {
            case self::ACTIVATION_UNLIMITED:
                return true;
            case self::ACTIVATION_LIMITED:
                return time() > $row->r_start && time() < $row->r_end;
            case self::ACTIVATION_OFFLINE:
            default:
                return false;
        }

        return false;
    }

    protected function doCreateCustomFields(array &$a_fields): void
    {
        $a_fields["availability_type"] = array("integer", 0);
        $a_fields["r_start"] = array("integer", 0);
        $a_fields["r_end"] = array("integer", 0);
    }

    protected function doUpdateCustomFields(array &$a_fields): void
    {
        $a_fields["availability_type"] = array("integer", $this->getAvailabilityType());
        $a_fields["r_start"] = array("integer", $this->getStartingTime());
        $a_fields["r_end"] = array("integer", $this->getEndingTime());
    }

    protected function doReadCustomFields($a_row): void
    {
        $this->setAvailabilityType((int) $a_row->availability_type);
        $this->setStartingTime((int) $a_row->r_start);
        $this->setEndingTime((int) $a_row->r_end);
    }

    protected function updateCustomFromECSContent(ilECSSetting $a_server, $ecs_content): void
    {
        // add custom values
        $this->setAvailabilityType($ecs_content->status === 'online' ? self::ACTIVATION_UNLIMITED : self::ACTIVATION_OFFLINE);

        // :TODO: ACTIVATION_LIMITED is currently not supported in ECS yet

        // adv. metadata
        $definition = ilECSUtils::getEContentDefinition($this->getECSObjectType());
        $this->importMetadataFromJson(
            $ecs_content,
            $a_server,
            $definition,
            ilECSDataMappingSetting::MAPPING_IMPORT_RCRS
        );

        $import = new ilECSImport($a_server->getServerId(), $this->getId());
        $import->setContentId($ecs_content->courseID);
        $import->save();
    }
}
