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

/**
 * Export options
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesExport
 */
class ilExportOptions
{
    private static ?ilExportOptions $instance = null;

    public const EXPORT_EXISTING = 1;
    public const EXPORT_BUILD = 2;
    public const EXPORT_OMIT = 3;

    public const KEY_INIT = 1;
    public const KEY_ITEM_MODE = 2;
    public const KEY_ROOT = 3;

    private int $export_id = 0;
    private array $ref_options = array();
    private array $obj_options = array();
    private array $options = array();

    protected ilDBInterface $db;

    private function __construct(int $a_export_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->export_id = $a_export_id;
        if ($this->export_id) {
            $this->read();
        }
    }

    public static function getInstance() : ?ilExportOptions
    {
        if (self::$instance) {
            return self::$instance;
        }
        return null;
    }

    public static function newInstance(int $a_export_id) : ilExportOptions
    {
        return self::$instance = new ilExportOptions($a_export_id);
    }

    public static function allocateExportId() : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        // get last export id
        $query = 'SELECT MAX(export_id) exp FROM export_options ' .
            'GROUP BY export_id ';
        $res = $ilDB->query($query);
        $exp_id = 1;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $exp_id = $row->exp + 1;
        }
        $query = 'INSERT INTO export_options (export_id,keyword,ref_id,obj_id,value) ' .
            'VALUES( ' .
            $ilDB->quote($exp_id, 'integer') . ', ' .
            $ilDB->quote(self::KEY_INIT, 'integer') . ', ' .
            $ilDB->quote(0, 'integer') . ', ' .
            $ilDB->quote(0, 'integer') . ', ' .
            $ilDB->quote(0, 'integer') . ' ' .
            ')';
        $ilDB->manipulate($query);
        return (int) $exp_id;
    }

    /**
     * Get all subitems with mode <code>ilExportOptions::EXPORT_BUILD</code>
     */
    public function getSubitemsForCreation(int $a_source_id) : array
    {
        $refs = array();
        foreach ((array) $this->ref_options[self::KEY_ITEM_MODE] as $ref_id => $mode) {
            if ($mode == self::EXPORT_BUILD) {
                $refs[] = $ref_id;
            }
        }
        return $refs;
    }

    /**
     * Get all subitems with mode != self::EXPORT_OMIT
     * @return int[] ref ids
     */
    public function getSubitemsForExport()
    {
        $refs = array();
        foreach ((array) $this->ref_options[self::KEY_ITEM_MODE] as $ref_id => $mode) {
            if ($mode != self::EXPORT_OMIT) {
                $refs[] = (int) $ref_id;
            }
        }
        return $refs;
    }

    public function getExportId() : int
    {
        return $this->export_id;
    }

    /**
     * @param int        $a_keyword
     * @param int        $a_ref_id
     * @param int        $a_obj_id
     * @param string|int $a_value
     */
    public function addOption(int $a_keyword, int $a_ref_id, int $a_obj_id, $a_value) : void
    {
        $query = "SELECT MAX(pos) position FROM export_options";
        $res = $this->db->query($query);

        $pos = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $pos = (int) $row->position;
        }
        $pos++;

        $query = 'INSERT INTO export_options (export_id,keyword,ref_id,obj_id,value,pos) ' .
            'VALUES( ' .
            $this->db->quote($this->getExportId(), 'integer') . ', ' .
            $this->db->quote($a_keyword, 'integer') . ', ' .
            $this->db->quote($a_ref_id, 'integer') . ', ' .
            $this->db->quote($a_obj_id, 'integer') . ', ' .
            $this->db->quote($a_value, 'integer') . ', ' .
            $this->db->quote($pos, 'integer') . ' ' .
            ')';
        $this->db->manipulate($query);
    }

    /**
     * @param $a_keyword
     * @return mixed|null
     */
    public function getOption(int $a_keyword)
    {
        return $this->options[$a_keyword] ?? null;
    }

    /**
     * Get option by
     * @param int $a_obj_id
     * @param int $a_keyword
     * @return mixed|null
     */
    public function getOptionByObjId(int $a_obj_id, int $a_keyword)
    {
        return $this->obj_options[$a_keyword][$a_obj_id] ?? null;
    }

    /**
     * Get option by
     * @param int $a_obj_id
     * @param int $a_keyword
     * @return mixed|null
     */
    public function getOptionByRefId(int $a_ref_id, int $a_keyword)
    {
        return $this->ref_options[$a_keyword][$a_ref_id] ?? null;
    }

    public function delete() : void
    {
        $query = "DELETE FROM export_options " .
            "WHERE export_id = " . $this->db->quote($this->getExportId(), 'integer');
        $this->db->manipulate($query);
    }

    public function read() : void
    {
        $this->options = array();
        $this->obj_options = array();
        $this->ref_options = array();

        $query = "SELECT * FROM export_options " .
            "WHERE export_id = " . $this->db->quote($this->getExportId(), 'integer') . ' ' .
            "ORDER BY pos";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->ref_id) {
                $this->ref_options[(int) $row->keyword][(int) $row->ref_id] = $row->value;
            }
            if ($row->obj_id) {
                $this->obj_options[(int) $row->keyword][(int) $row->obj_id] = $row->value;
            }
            if (!$row->ref_id and !$row->obj_id) {
                $this->options[(int) $row->keyword] = $row->value;
            }
        }
    }
}
