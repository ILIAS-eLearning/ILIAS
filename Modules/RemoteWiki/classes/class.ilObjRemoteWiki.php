<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* Remote wiki app class
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ModulesRemoteWiki
*/

class ilObjRemoteWiki extends ilRemoteObjectBase
{
    public const DB_TABLE_NAME = "rwik_settings";

    public const ACTIVATION_OFFLINE = 0;
    public const ACTIVATION_ONLINE = 1;

    protected $availability_type;

    public function initType(): void
    {
        $this->type = "rwik";
    }

    protected function getTableName(): string
    {
        return self::DB_TABLE_NAME;
    }

    protected function getECSObjectType(): string
    {
        return "/campusconnect/wikis";
    }

    /**
     * Set Availability type
     *
     * @param int $a_type availability type
     */
    public function setAvailabilityType($a_type)
    {
        $this->availability_type = $a_type;
    }

    /**
     * get availability type
     *
     * @return int
     */
    public function getAvailabilityType()
    {
        return $this->availability_type;
    }

    /**
     * Lookup online
     *
     * @param int $a_obj_id obj_id
     * @return bool
     */
    public static function _lookupOnline($a_obj_id)
    {
        global $ilDB;

        $query = "SELECT * FROM " . self::DB_TABLE_NAME .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        switch ($row->availability_type) {
            case self::ACTIVATION_ONLINE:
                return true;

            case self::ACTIVATION_OFFLINE:
                return false;

            default:
                return false;
        }

        return false;
    }

    protected function doCreateCustomFields(array &$a_fields): void
    {
        $a_fields["availability_type"] = array("integer", 0);
    }

    protected function doUpdateCustomFields(array &$a_fields): void
    {
        $a_fields["availability_type"] = array("integer", $this->getAvailabilityType());
    }

    protected function doReadCustomFields($a_row): void
    {
        $this->setAvailabilityType($a_row->availability_type);
    }

    protected function updateCustomFromECSContent(ilECSSetting $a_server, $a_ecs_content): void
    {
        $this->setAvailabilityType($a_ecs_content->availability == 'online' ? self::ACTIVATION_ONLINE : self::ACTIVATION_OFFLINE);
    }
}
