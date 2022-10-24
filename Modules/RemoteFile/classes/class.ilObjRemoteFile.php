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
* Remote file app class
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ModulesRemoteFile
*/

class ilObjRemoteFile extends ilRemoteObjectBase
{
    public const DB_TABLE_NAME = "rfil_settings";

    protected $version;
    protected $version_tstamp;

    public function initType(): void
    {
        $this->type = "rfil";
    }

    protected function getTableName(): string
    {
        return self::DB_TABLE_NAME;
    }

    protected function getECSObjectType(): string
    {
        return "/campusconnect/files";
    }

    /**
     * Set version
     *
     * @param int $a_version
     */
    public function setVersion($a_version)
    {
        $this->version = (int) $a_version;
    }

    /**
     * get version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version timestamp
     *
     * @param int $a_version
     */
    public function setVersionDateTime($a_tstamp)
    {
        $this->version_tstamp = (int) $a_tstamp;
    }

    /**
     * get version timestamp
     *
     * @return int
     */
    public function getVersionDateTime()
    {
        return $this->version_tstamp;
    }

    protected function doCreateCustomFields(array &$a_fields): void
    {
        $a_fields["version"] = array("integer", 1);
        $a_fields["version_tstamp"] = array("integer", time());
    }

    protected function doUpdateCustomFields(array &$a_fields): void
    {
        $a_fields["version"] = array("integer", $this->getVersion());
        $a_fields["version_tstamp"] = array("integer", $this->getVersionDateTime());
    }

    protected function doReadCustomFields($a_row): void
    {
        $this->setVersion($a_row->version);
        $this->setVersionDateTime($a_row->version_tstamp);
    }

    protected function updateCustomFromECSContent(ilECSSetting $a_server, $ecs_content): void
    {
        $this->setVersion($ecs_content->version);
        $this->setVersionDateTime($ecs_content->version_date);
    }

    /**
     * Get version info
     *
     * used in ilRemoteFileListGUI
     *
     * @param int $a_obj_id
     * @return string
     */
    public static function _lookupVersionInfo($a_obj_id)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT version, version_tstamp" .
            " FROM " . self::DB_TABLE_NAME .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        $res = (int) $row["version"];

        if ($row["version_tstamp"]) {
            $res .= " (" . ilDatePresentation::formatDate(new ilDateTime($row["version_tstamp"], IL_CAL_UNIX)) . ")";
        }

        return $res;
    }
}
