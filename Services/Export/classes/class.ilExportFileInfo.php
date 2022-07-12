<?php declare(strict_types=1);
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

/**
 * @classDescription Stores information of creation date and versions of export files
 * @author           Stefan Meyer <meyer@leifos.com>
 * @ingroup          ServicesExport
 */
class ilExportFileInfo
{
    protected const CURRENT_VERSION = "4.1.0";

    private int $obj_id = 0;
    private string $version = self::CURRENT_VERSION;
    private string $export_type = '';
    private string $file_name = '';
    private ?ilDateTime $create_date = null;

    protected ilDBInterface $db;

    public function __construct(int $a_obj_id, string $a_export_type = '', string $a_filename = '')
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->obj_id = $a_obj_id;
        $this->export_type = $a_export_type;
        $this->file_name = $a_filename;
        if ($this->getObjId() and $this->getExportType() and $this->getFilename()) {
            $this->read();
        }
    }

    /**
     * Lookup last export
     */
    public static function lookupLastExport(int $a_obj_id, string $a_type, string $a_version = '') : ?ilExportFileInfo
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM export_file_info " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . ' ' .
            "AND export_type = " . $ilDB->quote($a_type, 'text') . ' ' .
            "ORDER BY create_date DESC";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!$a_version || $row->version == $a_version) {
                return new ilExportFileInfo((int) $row->obj_id, $row->export_type, $row->filename);
            }
        }
        return null;
    }

    public static function deleteByObjId($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilDB->manipulate("DELETE FROM export_file_info WHERE obj_id = " . $ilDB->quote(
            $a_obj_id,
            ilDBConstants::T_INTEGER
        ));
        return true;
    }

    public function setExportType(string $a_type) : void
    {
        $this->export_type = $a_type;
    }

    public function getExportType() : string
    {
        return $this->export_type;
    }

    public function setFilename(string $a_name) : void
    {
        $this->file_name = $a_name;
    }

    public function getFilename() : string
    {
        return $this->file_name;
    }

    public function getBasename(string $a_ext = '.zip') : string
    {
        return basename($this->getFilename(), $a_ext);
    }

    public function setObjId(int $a_id) : void
    {
        $this->obj_id = $a_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function setVersion(string $a_version) : void
    {
        $this->version = $a_version;
    }

    public function getVersion() : string
    {
        return $this->version;
    }

    public function getCreationDate() : ilDateTime
    {
        return $this->create_date instanceof ilDateTime ? $this->create_date : new ilDateTime(time(), IL_CAL_UNIX);
    }

    public function setCreationDate(?ilDateTime $dt = null)
    {
        $this->create_date = $dt;
    }

    public function create() : void
    {
        $exists_query = 'select * from export_file_info ' .
            'where obj_id = ' . $this->db->quote($this->obj_id, 'integer') . ' ' .
            'and export_type = ' . $this->db->quote($this->getExportType(), 'text') . ' ' .
            'and filename = ' . $this->db->quote($this->getFilename(), 'text');
        $exists_res = $this->db->query($exists_query);

        if (!$exists_res->numRows()) {
            $query = "INSERT INTO export_file_info (obj_id, export_type, filename, version, create_date) " .
                "VALUES ( " .
                $this->db->quote($this->getObjId(), 'integer') . ', ' .
                $this->db->quote($this->getExportType(), 'text') . ', ' .
                $this->db->quote($this->getFilename(), 'text') . ', ' .
                $this->db->quote($this->getVersion(), 'text') . ', ' .
                $this->db->quote(
                    $this->getCreationDate()->get(IL_CAL_DATETIME, '', ilTimeZone::UTC),
                    'timestamp'
                ) . ' ' .
                ")";
            $this->db->manipulate($query);
        }
    }

    public function delete() : void
    {
        $this->db->manipulate(
            'DELETE FROM export_file_info ' .
            'WHERE obj_id = ' . $this->db->quote($this->getObjId(), 'integer') . ' ' .
            'AND filename = ' . $this->db->quote($this->getFilename(), 'text')
        );
    }

    protected function read() : void
    {
        $query = "SELECT * FROM export_file_info " .
            "WHERE obj_id = " . $this->db->quote($this->getObjId(), 'integer') . ' ' .
            "AND export_type = " . $this->db->quote($this->getExportType(), 'text') . ' ' .
            "AND filename = " . $this->db->quote($this->getFilename(), 'text');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setVersion($row->version);
            $this->setCreationDate(new ilDateTime($row->create_date, IL_CAL_DATETIME, ilTimeZone::UTC));
        }
    }
}
